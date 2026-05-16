<?php
require_once __DIR__ . '/../../includes/database.php';

$sql = "
CREATE TABLE IF NOT EXISTS RegistroActividad (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    accion VARCHAR(100),
    descripcion TEXT,
    ip_address VARCHAR(45),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);
";

if (mysqli_query($db, $sql)) {
    echo "Tabla RegistroActividad creada correctamente.";
} else {
    echo "Error: " . mysqli_error($db);
}
?>
