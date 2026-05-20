<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

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

$id_usuario = (int)($_POST['id_usuario'] ?? 0);
$accion = trim((string)($_POST['accion'] ?? 'Actividad Desconocida'));
$descripcion = trim((string)($_POST['descripcion'] ?? ''));
$ip = trim((string)($_POST['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? ''));

if ($id_usuario <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'id_usuario requerido']);
    exit;
}

$query = "INSERT INTO RegistroActividad (id_usuario, accion, descripcion, ip_address) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'isss', $id_usuario, $accion, $descripcion, $ip);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
