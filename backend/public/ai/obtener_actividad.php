<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

$query = "
    SELECT r.*, u.nombre as usuario_nombre, u.rol
    FROM RegistroActividad r
    LEFT JOIN Usuarios u ON r.id_usuario = u.id_usuario
    ORDER BY r.fecha_hora DESC
    LIMIT 50
";

$result = mysqli_query($db, $query);
$actividad = [];

while ($row = mysqli_fetch_assoc($result)) {
    $actividad[] = $row;
}

echo json_encode(['status' => 'ok', 'data' => $actividad]);
?>
