#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <SD.h>
#include <Wire.h>
#include <RTClib.h>
#include <Preferences.h>

const char* WIFI1_SSID = "DDHome";
const char* WIFI1_PASS = "Stage3556";

const char* WIFI2_SSID = "Jegvarazs2";
const char* WIFI2_PASS = "klima2025";

const char* API_URL = "https://jegvarazsbolt.hu/api/sensor-events";
const char* API_KEY = "9jQ21zzFUry2YkkxAMaxcZwuD6qR6cDJ";
const char* DEVICE_ID = "Teszt";

#define PIR_PIN 2

#define SD_CS   10
#define SD_MOSI 6
#define SD_MISO 5
#define SD_SCK  7

#define I2C_SDA 8
#define I2C_SCL 9

#define MOTION_LOCK_MS      10000
#define FLUSH_INTERVAL_MS   600000
#define UPLOAD_INTERVAL_MS  10000

#define MOTION_BUFFER_SIZE  128
#define PENDING_COMPACT_THRESHOLD_BYTES  200000

static const char* EVENTS_PATH  = "/events.log";
static const char* PENDING_PATH = "/pending.log";

struct MotionEvent
{
    uint32_t id;
    uint32_t epoch;
};

RTC_DS3231 rtc;
bool rtcAvailable = false;

Preferences prefs;
uint32_t nextEventId = 1;
uint32_t pendingOffset = 0;

volatile MotionEvent motionBuf[MOTION_BUFFER_SIZE];
volatile uint16_t motionHead = 0;
volatile uint16_t motionTail = 0;
volatile bool motionFlushRequested = false;

volatile unsigned long lastPirSignal = 0;
volatile uint16_t pirPendingCount = 0;

unsigned long lastMotionAccepted = 0;
unsigned long lastFlushAt = 0;
unsigned long nextUploadAt = 0;
unsigned long lastWifiAttemptAt = 0;

WiFiClientSecure secureClient;

static inline uint16_t motionBufferCountUnsafe()
{
    if (motionHead >= motionTail) return (uint16_t) (motionHead - motionTail);
    return (uint16_t) (MOTION_BUFFER_SIZE - (motionTail - motionHead));
}

uint16_t motionBufferCount()
{
    uint16_t count;
    noInterrupts();
    count = motionBufferCountUnsafe();
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
        motionFlushRequested = true;
        ok = false;
    }
    else
    {
        motionBuf[motionHead].id = id;
        motionBuf[motionHead].epoch = epoch;
        motionHead = nextHead;

        uint16_t used = motionBufferCountUnsafe();
        if (used >= (uint16_t) (MOTION_BUFFER_SIZE - 4))
            motionFlushRequested = true;

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

String formatEpoch(uint32_t epoch)
{
    if (epoch == 0) return String("1970-01-01 00:00:00");
    DateTime dt(epoch);
    char buf[24];
    sprintf(buf, "%04d-%02d-%02d %02d:%02d:%02d",
        dt.year(), dt.month(), dt.day(),
        dt.hour(), dt.minute(), dt.second());
    return String(buf);
}

void savePrefs()
{
    prefs.begin("motion", false);
    prefs.putUInt("next_id", nextEventId);
    prefs.putUInt("pending_ofs", pendingOffset);
    prefs.end();
}

void loadPrefs()
{
    prefs.begin("motion", true);
    nextEventId = prefs.getUInt("next_id", 1);
    pendingOffset = prefs.getUInt("pending_ofs", 0);
    prefs.end();
}

bool ensureSd()
{
    static bool inited = false;
    if (inited) return true;

    SPI.begin(SD_SCK, SD_MISO, SD_MOSI, SD_CS);
    if (!SD.begin(SD_CS))
        return false;

    inited = true;
    return true;
}

void appendLinesToFile(const char* path, MotionEvent* events, uint16_t n)
{
    File f = SD.open(path, FILE_APPEND);
    if (!f) return;

    for (uint16_t i = 0; i < n; i++)
    {
        const String ts = formatEpoch(events[i].epoch);
        f.print(events[i].id);
        f.print(',');
        f.print(ts);
        f.print(',');
        f.print(DEVICE_ID);
        f.print("\n");
    }

    f.close();
}

void flushMotionBufferToSd()
{
    if (!ensureSd()) return;

    MotionEvent local[32];
    while (true)
    {
        uint16_t n = drainMotionBuffer(local, 32);
        if (n == 0) break;

        appendLinesToFile(EVENTS_PATH, local, n);
        appendLinesToFile(PENDING_PATH, local, n);
    }

    savePrefs();
}

void compactPendingIfNeeded()
{
    if (!ensureSd()) return;

    File f = SD.open(PENDING_PATH, FILE_READ);
    if (!f) return;

    size_t size = f.size();
    f.close();

    if (pendingOffset < PENDING_COMPACT_THRESHOLD_BYTES) return;
    if (pendingOffset < (uint32_t) (size / 2)) return;

    File src = SD.open(PENDING_PATH, FILE_READ);
    if (!src) return;

    if (!src.seek(pendingOffset))
    {
        src.close();
        return;
    }

    SD.remove("/pending.tmp");
    File tmp = SD.open("/pending.tmp", FILE_WRITE);
    if (!tmp)
    {
        src.close();
        return;
    }

    uint8_t buf[256];
    while (src.available())
    {
        int r = src.read(buf, sizeof(buf));
        if (r <= 0) break;
        tmp.write(buf, (size_t) r);
    }

    src.close();
    tmp.close();

    SD.remove(PENDING_PATH);
    SD.rename("/pending.tmp", PENDING_PATH);
    pendingOffset = 0;
    savePrefs();
}

bool wifiConnected()
{
    return WiFi.status() == WL_CONNECTED;
}

void connectWifiIfNeeded()
{
    if (wifiConnected()) return;
    if (millis() - lastWifiAttemptAt < 5000) return;

    lastWifiAttemptAt = millis();

    WiFi.disconnect(true);
    WiFi.mode(WIFI_STA);

    WiFi.begin(WIFI1_SSID, WIFI1_PASS);
    unsigned long start = millis();
    while (!wifiConnected() && millis() - start < 3000) delay(50);
    if (wifiConnected()) return;

    WiFi.begin(WIFI2_SSID, WIFI2_PASS);
    start = millis();
    while (!wifiConnected() && millis() - start < 3000) delay(50);
}

bool uploadOnePendingLine()
{
    if (!ensureSd()) return false;
    if (!wifiConnected()) return false;

    File f = SD.open(PENDING_PATH, FILE_READ);
    if (!f) return false;

    if (pendingOffset > 0 && !f.seek(pendingOffset))
    {
        f.close();
        return false;
    }

    String line = f.readStringUntil('\n');
    uint32_t newOffset = (uint32_t) f.position();
    bool hadLine = line.length() > 0;
    f.close();

    if (!hadLine) return false;

    line.trim();
    int p1 = line.indexOf(',');
    int p2 = (p1 >= 0) ? line.indexOf(',', p1 + 1) : -1;
    if (p1 < 0 || p2 < 0) return false;

    String idStr = line.substring(0, p1);
    String tsStr = line.substring(p1 + 1, p2);

    HTTPClient http;
    secureClient.setInsecure();

    http.begin(secureClient, API_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-KEY", API_KEY);

    String body = "{";
    body += "\"device_id\":\"" + String(DEVICE_ID) + "\",";
    body += "\"event_id\":" + idStr + ",";
    body += "\"event_time\":\"" + tsStr + "\",";
    body += "\"event_type\":\"motion\"";
    body += "}";

    int code = http.POST(body);
    http.end();

    if (code >= 200 && code < 300)
    {
        pendingOffset = newOffset;
        savePrefs();
        compactPendingIfNeeded();
        return true;
    }

    return false;
}

void IRAM_ATTR pirIsr()
{
    lastPirSignal = millis();
    if (pirPendingCount < 65535) pirPendingCount++;
}

void handlePirPending()
{
    if (pirPendingCount == 0) return;

    unsigned long now = millis();
    if (now - lastMotionAccepted <= MOTION_LOCK_MS) return;

    delay(30);
    if (digitalRead(PIR_PIN) != HIGH) return;

    noInterrupts();
    pirPendingCount = 0;
    interrupts();

    lastMotionAccepted = now;

    uint32_t id = nextEventId++;
    uint32_t epoch = 0;
    if (rtcAvailable)
        epoch = (uint32_t) rtc.now().unixtime();

    enqueueMotionEvent(id, epoch);
}

void setup()
{
    Serial.begin(115200);

    pinMode(PIR_PIN, INPUT);
    attachInterrupt(digitalPinToInterrupt(PIR_PIN), pirIsr, RISING);

    Wire.begin(I2C_SDA, I2C_SCL);
    rtcAvailable = rtc.begin();

    loadPrefs();

    ensureSd();

    WiFi.mode(WIFI_STA);
    connectWifiIfNeeded();

    lastFlushAt = millis();
    nextUploadAt = millis() + 5000;
}

void loop()
{
    handlePirPending();

    if (motionFlushRequested)
    {
        motionFlushRequested = false;
        if (motionBufferCount() > 0)
        {
            flushMotionBufferToSd();
            lastFlushAt = millis();
        }
    }

    if ((unsigned long) (millis() - lastFlushAt) >= (unsigned long) FLUSH_INTERVAL_MS)
    {
        if (motionBufferCount() > 0)
            flushMotionBufferToSd();
        lastFlushAt = millis();
    }

    if ((unsigned long) (millis() - nextUploadAt) >= (unsigned long) UPLOAD_INTERVAL_MS)
    {
        nextUploadAt = millis();
        connectWifiIfNeeded();
        uploadOnePendingLine();
    }

    delay(5);
}
