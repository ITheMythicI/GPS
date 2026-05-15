<?php

header('Content-Type: application/json');

require __DIR__ . '/../includes/database.php';
require __DIR__ . '/../includes/funciones.php';

try {

    $consulta = obtener_tabla();

    if (!$consulta) {
        throw new Exception("Error en la consulta SQL: " . mysqli_error($db));
    }

    $datos = [];
    while ($fila = mysqli_fetch_assoc($consulta)) {
        $datos[] = $fila;
    }

    echo json_encode([
        "status" => "ok",
        "data" => $datos
    ]);


} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}