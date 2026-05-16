<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_usuario = $_POST['id_usuario'] ?? null;
$accion = $_POST['accion'] ?? 'Actividad Desconocida';
$descripcion = $_POST['descripcion'] ?? '';
$ip = $_POST['ip'] ?? $_SERVER['REMOTE_ADDR'];

$query = "INSERT INTO RegistroActividad (id_usuario, accion, descripcion, ip_address) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'isss', $id_usuario, $accion, $descripcion, $ip);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
