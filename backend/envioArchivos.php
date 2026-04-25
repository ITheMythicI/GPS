<?php
$conn = new mysqli("10.0.2.8", "bin_user", "123", "bin_db");

if ($conn->connect_error) {
    die("Conexion fallida: " . $conn->connect_error);
}

// Recibir datos
$idContenedor = $_POST['idContenedor'] ?? null;
$temp         = $_POST['tempCelsius'] ?? null;
$humedad      = $_POST['humedad'] ?? null;
$distancia    = $_POST['distanciaBoteTapa'] ?? null;
$peso         = $_POST['pesoKg'] ?? null;
$fecha        = date("Y-m-d H:i:s");

if (!$idContenedor) {
    die("Error: No se especificó el ID del contenedor.");
}

/**
 * Función para insertar lectura buscando el ID del sensor por tipo y contenedor
 */
function insertarLectura($conexion, $idCont, $tipo, $valor, $fechaHora) {
    if ($valor === null) return;

    // Buscamos el id_sensor que corresponde a este tipo en este contenedor
    // Nota: Si no tienes tipos 'Temperatura' o 'Peso' creados en la tabla Sensores, 
    // el sistema los ignorará. Solo insertará los que coincidan.
    $stmt = $conexion->prepare("SELECT id_sensor FROM Sensores WHERE id_contenedor = ? AND tipo_sensor LIKE ? LIMIT 1");
    $tipoBusqueda = "%$tipo%";
    $stmt->bind_param("is", $idCont, $tipoBusqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        $idSensor = $fila['id_sensor'];
        $insert = $conexion->prepare("INSERT INTO LecturasSensores (id_sensor, valor, fecha_hora) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $idSensor, $valor, $fechaHora);
        $insert->execute();
    }
}

// Ejecutar inserciones según los sensores que tengas vinculados en la BD
insertarLectura($conn, $idContenedor, 'Infrarrojo', $distancia, $fecha); // O 'Ultrasónico' según tu dump
insertarLectura($conn, $idContenedor, 'Temperatura', $temp, $fecha);
insertarLectura($conn, $idContenedor, 'Humedad', $humedad, $fecha);
insertarLectura($conn, $idContenedor, 'Peso', $peso, $fecha);

echo "OK - Datos procesados para Contenedor #$idContenedor";

$conn->close();
?>