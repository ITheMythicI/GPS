<?php
require_once __DIR__ . '/../../includes/database.php';

// 1. Renombrar y limpiar zonas
$sql = "
    UPDATE Zonas SET nombre = 'Área de Sistemas' WHERE id_zona = 1;
    UPDATE Zonas SET nombre = 'Área de Química' WHERE id_zona = 2;
    -- Mover contenedores de la zona 3 a la 1 antes de borrar
    UPDATE Contenedores SET id_zona = 1 WHERE id_zona = 3;
    DELETE FROM Zonas WHERE id_zona = 3;
";

if (mysqli_multi_query($db, $sql)) {
    do {
        if ($res = mysqli_store_result($db)) { mysqli_free_result($res); }
    } while (mysqli_next_result($db));
    echo "Zonas actualizadas a Sistemas y Química correctamente.";
} else {
    echo "Error: " . mysqli_error($db);
}
?>
