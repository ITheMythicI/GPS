<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_contenedor = $_POST['id_contenedor'] ?? null;

if (!$id_contenedor) {
    echo json_encode(['status' => 'error', 'message' => 'ID de contenedor requerido']);
    exit;
}

// Borrar el contenedor
$query = "DELETE FROM Contenedores WHERE id_contenedor = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_contenedor);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Contenedor eliminado correctamente']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
