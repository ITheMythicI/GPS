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

// Asegurar que la columna estado soporte tanto los valores nuevos como los antiguos
mysqli_query($db, "ALTER TABLE ReportesIncidencias MODIFY COLUMN estado ENUM('Pendiente', 'En Revisión', 'Resuelto', 'Sin resolver', 'En revisión', 'Resuelta') DEFAULT 'Sin resolver'");

$id_reporte = $_POST['id_reporte'] ?? 0;
$estado = $_POST['estado'] ?? '';

$estados_validos = ['Sin resolver', 'En revisión', 'Resuelta', 'Pendiente', 'En Revisión', 'Resuelto'];
if (!in_array($estado, $estados_validos)) {
    echo json_encode(['status' => 'error', 'message' => 'Estado no válido']);
    exit;
}

$query = "UPDATE ReportesIncidencias SET estado = ? WHERE id_reporte = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'si', $estado, $id_reporte);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Estado actualizado con éxito']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
