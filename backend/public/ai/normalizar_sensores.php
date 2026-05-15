<?php
require_once __DIR__ . '/../../includes/database.php';

// Asegurar que existan sensores del 1 al 9 mapeados 1 a 1 con los contenedores
$sql = "
    -- Eliminar conflictos de IDs 8 y 9 que apuntan al contenedor 1
    DELETE FROM Sensores WHERE id_sensor IN (8, 9, 10);
    
    -- Asegurar sensores para todos los contenedores
    INSERT IGNORE INTO Sensores (id_sensor, tipo_sensor, id_contenedor) VALUES 
    (1, 'Infrarrojo', 1), (2, 'Infrarrojo', 2), (3, 'Ultrasónico', 3),
    (4, 'Infrarrojo', 4), (5, 'Ultrasónico', 5), (6, 'Infrarrojo', 6),
    (7, 'Ultrasónico', 7), (8, 'Simulado', 8), (9, 'Simulado', 9);
";

if (mysqli_multi_query($db, $sql)) {
    // Consumir resultados para evitar errores de sync
    do {
        if ($res = mysqli_store_result($db)) { mysqli_free_result($res); }
    } while (mysqli_next_result($db));
    echo "Sensores normalizados correctamente. Ya puedes simular.";
} else {
    echo "Error: " . mysqli_error($db);
}
?>
