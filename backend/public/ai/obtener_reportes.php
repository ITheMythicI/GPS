<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

$query = "
    SELECT r.*, c.ubicacion as contenedor_nombre, u.nombre as usuario_nombre 
    FROM ReportesIncidencias r
    LEFT JOIN Contenedores c ON r.id_contenedor = c.id_contenedor
    LEFT JOIN Usuarios u ON r.id_usuario = u.id_usuario
    ORDER BY r.fecha_hora DESC
";

$result = mysqli_query($db, $query);
$reportes = [];

while ($row = mysqli_fetch_assoc($result)) {
    $reportes[] = $row;
}

echo json_encode(['status' => 'ok', 'data' => $reportes]);
?>
