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
$claves_permitidas = ['velocidad_simulacion', 'simulador_activo', 'umbral_llenado', 'umbral_bateria'];

foreach ($_POST as $clave => $valor) {
    if (!in_array($clave, $claves_permitidas)) {
        continue;
    }
    
    try {
        $query = "INSERT INTO AjustesSistema (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?";
        $stmt = mysqli_prepare($db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sss', $clave, $valor, $valor);
            if (!mysqli_stmt_execute($stmt)) {
                $errores[] = "$clave: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errores[] = "$clave: " . mysqli_error($db);
        }
    } catch (Throwable $e) {
        $errores[] = "$clave: " . $e->getMessage();
    }
}

if (empty($errores)) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'message' => implode(", ", $errores)]);
}
?>
