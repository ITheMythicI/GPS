<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

mysqli_query($db, "
    CREATE TABLE IF NOT EXISTS RegistroActividad (
        id_log INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT,
        accion VARCHAR(100),
        descripcion TEXT,
        ip_address VARCHAR(45),
        fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
    )
");

$query = "
    SELECT r.*, u.nombre AS usuario_nombre, u.rol
    FROM RegistroActividad r
    LEFT JOIN Usuarios u ON r.id_usuario = u.id_usuario
    ORDER BY r.fecha_hora DESC
    LIMIT 50
";

$result = mysqli_query($db, $query);
if (!$result) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db), 'data' => []]);
    exit;
}

$actividad = [];
while ($row = mysqli_fetch_assoc($result)) {
    $actividad[] = $row;
}

echo json_encode(['status' => 'ok', 'data' => $actividad]);
?>
