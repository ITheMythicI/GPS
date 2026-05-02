<?php
// Evitar que errores de PHP rompan el JSON, pero registrarlos
ini_set('display_errors', 0); 
error_reporting(E_ALL);

header('Content-Type: application/json');

// Requerir la base de datos (Verifica que esta ruta sea correcta en tu MV)
require __DIR__ . '/../../includes/database.php';

$nombre   = trim($_POST['nombre'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rol      = 'usuario';

if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// 1. Verificar conexión a la base de datos
if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Fallo de conexión a la base de datos']);
    exit;
}

// 2. Verificar si el correo ya existe
$stmt_check = mysqli_prepare($db, "SELECT id_usuario FROM Usuarios WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_check, "s", $email);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if (mysqli_stmt_num_rows($stmt_check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'El correo ya está registrado']);
    mysqli_stmt_close($stmt_check);
    exit;
}
mysqli_stmt_close($stmt_check);

// 3. Encriptación y guardado
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$sql = "INSERT INTO Usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
$stmt_insert = mysqli_prepare($db, $sql);

if ($stmt_insert) {
    mysqli_stmt_bind_param($stmt_insert, "ssss", $nombre, $email, $password_hash, $rol);
    if (mysqli_stmt_execute($stmt_insert)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al ejecutar inserción: ' . mysqli_stmt_error($stmt_insert)]);
    }
    mysqli_stmt_close($stmt_insert);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al preparar inserción: ' . mysqli_error($db)]);
}