<?php
// Evitar cualquier espacio o línea antes del tag <?php
$db = mysqli_connect('10.0.2.8', 'bin_user', '123', 'bin_db');

if (!$db) {
    // Si falla la conexión, mostramos el error detallado para debug
    die("Error crítico: No se pudo conectar a MySQL (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
}

// Configurar charset para evitar problemas con acentos
mysqli_set_charset($db, "utf8mb4");

$DB_HOST = "10.0.2.8";
$DB_USER = "bin_user";
$DB_PASS = "123";
$DB_NAME = "bin_db";