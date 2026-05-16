<?php
require_once __DIR__ . '/../../includes/database.php';

// Script para reparar la tabla Zonas si le faltan columnas
$sql = "
    -- Asegurar que la tabla existe
    CREATE TABLE IF NOT EXISTS Zonas (
        id_zona INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100)
    );

    -- Añadir columnas faltantes una por una (ignorando errores si ya existen)
    ALTER TABLE Zonas ADD COLUMN IF NOT EXISTS prioridad_limpieza INT DEFAULT 1;
    ALTER TABLE Zonas ADD COLUMN IF NOT EXISTS color_hex VARCHAR(7) DEFAULT '#3b82f6';
    ALTER TABLE Zonas ADD COLUMN IF NOT EXISTS coordenadas_poligono TEXT;
";

if (mysqli_multi_query($db, $sql)) {
    do {
        if ($res = mysqli_store_result($db)) { mysqli_free_result($res); }
    } while (mysqli_next_result($db));
    echo "Estructura de la tabla Zonas reparada correctamente.";
} else {
    echo "Error reparando: " . mysqli_error($db);
}
?>
