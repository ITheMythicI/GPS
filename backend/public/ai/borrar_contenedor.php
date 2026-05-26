<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_contenedor = $_POST['id_contenedor'] ?? null;

if (!$id_contenedor) {
    echo json_encode(['status' => 'error', 'message' => 'ID de contenedor requerido']);
    exit;
}

// Activar excepciones en mysqli para usar try-catch de manera limpia
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Iniciar transacción
    mysqli_begin_transaction($db);

    // 1. Obtener los IDs de los sensores asociados a este contenedor
    $querySensores = "SELECT id_sensor FROM Sensores WHERE id_contenedor = ?";
    $stmtSensores = mysqli_prepare($db, $querySensores);
    mysqli_stmt_bind_param($stmtSensores, 'i', $id_contenedor);
    mysqli_stmt_execute($stmtSensores);
    $resultSensores = mysqli_stmt_get_result($stmtSensores);
    $sensorIds = [];
    while ($row = mysqli_fetch_assoc($resultSensores)) {
        $sensorIds[] = (int)$row['id_sensor'];
    }
    mysqli_stmt_close($stmtSensores);

    // 2. Si existen sensores, eliminar sus LecturasSensores primero
    if (!empty($sensorIds)) {
        $placeholders = implode(',', array_fill(0, count($sensorIds), '?'));
        $queryLecturas = "DELETE FROM LecturasSensores WHERE id_sensor IN ($placeholders)";
        $stmtLecturas = mysqli_prepare($db, $queryLecturas);
        
        $types = str_repeat('i', count($sensorIds));
        mysqli_stmt_bind_param($stmtLecturas, $types, ...$sensorIds);
        mysqli_stmt_execute($stmtLecturas);
        mysqli_stmt_close($stmtLecturas);
    }

    // 3. Eliminar los sensores en Sensores
    $queryDelSensores = "DELETE FROM Sensores WHERE id_contenedor = ?";
    $stmtDelSensores = mysqli_prepare($db, $queryDelSensores);
    mysqli_stmt_bind_param($stmtDelSensores, 'i', $id_contenedor);
    mysqli_stmt_execute($stmtDelSensores);
    mysqli_stmt_close($stmtDelSensores);

    // 4. Eliminar referencias del contenedor en RutaContenedores
    $queryRutas = "DELETE FROM RutaContenedores WHERE id_contenedor = ?";
    $stmtRutas = mysqli_prepare($db, $queryRutas);
    mysqli_stmt_bind_param($stmtRutas, 'i', $id_contenedor);
    mysqli_stmt_execute($stmtRutas);
    mysqli_stmt_close($stmtRutas);

    // 5. Eliminar referencias en ResultadosIA (por si acaso, aunque tenga ON DELETE CASCADE)
    $queryIA = "DELETE FROM ResultadosIA WHERE id_contenedor = ?";
    $stmtIA = mysqli_prepare($db, $queryIA);
    mysqli_stmt_bind_param($stmtIA, 'i', $id_contenedor);
    mysqli_stmt_execute($stmtIA);
    mysqli_stmt_close($stmtIA);

    // 6. Eliminar finalmente el contenedor
    $queryCont = "DELETE FROM Contenedores WHERE id_contenedor = ?";
    $stmtCont = mysqli_prepare($db, $queryCont);
    mysqli_stmt_bind_param($stmtCont, 'i', $id_contenedor);
    mysqli_stmt_execute($stmtCont);
    mysqli_stmt_close($stmtCont);

    // Confirmar transacción
    mysqli_commit($db);
    echo json_encode(['status' => 'ok', 'message' => 'Contenedor y todas sus dependencias eliminados correctamente']);
} catch (Exception $e) {
    // Si algo falla, revertir los cambios
    mysqli_rollback($db);
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el contenedor: ' . $e->getMessage()]);
}
?>
