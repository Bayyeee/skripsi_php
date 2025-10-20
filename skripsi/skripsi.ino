#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "UD-LiMa";
const char* password = "kokobob1922";
const char* serverAddress = "https://fireguardudlima.ltd/add_data.php";

const int flamePin = A0;
const int flameThreshold = 1600;
const int minFlameValue = 200;
const int maxFlameValue = 3000;

unsigned long lastSendTime = 0;
const unsigned long sendInterval = 60000;

void setup() {
  Serial.begin(115200);
  connectWiFi();
}

void loop() {
  int flameValue = analogRead(flamePin);

  if (flameValue < minFlameValue) {
    flameValue = minFlameValue;
  } else if (flameValue > maxFlameValue) {
    flameValue = maxFlameValue;
  }

  unsigned long currentMillis = millis();

  if (flameValue >= flameThreshold || (currentMillis - lastSendTime >= sendInterval)) {
    sendData(flameValue);
    lastSendTime = currentMillis;
  }

  delay(10000);
}

void connectWiFi() {
  Serial.print("Menghubungkan ke WiFi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\n✅ WiFi Terhubung!");
}

void sendData(int flameValue) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    Serial.println("\n🔗 Menghubungi server...");
    if (!http.begin(serverAddress)) {
      Serial.println("❌ Gagal terhubung ke server!");
      return;
    }

    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "analog_data=" + String(flameValue);
    Serial.print("📡 Mengirim Data: ");
    Serial.println(postData);

    int httpResponseCode = http.POST(postData);
    String response = http.getString();

    Serial.print("📥 HTTP Response Code: ");
    Serial.println(httpResponseCode);
    Serial.print("📝 Response: ");
    Serial.println(response);

    http.end();
  } else {
    Serial.println("⚠️ Koneksi WiFi terputus, mencoba reconnect...");
    connectWiFi();
  }
}
