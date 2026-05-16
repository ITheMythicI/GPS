<?php
require_once __DIR__ . '/../../includes/database.php';

$sql = "
ALTER TABLE Usuarios 
ADD COLUMN IF NOT EXISTS foto_perfil VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS config_oscuro TINYINT(1) DEFAULT 0;

CREATE TABLE IF NOT EXISTS AjustesSistema (
    clave VARCHAR(50) PRIMARY KEY,
    valor TEXT
);

INSERT IGNORE INTO AjustesSistema (clave, valor) VALUES 
('umbral_llenado', '85'),
('umbral_bateria', '15'),
('velocidad_simulacion', '1'),
('simulador_activo', '1');
";

if (mysqli_multi_query($db, $sql)) {
    echo "Base de datos actualizada para ajustes y perfiles.";
} else {
    echo "Error: " . mysqli_error($db);
}
?>
