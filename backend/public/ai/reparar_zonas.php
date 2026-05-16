<?php
require_once __DIR__ . '/../../includes/database.php';

// Script robusto para reparar la tabla Zonas
echo "Iniciando reparación de tabla Zonas...\n";

// 1. Asegurar tabla base
mysqli_query($db, "CREATE TABLE IF NOT EXISTS Zonas (id_zona INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100))");

// 2. Función para añadir columna si no existe
function addColumnIfMissing($db, $table, $column, $definition) {
    $res = mysqli_query($db, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if (mysqli_num_rows($res) == 0) {
        if (mysqli_query($db, "ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
            echo "- Columna '$column' añadida.\n";
        } else {
            echo "- ERROR al añadir '$column': " . mysqli_error($db) . "\n";
        }
    } else {
        echo "- Columna '$column' ya existe.\n";
    }
}

addColumnIfMissing($db, 'Zonas', 'prioridad_limpieza', "INT DEFAULT 1");
addColumnIfMissing($db, 'Zonas', 'color_hex', "VARCHAR(7) DEFAULT '#3b82f6'");
addColumnIfMissing($db, 'Zonas', 'coordenadas_poligono', "TEXT");

echo "\nProceso finalizado.";
?>
