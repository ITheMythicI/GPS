<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$nombre = $_POST['nombre'] ?? '';
$prioridad = $_POST['prioridad'] ?? 1;
$color = $_POST['color'] ?? '#3b82f6';
$coords = $_POST['coords'] ?? '[]';

if (!$nombre) {
    echo json_encode(['status' => 'error', 'message' => 'Nombre requerido']);
    exit;
}

$query = "INSERT INTO Zonas (nombre, prioridad_limpieza, color_hex, coordenadas_poligono) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'siss', $nombre, $prioridad, $color, $coords);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Zona creada correctamente']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
