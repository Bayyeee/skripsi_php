#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "UD-LiMa";
const char* password = "kokobob1922";
const char* serverUrl = "https://fireguardudlima.ltd/controller_api.php";

#define RELAY1_PIN 4  // Relay 1
#define RELAY2_PIN 5  // Relay 2

bool fireDetected = false;
unsigned long fireStartTime = 0;
const unsigned long fireDuration = 60000; 

void setup() {
  Serial.begin(115200);
  pinMode(RELAY1_PIN, OUTPUT);
  pinMode(RELAY2_PIN, OUTPUT);
  digitalWrite(RELAY1_PIN, HIGH);
  digitalWrite(RELAY2_PIN, HIGH);

  // Koneksi WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi!");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverUrl);
    int httpResponseCode = http.GET();

    if (httpResponseCode == 200) {
      String response = http.getString();
      Serial.println("Response: " + response);

      // Parsing JSON
      DynamicJsonDocument doc(256);
      DeserializationError error = deserializeJson(doc, response);

      if (!error) {
        String relay1_status = doc["relay1_status"];
        String relay2_status = doc["relay2_status"];

        // Jika api terdeteksi, nyalakan relay selama 5 menit
        if (relay1_status == "ON" && !fireDetected) {
          fireDetected = true;
          fireStartTime = millis();
          digitalWrite(RELAY1_PIN, LOW);
          Serial.println("🔥 Relay 1 AKTIF! (Water Pump menyala)");
        }

        // Setelah 5 menit, matikan relay
        if (fireDetected && millis() - fireStartTime >= fireDuration) {
          fireDetected = false;
          digitalWrite(RELAY1_PIN, HIGH);
          Serial.println("✅ Relay 1 OFF! (Water Pump dimatikan)");
        }

        // Kontrol Relay 2
        if (relay2_status == "ON") {
          digitalWrite(RELAY2_PIN, LOW);
          Serial.println("Relay 2 ON!");
        } else {
          digitalWrite(RELAY2_PIN, HIGH);
          Serial.println("Relay 2 OFF!");
        }
      } else {
        Serial.println("JSON Parsing Error!");
      }
    } else {
      Serial.print("HTTP Request failed: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  } else {
    Serial.println("WiFi Disconnected!");
  }

  delay(5000);
}
