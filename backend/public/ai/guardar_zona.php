<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Desactivar reporte de errores en pantalla para no corromper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $nombre = $_POST['nombre'] ?? '';
    $prioridad = (int)($_POST['prioridad'] ?? 1);
    $color = $_POST['color'] ?? '#3b82f6';
    $coords = $_POST['coords'] ?? '[]';

    if (!$nombre) {
        throw new Exception('El nombre de la zona es obligatorio');
    }

    $query = "INSERT INTO Zonas (nombre, prioridad_limpieza, color_hex, coordenadas_poligono) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    
    if (!$stmt) {
        throw new Exception("Error preparando la consulta: " . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, 'siss', $nombre, $prioridad, $color, $coords);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'ok', 'message' => 'Zona creada correctamente']);
    } else {
        throw new Exception("Error al ejecutar: " . mysqli_stmt_error($stmt));
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>
