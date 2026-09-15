#include <WiFi.h>
#include <WiFiManager.h>
#include <HTTPClient.h>
#include <PZEM004Tv30.h>
#include <ArduinoJson.h>

// Responsabilidad: medir el consumo, comunicarlo con la API y controlar los relés localmente.
// Cambia esta URL solo si cambia la IP, el puerto o la carpeta del servidor.
const char* serverApiUrl = "http://192.168.100.136/voltec-slim/public/api";
// Debe coincidir con API_KEY del archivo .env del servidor.
const char* apiKey = "VoltecEsp32Local2026_9f4c2a7b8d1e6f3a";

// Pines de salida conectados a los tres módulos de relé.
#define PIN_RELE_1 18
#define PIN_RELE_2 19
#define PIN_RELE_3 21

// Por encima de este valor se desconectan los relés como protección local.
#define UMBRAL_SOBRECONSUMO_WATTS 2500.0

PZEM004Tv30 pzem(&Serial2, 16, 17);

unsigned long lastLecturaMillis = 0;
const long intervalLectura = 2000;
bool sobreconsumoActivo = false;

void conectarWiFi() {
  // Si ya hay conexión, no vuelve a abrir el portal ni reinicia el enlace.
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  // WiFiManager intenta usar la red guardada. Si no puede, crea una red
  // "Energhost-Config" para configurar las credenciales desde el celular.
  WiFiManager wifiManager;
  wifiManager.setConfigPortalTimeout(180);

  if (!wifiManager.autoConnect("Energhost-Config")) {
    Serial.println("No se pudo conectar al WiFi");
    ESP.restart();
  }

  Serial.print("WiFi conectado. IP del ESP32: ");
  Serial.println(WiFi.localIP());
}

void setup() {
  // Configura el puerto serial, los relés y la conexión Wi-Fi configurable.
  Serial.begin(115200);

  pinMode(PIN_RELE_1, OUTPUT);
  pinMode(PIN_RELE_2, OUTPUT);
  pinMode(PIN_RELE_3, OUTPUT);
  
  digitalWrite(PIN_RELE_1, LOW);
  digitalWrite(PIN_RELE_2, LOW);
  digitalWrite(PIN_RELE_3, LOW);

  conectarWiFi();
}

void loop() {
  // Mantiene la red disponible y ejecuta una ronda de lectura cada dos segundos.
  conectarWiFi();

  if (millis() - lastLecturaMillis >= intervalLectura) {
    lastLecturaMillis = millis();

    if (WiFi.status() == WL_CONNECTED) {
      procesarYEnviarLecturas();
      consultarEstadoReles();
    }
  }
}

void procesarYEnviarLecturas() {
  // Lee el sensor PZEM, aplica valores de respaldo y publica la medición.
  float v = pzem.voltage();
  float i = pzem.current();
  float p = pzem.power();
  float e = pzem.energy();

  if (isnan(v)) v = 220.0;
  if (isnan(i)) i = 0.0;
  if (isnan(p)) p = 0.0;
  if (isnan(e)) e = 0.0;

  sobreconsumoActivo = p > UMBRAL_SOBRECONSUMO_WATTS;
  // El corte se realiza en el dispositivo para que no dependa de la red.
  if (sobreconsumoActivo) {
    digitalWrite(PIN_RELE_1, LOW);
    digitalWrite(PIN_RELE_2, LOW);
    digitalWrite(PIN_RELE_3, LOW);
    Serial.println("¡ALERTA: CORTE POR SOBRECONSUMO LOCAL!");
  }

  HTTPClient http;
  http.setConnectTimeout(3000);
  http.setTimeout(5000);
  http.begin(String(serverApiUrl) + "/lectura");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);

  StaticJsonDocument<200> doc;
  doc["dispositivo_id"] = "EcoSmart_01";
  doc["voltaje"] = v;
  doc["corriente"] = i;
  doc["potencia_activa"] = p;
  doc["energia_total_kwh"] = e;

  String requestBody;
  serializeJson(doc, requestBody);

  const int httpCode = http.POST(requestBody);
  if (httpCode < 200 || httpCode >= 300) {
    Serial.printf("Error enviando lectura: %d\n", httpCode);
  }
  http.end();
}

void consultarEstadoReles() {
  // Durante un sobreconsumo no se acepta el estado remoto para no reactivar cargas.
  if (sobreconsumoActivo) {
    return;
  }

  HTTPClient http;
  http.setConnectTimeout(3000);
  http.setTimeout(5000);
  http.begin(String(serverApiUrl) + "/control-reles");
  http.addHeader("X-API-Key", apiKey);

  int httpCode = http.GET();
  if (httpCode == 200) {
    // Solo se actualizan las salidas cuando la respuesta es HTTP 200 y contiene JSON válido.
    String payload = http.getString();
    StaticJsonDocument<300> doc;
    const DeserializationError error = deserializeJson(doc, payload);
    if (error) {
      Serial.println("Respuesta de relés inválida");
      http.end();
      return;
    }

    int r1 = doc["reles"]["rele_1"];
    int r2 = doc["reles"]["rele_2"];
    int r3 = doc["reles"]["rele_3"];

    digitalWrite(PIN_RELE_1, r1 == 1 ? HIGH : LOW);
    digitalWrite(PIN_RELE_2, r2 == 1 ? HIGH : LOW);
    digitalWrite(PIN_RELE_3, r3 == 1 ? HIGH : LOW);
  }
  else {
    Serial.printf("Error consultando relés: %d\n", httpCode);
  }
  http.end();
}