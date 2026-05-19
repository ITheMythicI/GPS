<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $id = $_POST['id'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $id_zona = $_POST['id_zona'] ?? null;
    $lat = (float)($_POST['lat'] ?? 0);
    $lng = (float)($_POST['lng'] ?? 0);
    $es_real = (int)($_POST['es_real'] ?? 0);

    if (!$ubicacion) {
        throw new Exception('La ubicación es obligatoria');
    }

    if (!empty($id)) {
        // UPDATE
        $query = "UPDATE Contenedores SET ubicacion = ?, id_zona = ?, latitud = ?, longitud = ?, es_real = ? WHERE id_contenedor = ?";
        $stmt = mysqli_prepare($db, $query);
        if (!$stmt) throw new Exception("Error preparando la consulta update: " . mysqli_error($db));
        mysqli_stmt_bind_param($stmt, 'siddii', $ubicacion, $id_zona, $lat, $lng, $es_real, $id);
        $msg = 'Contenedor actualizado correctamente';
    } else {
        // INSERT
        $query = "INSERT INTO Contenedores (ubicacion, id_zona, latitud, longitud, es_real, estado) VALUES (?, ?, ?, ?, ?, 'Vacío')";
        $stmt = mysqli_prepare($db, $query);
        if (!$stmt) throw new Exception("Error preparando la consulta insert: " . mysqli_error($db));
        mysqli_stmt_bind_param($stmt, 'siddi', $ubicacion, $id_zona, $lat, $lng, $es_real);
        $msg = 'Contenedor creado correctamente';
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'ok', 'message' => $msg]);
    } else {
        throw new Exception("Error al ejecutar: " . mysqli_stmt_error($stmt));
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>
