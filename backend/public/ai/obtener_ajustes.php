<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Obtener ajustes generales (con seguridad por si no existe la tabla)
$ajustes = [];
$res_table = mysqli_query($db, "SHOW TABLES LIKE 'AjustesSistema'");
if (mysqli_num_rows($res_table) > 0) {
    $res_aj = mysqli_query($db, "SELECT * FROM AjustesSistema");
    while ($row = mysqli_fetch_assoc($res_aj)) {
        $ajustes[$row['clave']] = $row['valor'];
    }
}

// Valores por defecto si está vacío
if (empty($ajustes)) {
    $ajustes = [
        'umbral_llenado' => '85',
        'umbral_bateria' => '15',
        'velocidad_simulacion' => '1',
        'simulador_activo' => '1'
    ];
}

// Obtener info del usuario
$id_usuario = $_SESSION['id_usuario'] ?? 0;
$user_data = ['foto_perfil' => null, 'config_oscuro' => 0];

if ($id_usuario > 0) {
    $user_res = mysqli_query($db, "SELECT foto_perfil, config_oscuro FROM Usuarios WHERE id_usuario = $id_usuario");
    if ($user_res) {
        $user_data = mysqli_fetch_assoc($user_res) ?: $user_data;
    }
}

echo json_encode([
    'status' => 'ok',
    'sistema' => $ajustes,
    'usuario' => $user_data
]);
?>

