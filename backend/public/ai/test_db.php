<?php
require __DIR__ . '/../../includes/database.php';
$res = mysqli_query($db, "SHOW TABLES LIKE 'Zonas'");
if (mysqli_num_rows($res) > 0) {
    echo "Tabla Zonas existe.";
} else {
    echo "Tabla Zonas NO existe.";
}
?>
