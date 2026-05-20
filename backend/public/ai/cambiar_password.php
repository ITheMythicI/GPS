<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_usuario = (int)($_POST['id_usuario'] ?? 0);
$actual = (string)($_POST['password_actual'] ?? '');
$nueva = (string)($_POST['password_nueva'] ?? '');
$confirmar = (string)($_POST['password_confirmar'] ?? '');

if ($id_usuario <= 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada']);
    exit;
}
if (strlen($actual) < 6 || strlen($nueva) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'La contraseña no cumple requisitos mínimos']);
    exit;
}
if ($nueva !== $confirmar) {
    echo json_encode(['status' => 'error', 'message' => 'La confirmación no coincide']);
    exit;
}
if ($actual === $nueva) {
    echo json_encode(['status' => 'error', 'message' => 'La nueva contraseña debe ser diferente a la actual']);
    exit;
}

$stmt = mysqli_prepare($db, "SELECT password FROM Usuarios WHERE id_usuario = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row || !password_verify($actual, $row['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'La contraseña actual no es válida']);
    exit;
}

$nuevo_hash = password_hash($nueva, PASSWORD_BCRYPT);
$up = mysqli_prepare($db, "UPDATE Usuarios SET password = ? WHERE id_usuario = ?");
mysqli_stmt_bind_param($up, 'si', $nuevo_hash, $id_usuario);

if (mysqli_stmt_execute($up)) {
    echo json_encode(['status' => 'ok', 'message' => 'Contraseña actualizada correctamente']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la contraseña']);
}
mysqli_stmt_close($up);
?>
