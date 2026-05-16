<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Solo administradores pueden guardar ajustes globales
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$errores = [];
foreach ($_POST as $clave => $valor) {
    $query = "INSERT INTO AjustesSistema (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $clave, $valor, $valor);
    if (!mysqli_stmt_execute($stmt)) {
        $errores[] = mysqli_error($db);
    }
}

if (empty($errores)) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'message' => implode(", ", $errores)]);
}
?>
