<?php
require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

// Desactivar reporte de errores en pantalla para no corromper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

$id_usuario = $_POST['id_usuario'] ?? $_GET['id_usuario'] ?? 0;
$query_role = mysqli_query($db, "SELECT rol FROM Usuarios WHERE id_usuario = " . (int)$id_usuario);
$user = mysqli_fetch_assoc($query_role);
if (!$user || $user['rol'] !== 'administrador') {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$id_reporte = $_POST['id_reporte'] ?? 0;
$estado = $_POST['estado'] ?? '';

// Mapear los estados del frontend a los valores ENUM de la BD
$mapa_estados = [
    'Sin resolver' => 'Pendiente',
    'En revisión'  => 'En Revisión',
    'Resuelta'     => 'Resuelto'
];

// Si el estado viene con el nombre del frontend, convertirlo al valor de BD
if (isset($mapa_estados[$estado])) {
    $estado_bd = $mapa_estados[$estado];
} else {
    $estado_bd = $estado;
}

$estados_validos = ['Pendiente', 'En Revisión', 'Resuelto'];
if (!in_array($estado_bd, $estados_validos)) {
    echo json_encode(['status' => 'error', 'message' => 'Estado no válido: ' . $estado]);
    exit;
}

$query = "UPDATE ReportesIncidencias SET estado = ? WHERE id_reporte = ?";
$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error preparando consulta: ' . mysqli_error($db)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'si', $estado_bd, $id_reporte);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Estado actualizado con éxito']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_stmt_error($stmt)]);
}
?>
