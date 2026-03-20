<?php
header("Content-Type: application/json");

// ===============================
// CONFIGURACIÓN
// ===============================
$host = "10.0.2.8";
$user = "bin_user";
$password = "123";
$database = "bin_db";

// API KEY (cámbiala si quieres)
$API_KEY = "BIN2026";

// ===============================
// CONEXIÓN A MYSQL
// ===============================
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a la base de datos"
    ]);
    exit;
}

// ===============================
// OBTENER JSON
// ===============================
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "JSON inválido o vacío"
    ]);
    exit;
}

// ===============================
// VALIDAR API KEY
// ===============================
if (!isset($data['api_key']) || $data['api_key'] !== $API_KEY) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "API KEY inválida"
    ]);
    exit;
}

// ===============================
// VALIDAR DATOS
// ===============================
if (!isset($data['id_sensor']) || !isset($data['valor'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Faltan campos requeridos"
    ]);
    exit;
}

$id_sensor = intval($data['id_sensor']);
$valor = strval($data['valor']);
$fecha = date("Y-m-d H:i:s");

// ===============================
// INSERTAR EN BD
// ===============================
$sql = "INSERT INTO LecturasSensores (id_sensor, valor, fecha_hora) VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error en prepare: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("iss", $id_sensor, $valor, $fecha);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Lectura guardada correctamente",
        "data" => [
            "id_sensor" => $id_sensor,
            "valor" => $valor,
            "fecha" => $fecha
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error al insertar: " . $stmt->error
    ]);
}

// ===============================
// CIERRE
// ===============================
$stmt->close();
$conn->close();
?>
