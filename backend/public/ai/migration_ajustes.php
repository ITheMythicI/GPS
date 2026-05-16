<?php
require_once __DIR__ . '/../../includes/database.php';

// Función auxiliar para añadir columnas si no existen
function addColumnIfNotExists($db, $table, $column, $definition) {
    $check = mysqli_query($db, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($db, "ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

addColumnIfNotExists($db, 'Usuarios', 'foto_perfil', 'VARCHAR(255) DEFAULT NULL');
addColumnIfNotExists($db, 'Usuarios', 'config_oscuro', 'TINYINT(1) DEFAULT 0');

$sql_ajustes = "
CREATE TABLE IF NOT EXISTS AjustesSistema (
    clave VARCHAR(50) PRIMARY KEY,
    valor TEXT
);
";
mysqli_query($db, $sql_ajustes);

mysqli_query($db, "INSERT IGNORE INTO AjustesSistema (clave, valor) VALUES ('umbral_llenado', '85'), ('umbral_bateria', '15'), ('velocidad_simulacion', '1'), ('simulador_activo', '1')");

echo "Base de datos actualizada correctamente.";
?>

