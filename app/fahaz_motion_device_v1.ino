#include <WiFi.h>
#include <HTTPClient.h>

/* ====== WIFI LISTA ====== */
struct WifiCred {
  const char* ssid;
  const char* password;
};

WifiCred wifiList[] = {
  {"Jegvarazs2", ""},
  {"DDHome", ""}
};

const int WIFI_COUNT = sizeof(wifiList) / sizeof(wifiList[0]);

/* ====== API ====== */
const char* API_URL = "https://jegvarazsbolt.hu/api/sensor-events";
const char* API_KEY = "";

/* ====== DEVICE ====== */
const char* DEVICE_ID = "Azur";

/* ====== PIR ====== */
const int PIR_PIN = 2; // GPIO2 (D2)
bool lastPirState = LOW;
unsigned long lastTriggerTime = 0;
const unsigned long PIR_COOLDOWN = 15000; // 15 mp

/* ====== SETUP ====== */
void setup() {
  Serial.begin(115200);
  delay(1000);

  pinMode(PIR_PIN, INPUT);

  Serial.println("🚀 Booting...");

  // 🔥 PIR warm-up
  Serial.println("⏳ PIR warmup...");
  delay(5000);

  // 🔥 WiFi indulás késleltetve
  delay(1000);
  connectToWifi();
}

/* ====== LOOP ====== */
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ WiFi lost, reconnecting...");
    connectToWifi();
  }

  bool pirState = digitalRead(PIR_PIN);
  unsigned long now = millis();

  if (pirState == HIGH &&
      lastPirState == LOW &&
      now - lastTriggerTime > PIR_COOLDOWN) {

    lastTriggerTime = now;
    Serial.println("🚨 Motion detected!");
    sendMotionEvent();
  }

  lastPirState = pirState;
  delay(100);
}

/* ====== WIFI CONNECT ====== */
void connectToWifi() {
  WiFi.disconnect(true, true);
  delay(500);

  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false); // 🔥 nagyon fontos ESP32-C3-nál
  delay(500);

  for (int i = 0; i < WIFI_COUNT; i++) {
    Serial.printf("🔌 Trying WiFi: %s\n", wifiList[i].ssid);

    WiFi.begin(wifiList[i].ssid, wifiList[i].password);
    WiFi.setTxPower(WIFI_POWER_8_5dBm);

    unsigned long startAttempt = millis();
    while (WiFi.status() != WL_CONNECTED &&
           millis() - startAttempt < 15000) {
      delay(300);
      Serial.print(".");
    }

    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("\n✅ WiFi connected");
      Serial.print("IP: ");
      Serial.println(WiFi.localIP());
      return;
    }

    Serial.println("\n❌ Failed");
    WiFi.disconnect(true);
    delay(1000);
  }

  Serial.println("🚫 No WiFi available, retry later");
  delay(5000);
}

/* ====== SEND EVENT ====== */
void sendMotionEvent() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ No WiFi, cannot send");
    return;
  }

  HTTPClient http;
  http.begin(API_URL);

  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.addHeader("X-API-KEY", API_KEY);

  String payload =
    "device_id=" + String(DEVICE_ID) +
    "&event=motion" +
    "&sensor=pir" +
    "&value=1" +
    "&ip_address=" + WiFi.localIP().toString();

  int httpResponseCode = http.POST(payload);

  Serial.print("📡 HTTP response: ");
  Serial.println(httpResponseCode);

  if (httpResponseCode > 0) {
    Serial.println(http.getString());
  }

  http.end();
}
