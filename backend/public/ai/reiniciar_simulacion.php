<?php
/**
 * reiniciar_simulacion.php
 * Resetea todos los contenedores de tipo "Simulado" a estado vacío.
 * Solo debe llamarse desde el proxy con rol administrador.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/database.php';

// Resetear contenedores simulados: distancia alta = vacío (55cm de 60cm)
$sql = "UPDATE Contenedores SET distanciaBoteTapa = 55 WHERE tipo_sensor = 'Simulado'";
$result = mysqli_query($db, $sql);

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
