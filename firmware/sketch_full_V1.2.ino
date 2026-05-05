//ctrl + g -> CAMBIAR ESTO y debug

// Librerias necesarias:
// - DHT Sensor Library: https://github.com/adafruit/DHT-sensor-library
// - Adafruit Unified Sensor Lib: https://github.com/adafruit/Adafruit_Sensor
// - HX711 Library: https://github.com/bogde/HX711
// - WiFi y HTTPClient, vienen por defecto
#include "DHT.h"
#include "HX711.h"

#define DHTPIN 18    
#define DHTTYPE DHT22   // DHT 22  
#include <Wire.h>
#include <VL53L0X.h>
#include <WiFi.h>
#include <HTTPClient.h>

// Datos del wifi, poner internet de 2.4 GHz, NO FUNCIONA CON 5G
const char* ssid = "Mega_2.4G_239E";
const char* password = "SQUHKDKT";
//internet de la uni
//ssid -> "";
//pass -> "";

// IP de envio - apuntar al contenedor API de Docker
//const char* serverName = "http://api:3000/lectura"; // CAMBIAR ESTO si el ESP32 está fuera de la red Docker
const char* serverName = "http://129.146.115.127/api/lecturas.php";

// HX711 #1
const int LOADCELL_A_DOUT = 16;
const int LOADCELL_A_SCK = 4;
// HX711 #2
//const int LOADCELL_B_DOUT = 17;
//const int LOADCELL_B_SCK = 5;

// Sensor temperatura:
// Pin 1 del sensor a 3V
// Pin 2 del sensor a cualquier DHTPIN
// Pin 4 del sensor a GROUND
// Un resistor de 10K ohms de pin 2 (datos) a pin 1 (poder) del sensor, en el protoboard
// Sensor volumen:
// 3Volts a VIN
// GROUND a GROUND (GND)
// D22 a SCL
// D21 a SDA
// Sensor peso:
// Celdas a HX711:
//  Rojo a E-
//  Negro a E+
//  Blanco a A-
//  Verde a A+
// HX711 a ESP32:
//  GND a GND
//  DT a Pin 16/17
//  SCK Pin 4/5
//  VCC a 5volts 

// Inicializar sensor
DHT dht(DHTPIN, DHTTYPE); //DHT22, temperatura y humedad
VL53L0X sensor; //sensor infrarojo de distancia
HX711 scale1; //sensores de peso
//HX711 scale2;

//Constantes para los timers
unsigned long tiempoAnterior = 0;
const unsigned long intervalo = 1200000; // 1200000 milisegundos = 20 minutos, se cambia a 1000 para que lo haga cada segundo
//Funcion para conectarse a wi-fi 
void conectarWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;

  Serial.println("Reconectando a WiFi...");

  WiFi.disconnect();
  WiFi.begin(ssid, password);

  int intentos = 0;
  while (WiFi.status() != WL_CONNECTED && intentos < 20) {
    delay(500);
    Serial.print(".");
    intentos++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi reconectado!");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nNo se pudo reconectar");
  }
}



void setup() {
  Serial.begin(115200);
  delay(2000);
  Serial.println("INICIO SETUP");

  Serial.begin(115200); //antes 9600
  dht.begin();
  scale1.begin(LOADCELL_A_DOUT, LOADCELL_A_SCK);
  //scale2.begin(LOADCELL_B_DOUT, LOADCELL_B_SCK);

  //tara de la escala
  scale1.set_scale(13133);
  scale1.tare();

  //Inicializar I2C (los pines por defecto del ESP32 son el 21 y 22)
  Wire.begin(21, 22);

  //Inicializar el sensor
  sensor.setTimeout(500);
  if(!sensor.init())
  {
    Serial.println("Error: no se detecta VL53L0X, verifique la conexión");
    while(1);
  }
  
  //esto es opcional, para aumentar el rango
  sensor.setSignalRateLimit(0.1);
  sensor.setVcselPulsePeriod(VL53L0X::VcselPeriodPreRange, 18);
  sensor.setVcselPulsePeriod(VL53L0X::VcselPeriodFinalRange, 14);

  //Conectar a WiFi
  WiFi.mode(WIFI_STA);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(true);
  WiFi.begin(ssid, password);

  Serial.print("Conectando a WiFi");

  int intentos = 0;

  while (WiFi.status() != WL_CONNECTED && intentos < 20) {
    delay(500);
    Serial.print(".");
    intentos++;
  }
}

void loop() {
  //revisa si se conectó a wifi o no
  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }
  //debug, imprimir el peso
  //Serial.print("Peso: ");
  //Serial.println(scale1.get_units(), 3);
  
  //valores del sensor infrarojo
  int distancia = sensor.readRangeSingleMillimeters(); 
  float distanciaCalibrada = distancia / 10.0; // mm a cm
  float suma=0;

  Serial.print("Distancia RAW mm: ");
  Serial.println(distancia);
  //valores del sensor humedad
  // tomar las pruebas toma unos 250 millisegundos
  float h = dht.readHumidity();
  // Leer en celsius
  float t = dht.readTemperature();
  // lo lee en fahrenheit si se pone true al final  (isFahreheit = true)
  float f = dht.readTemperature(true);

  // Check para ver si alguna lectura falla y se sale muy temprano para volverlo a intentar
  if (isnan(h) || isnan(t) || isnan(f)) {
    Serial.println(F("Fallo al leer el sensor"));
    return;
  }

  // Calcular el indice de la temperatura en Fahrenheit 
  float hif = dht.computeHeatIndex(f, h);
  // Se calcula en Celsius si se pone false al final (isFahreheit = false)
  float hic = dht.computeHeatIndex(t, h, false);

  if (sensor.timeoutOccurred())
  {
    Serial.print("Timeout");
  }

  //prints temperatura, debug
  /*
  Serial.print(F("Humedad: "));
  Serial.print(h);
  Serial.print(F("%  Temperatura: "));
  Serial.print(t);
  Serial.print(F("°C "));
  Serial.print(f);
  Serial.print(F("°F  Indice calor: "));
  Serial.print(hic);
  Serial.print(F("°C "));
  Serial.print(hif);
  Serial.println(F("°F"));
  
  Serial.println("");
  //prints distancia, debug
  Serial.print("Distancia calibrada: ");
  Serial.print(distanciaCalibrada);
  Serial.println(" mm");
  */

    //if (scale1.is_ready() && scale2.is_ready()) {
    if (scale1.is_ready()) {
    //Enviar a base de datos
    if (WiFi.status() == WL_CONNECTED) {

      //float suma = 0;
      float peso = scale1.get_units(10);  // promedio interno de 10 lecturas cada segundo
      delay(1000);

      //cálculo para el tiempo, calcula los milisegundos entre cada intervalo de 20 minutos para que envie los datos sin afectar la conexión a wi-fi o Base de datos
      if (millis() - tiempoAnterior >= intervalo) {
        tiempoAnterior = millis();
        HTTPClient http;

        http.begin(serverName);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        //Variables a enviar
        String httpRequestData = "";
        httpRequestData += "idContenedor=1";
        httpRequestData += "&tempCelsius=" + String(t, 2);
        httpRequestData += "&humedad=" + String(h, 2);
        httpRequestData += "&distanciaBoteTapa=" + String(distanciaCalibrada, 2);
        httpRequestData += "&pesoKg=" + String(peso, 2);

        Serial.println("Enviando datos:");
        Serial.println(httpRequestData);
        int httpResponseCode = http.POST(httpRequestData);

        if (httpResponseCode > 0) {
          String response = http.getString();
          Serial.println("Respuesta servidor:");
          Serial.println(response);
        } else {
          Serial.print("Error enviando datos: ");
          Serial.println(httpResponseCode);
        }

        http.end();
      }
    }

  } else {

    Serial.println("HX711 no conectado, enviando peso=0");
    float peso = 0;
  }
}