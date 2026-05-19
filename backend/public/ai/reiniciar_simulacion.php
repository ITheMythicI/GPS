<?php
/**
 * reiniciar_simulacion.php
 * Resetea todos los contenedores de tipo "Simulado" a estado vacío.
 * Solo debe llamarse desde el proxy con rol administrador.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/database.php';

// 1. Borrar resultados IA de los contenedores simulados
$sql1 = "DELETE r FROM ResultadosIA r
         INNER JOIN Contenedores c ON r.id_contenedor = c.id_contenedor
         WHERE c.es_real = 0";
mysqli_query($db, $sql1);

// 2. Borrar lecturas de los contenedores simulados
$sql2 = "DELETE l FROM LecturasSensores l
         INNER JOIN Contenedores c ON l.id_sensor = c.id_contenedor
         WHERE c.es_real = 0";
mysqli_query($db, $sql2);

// 3. Resetear estado en la tabla Contenedores
$sql3 = "UPDATE Contenedores SET estado = 'Vacío' WHERE es_real = 0";
$result = mysqli_query($db, $sql3);

if ($result) {
    $afectados = mysqli_affected_rows($db);
    echo json_encode([
        'status'    => 'ok',
        'message'   => "Reinicio completado. $afectados contenedor(es) simulado(s) reseteados.",
        'afectados' => $afectados
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al reiniciar: ' . mysqli_error($db)
    ]);
}
