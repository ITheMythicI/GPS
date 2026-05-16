<?php
require_once __DIR__ . '/../../includes/database.php';

// Crear tabla de Reportes de Incidencias
$sql = "
CREATE TABLE IF NOT EXISTS ReportesIncidencias (
    id_reporte INT AUTO_INCREMENT PRIMARY KEY,
    id_contenedor INT,
    id_usuario INT,
    tipo_incidencia ENUM('Vandalismo', 'Exceso de Desechos', 'Mal Olor', 'Sensor Fallido', 'Otro') DEFAULT 'Otro',
    descripcion TEXT,
    foto_url VARCHAR(255),
    lat_reporte DECIMAL(10, 8),
    lng_reporte DECIMAL(11, 8),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente', 'En Revisión', 'Resuelto') DEFAULT 'Pendiente',
    FOREIGN KEY (id_contenedor) REFERENCES Contenedores(id_contenedor),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);
";

if (mysqli_query($db, $sql)) {
    echo "Tabla ReportesIncidencias creada correctamente.";
} else {
    echo "Error: " . mysqli_error($db);
}
?>
