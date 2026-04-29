<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "INICIO\n";

include "../includes/database.php";
include "../includes/funciones.php";

//conexion a la base de datos con datos ocultos
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Conexion fallida: " . $conn->connect_error);
}

// Recibir datos por API
$idContenedor = $_POST['idContenedor'] ?? null;
$temp         = $_POST['tempCelsius'] ?? null;
$humedad      = $_POST['humedad'] ?? null;
$distancia    = $_POST['distanciaBoteTapa'] ?? null;
$peso         = $_POST['pesoKg'] ?? null;
$fecha        = date("Y-m-d H:i:s");

if (!$idContenedor) {
    die("Error: No se especificó el ID del contenedor.");
}

//Insercion de datos en la base de datos BIN
$stmt = $conn->prepare("
INSERT INTO LecturasSensores 
(id_sensor, fecha_hora, tempCelsius, humedad, distanciaBoteTapa, pesoKg)
VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isdddd",
    $idContenedor,
    $fecha,
    $temp,
    $humedad,
    $distancia,
    $peso
);

$stmt->execute();

//Confirmacion de insercion
echo "OK - Datos procesados para Contenedor #$idContenedor";

$conn->close();

echo "\nFIN";
?>