//ctrl + g -> CAMBIAR ESTO y debug

// Librerias necesarias:
// - DHT Sensor Library: https://github.com/adafruit/DHT-sensor-library
// - Adafruit Unified Sensor Lib: https://github.com/adafruit/Adafruit_Sensor
// - WiFi y HTTPClient, vienen por defecto
#include "DHT.h"

#define DHTPIN 4     
#define DHTTYPE DHT22   // DHT 22  
#include <Wire.h>
#include <VL53L0X.h>
#include <WiFi.h>
#include <HTTPClient.h>

// Datos del wifi, poner internet de 2.4 GHz, NO FUNCIONA CON 5G
const char* ssid = "Mega_5G_239E";
const char* password = "SQUHKDKT";

//internet de la uni
//ssid -> "";
//pass -> "";

// IP de envio
const char* serverName = "http://129.146.115.127/(direccion del archivo php aqui)"; // CAMBIAR ESTO, PONER LA DIRECCION DEL FRONTEND EN EL QUE SE ALOJA EL PHP DE ENVIO DE DATOS

// HX711 #1
const int LOADCELL_A_DOUT = 16;
const int LOADCELL_A_SCK = 4;
// HX711 #2
const int LOADCELL_B_DOUT = 17;
const int LOADCELL_B_SCK = 5;

// Sensor temperatura:
// Pin 1 del sensor a 3V
// Pin 2 del sensor a cualquier DHTPIN
// Pin 4 del sensor a GROUND
// Un resistor de 10K ohms de pin 2 (datos) a pin 1 (poder) del sensor, en el protoboard
// Sensor volumen:
// 3Volts a VIN
// GROUND a GROUND (GND)
// D23 a SCL
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
HX711 scale2;

void setup() {
  Serial.begin(115200); //antes 9600
  dht.begin();
  scale1.begin(LOADCELL_A_DOUT, LOADCELL_A_SCK);
  scale2.begin(LOADCELL_B_DOUT, LOADCELL_B_SCK);

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
  WiFi.begin(ssid, password);

  Serial.print("Conectando a WiFi");

  int intentos = 0;

  while (WiFi.status() != WL_CONNECTED && intentos < 20) {
    delay(500);
    Serial.print(".");
    intentos++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n Conectado!");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n ERROR: No se pudo conectar");
    Serial.print("Codigo: ");
    Serial.println(WiFi.status());
  }
}

void loop() {
  //valores del sensor infrarojo
  int distancia = sensor.readRangeSingleMillimeters(); 
  float distanciaCalibrada = distancia * 0.6;
  distanciaCalibrada * 10; //mm a cm
  float suma=0;

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
  if (scale1.is_ready() && scale2.is_ready()) {

    //Enviar a base de datos
    if (WiFi.status() == WL_CONNECTED) {

      for (int i = 0; i < 20; i++) {
        float lectura = scale1.get_units(5) + scale2.get_units(5);
        suma += lectura;
        delay(10);
      }

      float peso = suma / 20.0;

      delay(1000);

      HTTPClient http;

      http.begin(serverName);
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");
      //Variables a enviar
      String httpRequestData = 
      "tempCelsius=" + String(t, 2) + //temperatura en Celsius
      "&humedad=" + String(h, 2) + //% de humedad
      "&distanciaBoteTapa=" + String(distanciaCalibrada, 2) + //distancia de la tapa hacia el bote
      "&pesoKg=" + String(peso, 2); //peso en Kg

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

  } else {
    Serial.println("ERROR HX711: no se encontró.");
  }
  delay(10000);  // Envía cada 10 segundos
}