<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$ubicacion = $_POST['ubicacion'] ?? '';
$id_zona = $_POST['id_zona'] ?? null;
$lat = $_POST['lat'] ?? 0;
$lng = $_POST['lng'] ?? 0;
$es_real = $_POST['es_real'] ?? 0;

if (!$ubicacion) {
    echo json_encode(['status' => 'error', 'message' => 'Ubicación requerida']);
    exit;
}

$query = "INSERT INTO Contenedores (ubicacion, id_zona, latitud, longitud, es_real, estado) VALUES (?, ?, ?, ?, ?, 'Vacío')";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'siddi', $ubicacion, $id_zona, $lat, $lng, $es_real);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Contenedor creado correctamente']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
