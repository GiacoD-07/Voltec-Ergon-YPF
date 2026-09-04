#include <WiFi.h>
#include <HTTPClient.h>
#include <PZEM004Tv30.h>
#include <ArduinoJson.h>

const char* ssid = "TU_RED_WIFI";
const char* password = "TU_CONTRASEÑA";
const char* serverApiUrl = "http://192.168.1.100:8000/api";

// Asignación de Pines para 3 Relés (ajustá los GPIO según tu circuito)
#define PIN_RELE_1 18
#define PIN_RELE_2 19
#define PIN_RELE_3 21

#define UMBRAL_SOBRECONSUMO_WATTS 2500.0

PZEM004Tv30 pzem(&Serial2, 16, 17);

unsigned long lastLecturaMillis = 0;
const long intervalLectura = 2000;

void setup() {
  Serial.begin(115200);

  pinMode(PIN_RELE_1, OUTPUT);
  pinMode(PIN_RELE_2, OUTPUT);
  pinMode(PIN_RELE_3, OUTPUT);
  
  digitalWrite(PIN_RELE_1, LOW);
  digitalWrite(PIN_RELE_2, LOW);
  digitalWrite(PIN_RELE_3, LOW);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Conectado");
}

void loop() {
  if (millis() - lastLecturaMillis >= intervalLectura) {
    lastLecturaMillis = millis();

    if (WiFi.status() == WL_CONNECTED) {
      procesarYEnviarLecturas();
      consultarEstadoReles();
    }
  }
}

void procesarYEnviarLecturas() {
  float v = pzem.voltage();
  float i = pzem.current();
  float p = pzem.power();
  float e = pzem.energy();

  if (isnan(v)) v = 220.0;
  if (isnan(i)) i = 0.0;
  if (isnan(p)) p = 0.0;
  if (isnan(e)) e = 0.0;

  if (p > UMBRAL_SOBRECONSUMO_WATTS) {
    digitalWrite(PIN_RELE_1, LOW);
    digitalWrite(PIN_RELE_2, LOW);
    digitalWrite(PIN_RELE_3, LOW);
    Serial.println("¡ALERTA: CORTE POR SOBRECONSUMO LOCAL!");
  }

  HTTPClient http;
  http.begin(String(serverApiUrl) + "/lectura");
  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<200> doc;
  doc["dispositivo_id"] = "EcoSmart_01";
  doc["voltaje"] = v;
  doc["corriente"] = i;
  doc["potencia_activa"] = p;
  doc["energia_total_kwh"] = e;

  String requestBody;
  serializeJson(doc, requestBody);

  http.POST(requestBody);
  http.end();
}

void consultarEstadoReles() {
  HTTPClient http;
  http.begin(String(serverApiUrl) + "/control-reles");

  int httpCode = http.GET();
  if (httpCode == 200) {
    String payload = http.getString();
    StaticJsonDocument<300> doc;
    deserializeJson(doc, payload);

    int r1 = doc["reles"]["rele_1"];
    int r2 = doc["reles"]["rele_2"];
    int r3 = doc["reles"]["rele_3"];

    digitalWrite(PIN_RELE_1, r1 == 1 ? HIGH : LOW);
    digitalWrite(PIN_RELE_2, r2 == 1 ? HIGH : LOW);
    digitalWrite(PIN_RELE_3, r3 == 1 ? HIGH : LOW);
  }
  http.end();
}