<?php
require_once __DIR__ . '/../../includes/database.php';


$sql = "
-- 1. Tabla de Zonas
CREATE TABLE IF NOT EXISTS `Zonas` (
    `id_zona` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `color_hex` VARCHAR(7) DEFAULT '#3b82f6',
    `prioridad_zona` INT DEFAULT 1,
    `coordenadas_poligono` JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Modificar Contenedores
ALTER TABLE `Contenedores` 
ADD COLUMN `id_zona` INT DEFAULT NULL,
ADD COLUMN `es_real` BOOLEAN DEFAULT FALSE;

ALTER TABLE `Contenedores`
ADD CONSTRAINT `fk_contenedor_zona` FOREIGN KEY (`id_zona`) REFERENCES `Zonas` (`id_zona`) ON DELETE SET NULL;

-- 3. Insertar zonas iniciales
INSERT INTO `Zonas` (`nombre`, `descripcion`, `color_hex`, `prioridad_zona`) VALUES 
('Zona Académica', 'Edificios de clases y laboratorios', '#3b82f6', 2),
('Zona Recreativa', 'Cafetería y canchas', '#10b981', 1),
('Zona Administrativa', 'Oficinas y servicios', '#f59e0b', 3);

-- 4. Actualizar contenedores
UPDATE `Contenedores` SET `es_real` = TRUE WHERE `id_contenedor` = 1;
UPDATE `Contenedores` SET `id_zona` = 1 WHERE `id_contenedor` IN (1, 2, 6, 7);
UPDATE `Contenedores` SET `id_zona` = 2 WHERE `id_contenedor` IN (3, 5, 8, 9);
UPDATE `Contenedores` SET `id_zona` = 3 WHERE `id_contenedor` = 4;
";

// Ejecutar múltiples consultas
if (mysqli_multi_query($db, $sql)) {
    do {
        if ($result = mysqli_store_result($db)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($db));
    echo "Migración completada exitosamente.";
} else {
    echo "Error en la migración: " . mysqli_error($db);
}
?>
