<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

file_put_contents('debug_registro.log', date('[Y-m-d H:i:s] ') . "POST: " . print_r($_POST, true), FILE_APPEND);

require __DIR__ . '/../../includes/database.php';

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rol = 'usuario';

if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión MySQL: ' . mysqli_connect_error()]);
    exit;
}

$stmt_check = mysqli_prepare($db, "SELECT id_usuario FROM Usuarios WHERE email = ? LIMIT 1");
if (!$stmt_check) {
    echo json_encode(['status' => 'error', 'message' => 'Error en preparación SQL (Check): ' . mysqli_error($db)]);
    exit;
}

mysqli_stmt_bind_param($stmt_check, "s", $email);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if (mysqli_stmt_num_rows($stmt_check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'El correo electrónico ya está registrado.']);
    mysqli_stmt_close($stmt_check);
    exit;
}
mysqli_stmt_close($stmt_check);

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO Usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
$stmt_insert = mysqli_prepare($db, $sql);

if (!$stmt_insert) {
    echo json_encode(['status' => 'error', 'message' => 'Error en preparación SQL (Insert): ' . mysqli_error($db)]);
    exit;
}

mysqli_stmt_bind_param($stmt_insert, "ssss", $nombre, $email, $password_hash, $rol);

if (mysqli_stmt_execute($stmt_insert)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'No se pudo guardar el usuario: ' . mysqli_stmt_error($stmt_insert)
    ]);
}

mysqli_stmt_close($stmt_insert);