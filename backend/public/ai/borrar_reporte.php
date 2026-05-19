<?php
require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

$id_usuario = $_POST['id_usuario'] ?? $_GET['id_usuario'] ?? 0;
$query_role = mysqli_query($db, "SELECT rol FROM Usuarios WHERE id_usuario = " . (int)$id_usuario);
$user = mysqli_fetch_assoc($query_role);
if (!$user || $user['rol'] !== 'administrador') {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$id_reporte = $_POST['id_reporte'] ?? 0;

$query = "DELETE FROM ReportesIncidencias WHERE id_reporte = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_reporte);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Reporte eliminado con éxito']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
