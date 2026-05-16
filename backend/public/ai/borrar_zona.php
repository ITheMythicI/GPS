<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_zona = $_POST['id_zona'] ?? null;

if (!$id_zona) {
    echo json_encode(['status' => 'error', 'message' => 'ID de zona requerido']);
    exit;
}

// 1. Mover contenedores de esta zona a NULL o a una zona por defecto para no perderlos
mysqli_query($db, "UPDATE Contenedores SET id_zona = NULL WHERE id_zona = " . (int)$id_zona);

// 2. Borrar la zona
$query = "DELETE FROM Zonas WHERE id_zona = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_zona);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Zona eliminada correctamente']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
