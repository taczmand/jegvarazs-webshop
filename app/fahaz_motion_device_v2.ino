#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <SD.h>
#include <Wire.h>
#include <RTClib.h>
#include <Preferences.h>
#include <time.h>

//
// ================= CONFIG =================
//

const char* WIFI1_SSID = "DDHome";
const char* WIFI1_PASS = "";

const char* WIFI2_SSID = "Jegvarazs2";
const char* WIFI2_PASS = "";

const char* API_URL = "https://jegvarazsbolt.hu/api/sensor-events";
const char* API_KEY = "";

const char* DEVICE_ID = "Teszt";

#define PIR_PIN 2

#define SD_CS   10
#define SD_MOSI 6
#define SD_MISO 5
#define SD_SCK  7

#define I2C_SDA 8
#define I2C_SCL 9

#define MOTION_LOCK_MS      10000
#define UPLOAD_INTERVAL_MS   10000
#define MAX_UPLOAD_PER_CYCLE 1

#define UPLOAD_IDLE_INTERVAL_MS  60000

#define FLUSH_INTERVAL_MS        600000
#define MOTION_BUFFER_SIZE       256
#define PENDING_COMPACT_THRESHOLD_BYTES  200000

#define RTC_SYNC_INTERVAL_MS 3600000
#define RTC_MAX_DRIFT_SEC    30

#define SD_RECHECK_INTERVAL_MS 10000

//
// ================= GLOBALS =================
//

RTC_DS3231 rtc;
Preferences prefs;

uint32_t nextEventId = 1;

struct MotionEvent
{
    uint32_t id;
    uint32_t epoch;
};

volatile MotionEvent motionBuf[MOTION_BUFFER_SIZE];
volatile uint16_t motionHead = 0;
volatile uint16_t motionTail = 0;
volatile uint16_t motionDropped = 0;
volatile bool motionFlushRequested = false;

volatile unsigned long lastPirSignal = 0;
volatile uint16_t pirPendingCount = 0;

unsigned long lastMotionAccepted = 0;
unsigned long nextUploadAt = 0;
unsigned long lastWifiCheck = 0;
unsigned long lastWifiConnectAttempt = 0;
unsigned long lastRtcSync = 0;
unsigned long lastSdCheck = 0;
unsigned long lastFlushAt = 0;

bool sdErrorReported = false;
bool sdAvailable = true;

int lastPendingCountPrinted = -1;

bool apiIsHttps = false;
WiFiClientSecure apiSecureClient;
WiFiClient apiPlainClient;

static const char* rootCACertificate =
"-----BEGIN CERTIFICATE-----\n"
"MIIFazCCA1OgAwIBAgIRAIIQz7DSQONZRGPgu2OCiwAwDQYJKoZIhvcNAQELBQAw\n"
"TzELMAkGA1UEBhMCVVMxKTAnBgNVBAoTIEludGVybmV0IFNlY3VyaXR5IFJlc2Vh\n"
"cmNoIEdyb3VwMRUwEwYDVQQDEwxJU1JHIFJvb3QgWDEwHhcNMTUwNjA0MTEwNDM4\n"
"WhcNMzUwNjA0MTEwNDM4WjBPMQswCQYDVQQGEwJVUzEpMCcGA1UEChMgSW50ZXJu\n"
"ZXQgU2VjdXJpdHkgUmVzZWFyY2ggR3JvdXAxFTATBgNVBAMTDElTUkcgUm9vdCBY\n"
"MTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBAK3oJHP0FDfzm54rVygc\n"
"h77ct984kIxuPOZXoHj3dcKi/vVqbvYATyjb3miGbESTtrFj/RQSa78f0uoxmyF+\n"
"0TM8ukj13Xnfs7j/EvEhmkvBioZxaUpmZmyPfjxwv60pIgbz5MDmgK7iS4+3mX6U\n"
"A5/TR5d8mUgjU+g4rk8Kb4Mu0UlXjIB0ttov0DiNewNwIRt18jA8+o+u3dpjq+sW\n"
"T8KOEUt+zwvo/7V3LvSye0rgTBIlDHCNAymg4VMk7BPZ7hm/ELNKjD+Jo2FR3qyH\n"
"B5T0Y3HsLuJvW5iB4YlcNHlsdu87kGJ55tukmi8mxdAQ4Q7e2RCOFvu396j3x+UC\n"
"B5iPNgiV5+I3lg02dZ77DnKxHZu8A/lJBdiB3QW0KtZB6awBdpUKD9jf1b0SHzUv\n"
"KBds0pjBqAlkd25HN7rOrFleaJ1/ctaJxQZBKT5ZPt0m9STJEadao0xAH0ahmbWn\n"
"OlFuhjuefXKnEgV4We0+UXgVCwOPjdAvBbI+e0ocS3MFEvzG6uBQE3xDk3SzynTn\n"
"jh8BCNAw1FtxNrQHusEwMFxIt4I7mKZ9YIqioymCzLq9gwQbooMDQaHWBfEbwrbw\n"
"qHyGO0aoSCqI3Haadr8faqU9GY/rOPNk3sgrDQoo//fb4hVC1CLQJ13hef4Y53CI\n"
"rU7m2Ys6xt0nUW7/vGT1M0NPAgMBAAGjQjBAMA4GA1UdDwEB/wQEAwIBBjAPBgNV\n"
"HRMBAf8EBTADAQH/MB0GA1UdDgQWBBR5tFnme7bl5AFzgAiIyBpY9umbbjANBgkq\n"
"hkiG9w0BAQsFAAOCAgEAVR9YqbyyqFDQDLHYGmkgJykIrGF1XIpu+ILlaS/V9lZL\n"
"ubhzEFnTIZd+50xx+7LSYK05qAvqFyFWhfFQDlnrzuBZ6brJFe+GnY+EgPbk6ZGQ\n"
"3BebYhtF8GaV0nxvwuo77x/Py9auJ/GpsMiu/X1+mvoiBOv/2X/qkSsisRcOj/KK\n"
"NFtY2PwByVS5uCbMiogziUwthDyC3+6WVwW6LLv3xLfHTjuCvjHIInNzktHCgKQ5\n"
"ORAzI4JMPJ+GslWYHb4phowim57iaztXOoJwTdwJx4nLCgdNbOhdjsnvzqvHu7Ur\n"
"TkXWStAmzOVyyghqpZXjFaH3pO3JLF+l+/+sKAIuvtd7u+Nxe5AW0wdeRlN8NwdC\n"
"jNPElpzVmbUq4JUagEiuTDkHzsxHpFKVK7q4+63SM1N95R1NbdWhscdCb+ZAJzVc\n"
"oyi3B43njTOQ5yOf+1CceWxG1bQVs5ZufpsMljq4Ui0/1lvh+wjChP4kqKOJ2qxq\n"
"4RgqsahDYVvTH9w7jXbyLeiNdd8XM2w9U/t7y0Ff/9yi0GE44Za4rF2LN9d11TPA\n"
"mRGunUHBcnWEvgJBQl9nJEiU0Zsnvgc/ubhPgXRR4Xq37Z0j4r7g1SgEEzwxA57d\n"
"emyPxgcYxn/eR44/KJ4EBs+lVDR3veyJm+kXQ99b21/+jh5Xos1AnX5iItreGCc=\n"
"-----END CERTIFICATE-----\n";

const char* TZ_INFO = "CET-1CEST,M3.5.0,M10.5.0/3";

void onWiFiEvent(WiFiEvent_t event)
{
    Serial.print("[wifi] event: ");
    Serial.println((int)event);
}

String getDailyEventsPath();
String formatEpoch(uint32_t epoch);
void handleSdFailure();
void saveEventId();

uint16_t motionBufferCount()
{
    uint16_t count = 0;
    noInterrupts();
    if (motionHead >= motionTail) {
        count = (uint16_t) (motionHead - motionTail);
    } else {
        count = (uint16_t) (MOTION_BUFFER_SIZE - (motionTail - motionHead));
    }
    interrupts();
    return count;
}

bool enqueueMotionEvent(uint32_t id, uint32_t epoch)
{
    bool ok = false;
    noInterrupts();
    uint16_t nextHead = (uint16_t) ((motionHead + 1) % MOTION_BUFFER_SIZE);
    if (nextHead == motionTail)
    {
        if (motionDropped < 65535) motionDropped++;
        motionFlushRequested = true;
        ok = false;
    }
    else
    {
        motionBuf[motionHead].id = id;
        motionBuf[motionHead].epoch = epoch;
        motionHead = nextHead;
        uint16_t used = 0;
        if (motionHead >= motionTail) {
            used = (uint16_t) (motionHead - motionTail);
        } else {
            used = (uint16_t) (MOTION_BUFFER_SIZE - (motionTail - motionHead));
        }
        if (used >= (uint16_t) (MOTION_BUFFER_SIZE - 8)) {
            motionFlushRequested = true;
        }
        ok = true;
    }
    interrupts();
    return ok;
}

uint16_t drainMotionBuffer(MotionEvent* out, uint16_t maxCount)
{
    uint16_t written = 0;
    noInterrupts();
    while (motionTail != motionHead && written < maxCount)
    {
        out[written].id = motionBuf[motionTail].id;
        out[written].epoch = motionBuf[motionTail].epoch;
        motionTail = (uint16_t) ((motionTail + 1) % MOTION_BUFFER_SIZE);
        written++;
    }
    interrupts();
    return written;
}

bool flushMotionBufferToSd()
{
    if (!sdAvailable)
        return false;

    MotionEvent local[64];
    uint16_t totalFlushed = 0;

    while (true)
    {
        uint16_t n = drainMotionBuffer(local, 64);
        if (n == 0)
            break;

        const String eventsPath = getDailyEventsPath();

        File eventsFile = SD.open(eventsPath.c_str(), FILE_APPEND);
        if (!eventsFile)
        {
            Serial.print("[sd] open failed: ");
            Serial.println(eventsPath);
            handleSdFailure();
            return false;
        }

        File pendingFile = SD.open("/pending.log", FILE_APPEND);
        if (!pendingFile)
        {
            eventsFile.close();
            Serial.println("[sd] open failed: /pending.log");
            handleSdFailure();
            return false;
        }

        for (uint16_t i = 0; i < n; i++)
        {
            String dt = formatEpoch(local[i].epoch);
            String line = String(local[i].id) + ";" + dt;
            eventsFile.println(line);
            pendingFile.println(line);
        }

        pendingFile.close();
        eventsFile.close();

        totalFlushed += n;
    }

    if (totalFlushed > 0)
    {
        saveEventId();
        Serial.print("[sd] flushed motion events: ");
        Serial.println(totalFlushed);
    }

    return totalFlushed > 0;
}

//
// ================= WIFI =================
//

bool connectWifi()
{
    if (WiFi.status() == WL_CONNECTED)
        return true;

    lastWifiConnectAttempt = millis();

    WiFi.disconnect(false, false);
    delay(500);

    WiFi.mode(WIFI_STA);
    WiFi.setSleep(false);
    WiFi.setTxPower(WIFI_POWER_19_5dBm);
    delay(200);

    const char* ssids[] = {WIFI1_SSID, WIFI2_SSID};
    const char* passes[] = {WIFI1_PASS, WIFI2_PASS};

    for (int idx = 0; idx < 2; idx++)
    {
        Serial.print("[wifi] connecting to ");
        Serial.println(ssids[idx]);

        WiFi.begin(ssids[idx], passes[idx]);

        unsigned long startAttempt = millis();
        while (WiFi.status() != WL_CONNECTED && (millis() - startAttempt) < 15000)
        {
            bool hasPendingMotion = false;
            noInterrupts();
            hasPendingMotion = (pirPendingCount > 0);
            interrupts();

            if (hasPendingMotion)
            {
                Serial.println("\n[wifi] motion while connecting -> suspending connect");
                WiFi.disconnect(false, false);
                handlePirPending();
                Serial.print("[wifi] resuming connect to ");
                Serial.println(ssids[idx]);
                WiFi.begin(ssids[idx], passes[idx]);
                startAttempt = millis();
                continue;
            }

            delay(300);
            Serial.print(".");
            handlePirPending();
        }
        Serial.println();

        if (WiFi.status() == WL_CONNECTED)
        {
            Serial.println("[wifi] connected");
            Serial.print("[wifi] ip: ");
            Serial.println(WiFi.localIP());

            apiSecureClient.stop();
            apiPlainClient.stop();

            apiSecureClient.setNoDelay(true);
            apiPlainClient.setNoDelay(true);
            attachInterrupt(digitalPinToInterrupt(PIR_PIN), pirISR, RISING);
            return true;
        }

        Serial.println("[wifi] failed");
        WiFi.disconnect(true, true);
        delay(1000);
    }

    attachInterrupt(digitalPinToInterrupt(PIR_PIN), pirISR, RISING);
    return false;
}

void handlePirPending()
{
    uint16_t localCount = 0;
    noInterrupts();
    if (pirPendingCount > 0) {
        localCount = pirPendingCount;
        pirPendingCount = 0;
    }
    interrupts();

    if (localCount == 0)
        return;

    unsigned long now = millis();
    if (now - lastMotionAccepted <= MOTION_LOCK_MS)
        return;

    delay(50);
    if (digitalRead(PIR_PIN) != HIGH)
        return;

    lastMotionAccepted = now;
    Serial.println("[motion] accepted");

    uint32_t id = nextEventId++;

    uint32_t epoch = 0;
    if (!rtc.begin())
    {
        sendRtcError();
    }
    else
    {
        epoch = (uint32_t) rtc.now().unixtime();
    }

    enqueueMotionEvent(id, epoch);
}

void maintainWifi()
{
    unsigned long interval = (WiFi.status() == WL_CONNECTED) ? 30000 : 5000;
    if (millis() - lastWifiCheck < interval)
        return;

    lastWifiCheck = millis();

    if (WiFi.status() != WL_CONNECTED)
    {
        Serial.println("[wifi] disconnected, reconnecting...");
        connectWifi();
    }
}

void checkSdCard()
{
    if (millis() - lastSdCheck < SD_RECHECK_INTERVAL_MS)
        return;

    lastSdCheck = millis();

    if (sdAvailable)
        return;

    Serial.println("[sd] recheck...");
    if (!SD.begin(SD_CS))
    {
        Serial.println("[sd] recheck failed: SD.begin returned false");
        return;
    }

    uint8_t ct = SD.cardType();
    if (ct == CARD_NONE)
    {
        Serial.println("[sd] recheck: SD.begin ok but CARD_NONE (no card?)");
        return;
    }

    File root = SD.open("/");
    if (!root)
    {
        Serial.println("[sd] recheck: cannot open root, keeping SD disabled");
        return;
    }
    root.close();

    Serial.println("[sd] card is back, enabling SD logging");
    sdAvailable = true;
    sdErrorReported = false;
}

//
// ================= EVENT ID =================
//

void loadEventId()
{
    prefs.begin("sensor", false);
    nextEventId = prefs.getUInt("eid", 1);
}

void saveEventId()
{
    prefs.putUInt("eid", nextEventId);
}

uint32_t loadPendingOffset()
{
    prefs.begin("sensor", false);
    return prefs.getUInt("poff", 0);
}

void savePendingOffset(uint32_t offset)
{
    prefs.putUInt("poff", offset);
}

//
// ================= TIME =================
//

String getTime()
{
    DateTime now = rtc.now();


    char buf[32];
    sprintf(buf,
        "%04d-%02d-%02d %02d:%02d:%02d",
        now.year(), now.month(), now.day(),
        now.hour(), now.minute(), now.second()
    );
    Serial.println(buf);
    return String(buf);
}

String formatEpoch(uint32_t epoch)
{
    if (epoch == 0)
    {
        return String("1970-01-01 00:00:00");
    }
    DateTime dt(epoch);
    char buf[32];
    sprintf(buf,
        "%04d-%02d-%02d %02d:%02d:%02d",
        dt.year(), dt.month(), dt.day(),
        dt.hour(), dt.minute(), dt.second()
    );
    return String(buf);
}

String getDateStamp()
{
    DateTime now = rtc.now();

    char buf[16];
    sprintf(buf, "%04d-%02d-%02d", now.year(), now.month(), now.day());
    return String(buf);
}

String getDailyEventsPath()
{
    return String("/events_") + getDateStamp() + ".log";
}

bool syncRtcIfNeeded()
{
    if (millis() - lastRtcSync < RTC_SYNC_INTERVAL_MS)
        return false;

    lastRtcSync = millis();

    if (WiFi.status() != WL_CONNECTED)
        return false;

    if (!rtc.begin())
    {
        sendRtcError();
        return false;
    }

    configTime(0, 0, "pool.ntp.org", "time.nist.gov");
    setenv("TZ", TZ_INFO, 1);
    tzset();

    struct tm timeinfo;
    if (!getLocalTime(&timeinfo, 10000))
    {
        Serial.println("[rtc] NTP getLocalTime failed");
        return false;
    }

    time_t ntpEpoch = mktime(&timeinfo);
    DateTime rtcNow = rtc.now();
    struct tm rtcTm;
    rtcTm.tm_year = rtcNow.year() - 1900;
    rtcTm.tm_mon = rtcNow.month() - 1;
    rtcTm.tm_mday = rtcNow.day();
    rtcTm.tm_hour = rtcNow.hour();
    rtcTm.tm_min = rtcNow.minute();
    rtcTm.tm_sec = rtcNow.second();
    rtcTm.tm_isdst = -1;
    time_t rtcEpoch = mktime(&rtcTm);

    long diffSec = (long) (ntpEpoch - rtcEpoch);
    if (diffSec < 0) diffSec = -diffSec;

    if (diffSec > RTC_MAX_DRIFT_SEC)
    {
        Serial.print("[rtc] drift ");
        Serial.print(diffSec);
        Serial.println(" sec -> adjusting");
        rtc.adjust(DateTime(
            timeinfo.tm_year + 1900,
            timeinfo.tm_mon + 1,
            timeinfo.tm_mday,
            timeinfo.tm_hour,
            timeinfo.tm_min,
            timeinfo.tm_sec
        ));
        return true;
    }

    Serial.print("[rtc] drift OK: ");
    Serial.print(diffSec);
    Serial.println(" sec");

    return false;
}

//
// ================= IP =================
//

String getIP()
{
    return WiFi.localIP().toString();
}

//
// ================= SD =================
//

bool appendFile(const char* path, const String& line)
{
    if (!sdAvailable)
        return false;

    File f = SD.open(path, FILE_APPEND);
    if (!f) {
        Serial.print("[sd] open failed: ");
        Serial.println(path);
        handleSdFailure();
        return false;
    }

    f.println(line);
    f.close();
    Serial.print("[sd] appended: ");
    Serial.println(path);
    return true;
}

bool appendFile(const String& path, const String& line)
{
    return appendFile(path.c_str(), line);
}

int countPendingItems()
{
    if (!sdAvailable)
        return -1;

    File f = SD.open("/pending.log");
    if (!f)
        return 0;

    int count = 0;
    while (f.available())
    {
        String line = f.readStringUntil('\n');
        line.trim();
        if (line.length() > 0)
            count++;
    }
    f.close();
    return count;
}

void printPendingCountIfChanged()
{
    int c = countPendingItems();
    if (c < 0)
        return;

    if (c != lastPendingCountPrinted)
    {
        lastPendingCountPrinted = c;
        Serial.print("[queue] pending items: ");
        Serial.println(c);
    }
}

//
// ================= API =================
//

String urlEncode(const String& value)
{
    String out;
    out.reserve(value.length() * 3);
    for (size_t i = 0; i < value.length(); i++)
    {
        const char c = value.charAt(i);
        if ((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z') || (c >= '0' && c <= '9') || c == '-' || c == '_' || c == '.' || c == '~')
        {
            out += c;
        }
        else if (c == ' ')
        {
            out += "%20";
        }
        else
        {
            char buf[4];
            sprintf(buf, "%%%02X", (unsigned char)c);
            out += buf;
        }
    }
    return out;
}

bool sendForm(String payload)
{
    if (WiFi.status() != WL_CONNECTED)
    {
        Serial.println("[api] sendForm skipped, wifi not connected");
        return false;
    }

    Serial.print("[api] wifi ip: ");
    Serial.println(WiFi.localIP());

    for (int attempt = 1; attempt <= 2; attempt++)
    {
        Serial.print("[api] attempt: ");
        Serial.println(attempt);

        HTTPClient http;
        http.setReuse(false);
        Serial.println("[api] http begin...");

        if (apiIsHttps) {
            Serial.print("[api] tls connected: ");
            Serial.println(apiSecureClient.connected() ? "yes" : "no");
        } else {
            Serial.print("[api] tcp connected: ");
            Serial.println(apiPlainClient.connected() ? "yes" : "no");
        }


        bool begun = false;
        if (apiIsHttps)
        {
            begun = http.begin(apiSecureClient, API_URL);
        }
        else
        {
            begun = http.begin(apiPlainClient, API_URL);
        }

        if (!begun)
        {
            Serial.println("[api] http begin failed");
            http.end();
            if (attempt == 1)
            {
                apiSecureClient.stop();
                apiPlainClient.stop();
                Serial.println("[api] retrying with fresh connection...");
                delay(250);
                continue;
            }
            return false;
        }

        http.setTimeout(30000);

        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        http.addHeader("X-API-KEY", API_KEY);

        Serial.print("[api] posting bytes: ");
        Serial.println(payload.length());

        unsigned long postStart = millis();
        int code = http.POST(payload);
        unsigned long postMs = millis() - postStart;
        Serial.print("[api] post ms: ");
        Serial.println(postMs);

        Serial.print("[api] http code: ");
        Serial.println(code);

        if (code < 0)
        {
            Serial.print("[api] error: ");
            Serial.println(http.errorToString(code));

            http.end();
            apiSecureClient.stop();
            apiPlainClient.stop();

            if (attempt == 1)
            {
                Serial.println("[api] retrying with fresh connection...");
                delay(250);
                continue;
            }
            return false;
        }

        if (code > 0)
        {
            String body = http.getString();
            if (body.length() > 0)
            {
                Serial.print("[api] response: ");
                Serial.println(body);
            }
        }

        http.end();

        return (code >= 200 && code < 300);
    }

    return false;
}

//
// ================= ERROR EVENTS =================
//

void sendRtcError()
{
    Serial.println("[rtc] error");
    String payload =
        "device_id=" + urlEncode(String(DEVICE_ID)) +
        "&event=rtc_error" +
        "&sensor=pir" +
        "&ip_address=" + urlEncode(getIP());

    sendForm(payload);
}

void sendSdError()
{
    Serial.println("[sd] error");
    String payload =
        "device_id=" + urlEncode(String(DEVICE_ID)) +
        "&event=sd_error" +
        "&sensor=pir" +
        "&ip_address=" + urlEncode(getIP());

    sendForm(payload);
}

void handleSdFailure()
{
    if (sdErrorReported)
        return;

    sdErrorReported = true;
    sdAvailable = false;
    Serial.println("[sd] marking SD unavailable (will recheck periodically)");
    sendSdError();
}

//
// ================= MOTION DETECT (ISR only timestamp) =================
//

void IRAM_ATTR pirISR()
{
    lastPirSignal = millis();
    if (pirPendingCount < 1000) {
        pirPendingCount++;
    }
}

//
// ================= SAVE MOTION =================
//

void saveMotion()
{
    if (!rtc.begin())
    {
        sendRtcError();
        return;
    }

    uint32_t id = nextEventId++;
    saveEventId();

    String dt = getTime();

    String line = String(id) + ";" + dt;

    if (!sdAvailable)
    {
        Serial.println("[motion] SD unavailable -> sending immediately");
        String payload =
            "device_id=" + urlEncode(String(DEVICE_ID)) +
            "&event_id=" + String(id) +
            "&event=motion" +
            "&sensor=pir" +
            "&value=1" +
            "&event_time=" + urlEncode(dt) +
            "&ip_address=" + urlEncode(getIP());
        sendForm(payload);
        return;
    }

    Serial.println("[motion] saving to SD");

    appendFile(getDailyEventsPath(), line);
    appendFile("/pending.log", line);

    printPendingCountIfChanged();
}

//
// ================= UPLOAD QUEUE =================
//

int processQueue()
{
    if (!sdAvailable)
        return 0;

    if (WiFi.status() != WL_CONNECTED)
        return -1;

    File f = SD.open("/pending.log", FILE_READ);
    if (!f)
    {
        return 0;
    }

    uint32_t offset = loadPendingOffset();
    size_t size = f.size();
    if (offset >= size)
    {
        f.close();
        SD.remove("/pending.log");
        savePendingOffset(0);
        return 0;
    }

    if (!f.seek(offset))
    {
        f.close();
        handleSdFailure();
        return -1;
    }

    bool uploadedAny = false;
    uint16_t uploadedCount = 0;
    uint32_t newOffset = offset;

    char buf[96];
    while (f.available() && uploadedCount < MAX_UPLOAD_PER_CYCLE)
    {
        uint32_t lineStart = (uint32_t) f.position();
        size_t n = f.readBytesUntil('\n', buf, sizeof(buf) - 1);
        buf[n] = '\0';

        String line(buf);
        line.trim();
        if (line.length() == 0)
        {
            newOffset = (uint32_t) f.position();
            continue;
        }

        int p = line.indexOf(';');
        if (p < 0)
        {
            newOffset = (uint32_t) f.position();
            continue;
        }

        uint32_t id = (uint32_t) line.substring(0, p).toInt();
        String dt = line.substring(p + 1);

        String payload =
            "device_id=" + urlEncode(String(DEVICE_ID)) +
            "&event_id=" + String(id) +
            "&event=motion" +
            "&sensor=pir" +
            "&value=1" +
            "&event_time=" + urlEncode(dt) +
            "&ip_address=" + urlEncode(getIP());

        if (!sendForm(payload))
        {
            f.close();
            return uploadedAny ? (int) uploadedCount : -1;
        }

        uploadedAny = true;
        uploadedCount++;

        newOffset = (uint32_t) f.position();
        if (newOffset == lineStart)
        {
            newOffset = offset;
            break;
        }
    }

    f.close();
    if (uploadedCount == 0)
    {
        return 0;
    }

    savePendingOffset(newOffset);

    File rf = SD.open("/pending.log", FILE_READ);
    if (!rf)
    {
        return (int) uploadedCount;
    }

    size_t finalSize = rf.size();
    rf.close();

    if (newOffset >= finalSize)
    {
        SD.remove("/pending.log");
        savePendingOffset(0);
        return (int) uploadedCount;
    }

    if (newOffset > PENDING_COMPACT_THRESHOLD_BYTES)
    {
        File in = SD.open("/pending.log", FILE_READ);
        if (in)
        {
            if (in.seek(newOffset))
            {
                File out = SD.open("/pending.compact", FILE_WRITE);
                if (out)
                {
                    uint8_t chunk[256];
                    while (in.available())
                    {
                        size_t r = in.read(chunk, sizeof(chunk));
                        if (r == 0) break;
                        out.write(chunk, r);
                    }
                    out.close();
                    in.close();
                    SD.remove("/pending.log");
                    SD.rename("/pending.compact", "/pending.log");
                    savePendingOffset(0);
                }
                else
                {
                    in.close();
                }
            }
            else
            {
                in.close();
            }
        }
    }

    return (int) uploadedCount;
}

//
// ================= SETUP =================
//

void setup()
{
    Serial.begin(115200);
    Serial.println("[boot] start");

    WiFi.onEvent(onWiFiEvent);

    pinMode(PIR_PIN, INPUT_PULLDOWN);
    attachInterrupt(digitalPinToInterrupt(PIR_PIN), pirISR, RISING);

    Wire.begin(I2C_SDA, I2C_SCL);

    if (!rtc.begin())
    {
        sendRtcError();
    }
    else
    {
        DateTime now = rtc.now();

        char buffer[32];

        sprintf(
            buffer,
            "%04d-%02d-%02d %02d:%02d:%02d",
            now.year(),
            now.month(),
            now.day(),
            now.hour(),
            now.minute(),
            now.second()
        );

        Serial.print("RTC time: ");
        Serial.println(buffer);
    }

    SPI.begin(SD_SCK, SD_MISO, SD_MOSI, SD_CS);

    if (!SD.begin(SD_CS))
    {
        sendSdError();
        sdAvailable = false;
    }
    else
    {
        Serial.println("[sd] init OK");
    }

    loadEventId();

    savePendingOffset(loadPendingOffset());

    WiFi.mode(WIFI_STA);
    WiFi.setSleep(false); // 🔥 nagyon fontos ESP32-C3-nál
    WiFi.setTxPower(WIFI_POWER_8_5dBm);
    delay(500);

    apiIsHttps = String(API_URL).startsWith("https://");
    if (apiIsHttps) {
        apiSecureClient.setCACert(rootCACertificate);
        apiSecureClient.setTimeout(30000);
        apiSecureClient.setHandshakeTimeout(30);
    }

    apiSecureClient.setNoDelay(true);
    apiPlainClient.setNoDelay(true);

    connectWifi();

    Serial.print("[wifi] status: ");
    Serial.println(WiFi.status() == WL_CONNECTED ? "connected" : "not connected");

    lastRtcSync = millis() - RTC_SYNC_INTERVAL_MS;
    syncRtcIfNeeded();

    lastFlushAt = millis();
}

//
// ================= LOOP =================
//

void loop()
{
    maintainWifi();

    checkSdCard();

    syncRtcIfNeeded();

    //
    // PIR STABLE DEBOUNCE + LOCKOUT
    //
    handlePirPending();

    if (lastFlushAt == 0) {
        lastFlushAt = millis();
    }
    if ((unsigned long) (millis() - lastFlushAt) >= (unsigned long) FLUSH_INTERVAL_MS)
    {
        flushMotionBufferToSd();
        lastFlushAt = millis();
    }

    if (motionFlushRequested)
    {
        motionFlushRequested = false;
        if (motionBufferCount() > 0)
        {
            flushMotionBufferToSd();
            lastFlushAt = millis();
        }
    }

    //
    // UPLOAD LOOP
    //
    if (nextUploadAt == 0) {
        nextUploadAt = millis() + UPLOAD_IDLE_INTERVAL_MS;
    }

    if ((long) (millis() - nextUploadAt) >= 0)
    {
        processQueue();

        if (sdAvailable)
        {
            File pf = SD.open("/pending.log", FILE_READ);
            if (pf)
            {
                uint32_t off = loadPendingOffset();
                size_t sz = pf.size();
                pf.close();
                nextUploadAt = (off < sz) ? (millis() + UPLOAD_INTERVAL_MS) : (millis() + UPLOAD_IDLE_INTERVAL_MS);
            }
            else
            {
                nextUploadAt = millis() + UPLOAD_IDLE_INTERVAL_MS;
            }
        }
        else
        {
            nextUploadAt = millis() + UPLOAD_IDLE_INTERVAL_MS;
        }
    }

    delay(10);
}
