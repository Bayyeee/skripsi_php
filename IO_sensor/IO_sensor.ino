#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "UD-LiMa";
const char* password = "kokobob1922";
const char* serverAddress = "http://192.168.1.198/add_data.php";

const int flamePin = A0;
const int flameThresholdMin = 1600;
const int flameThresholdMax = 2500;
const int minFlameValue = 200;
const int maxFlameValue = 3000;

unsigned long lastSendTime = 0;
const unsigned long sendInterval = 60000;  // 1 menit
bool fireDetected = false;
unsigned long fireSuppressTime = 0;
const unsigned long suppressDuration = 60000;  // 1 menit

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

  // Jika api lebih dari 2500, hentikan pengiriman selama 1 menit
  if (flameValue > flameThresholdMax) {
    if (!fireDetected) {
      fireDetected = true;
      fireSuppressTime = currentMillis;
      Serial.println("🔥 Kebakaran besar terdeteksi! Menghentikan pengiriman data selama 1 menit.");
    }
  }

  // Jika sudah lebih dari 1 menit setelah kebakaran besar, reset flag
  if (fireDetected && (currentMillis - fireSuppressTime >= suppressDuration)) {
    fireDetected = false;
    Serial.println("✅ Waktu 1 menit selesai. Kembali mengirim data sensor.");
  }

  // Kirim data jika tidak dalam mode suppress dan sesuai interval
  if (!fireDetected && (currentMillis - lastSendTime >= sendInterval)) {
    sendData(flameValue);
    lastSendTime = currentMillis;
  }

  delay(1000);
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
