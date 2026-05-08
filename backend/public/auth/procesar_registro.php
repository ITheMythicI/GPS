<?php

header('Content-Type: application/json');
require __DIR__ . '/../../includes/database.php';

$nombre   = trim($_POST['nombre'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rol      = 'usuario';

// Validación de campos
if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// Verificar si el correo ya esta registrado
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

// Encriptación de contraseña
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insercion en la base de datos
$sql = "INSERT INTO Usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
$stmt_insert = mysqli_prepare($db, $sql);

if ($stmt_insert) {
    mysqli_stmt_bind_param($stmt_insert, "ssss", $nombre, $email, $password_hash, $rol);

    if (mysqli_stmt_execute($stmt_insert)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos: ' . mysqli_error($db)]);
    }
    mysqli_stmt_close($stmt_insert);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta']);
}

// Cerrar conexión
mysqli_close($db);