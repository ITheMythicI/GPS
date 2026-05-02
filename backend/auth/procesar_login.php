<?php

header('Content-Type: application/json');
require __DIR__ . '/../../includes/database.php'; // Ruta local a tu conexión

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
    exit;
}

// Query segura
$sql = "SELECT id_usuario, nombre, password, rol FROM Usuarios WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($usuario = mysqli_fetch_assoc($resultado)) {
    // Verificacion del Hash
    if (password_verify($password, $usuario['password'])) {
        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'rol' => strtolower($usuario['rol'])
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'El correo no existe']);
}

mysqli_stmt_close($stmt);