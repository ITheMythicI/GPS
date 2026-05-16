<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Obtener ajustes generales
$res = mysqli_query($db, "SELECT * FROM AjustesSistema");
$ajustes = [];
while ($row = mysqli_fetch_assoc($res)) {
    $ajustes[$row['clave']] = $row['valor'];
}

// Obtener info del usuario (para modo oscuro y foto)
session_start();
$id_usuario = $_SESSION['id_usuario'] ?? 0;
$user_res = mysqli_query($db, "SELECT foto_perfil, config_oscuro FROM Usuarios WHERE id_usuario = $id_usuario");
$user_data = mysqli_fetch_assoc($user_res);

echo json_encode([
    'status' => 'ok',
    'sistema' => $ajustes,
    'usuario' => $user_data
]);
?>
