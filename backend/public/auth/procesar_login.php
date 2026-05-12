<?php

header('Content-Type: application/json');
require __DIR__ . '/../../includes/database.php';

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
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_bind_result($stmt, $id_usuario, $nombre, $hashed_password, $rol);
    mysqli_stmt_fetch($stmt);

    // Verificacion del Hash
    if (password_verify($password, $hashed_password)) {
        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $id_usuario,
                'nombre' => $nombre,
                'rol' => strtolower($rol)
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'El correo no existe']);
}

mysqli_stmt_close($stmt);