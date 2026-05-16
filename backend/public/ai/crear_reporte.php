<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// DEBUG: Loguear lo que llega
file_put_contents(__DIR__ . '/../../debug_reporte.txt', "POST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_contenedor = $_POST['id_contenedor'] ?? null;
$id_usuario = $_POST['id_usuario'] ?? null;
$tipo = $_POST['tipo'] ?? 'Otro';
$descripcion = $_POST['descripcion'] ?? '';
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;

if (!$id_contenedor) {
    echo json_encode(['status' => 'error', 'message' => 'Contenedor no especificado']);
    exit;
}

// Manejo de imagen
$foto_url = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../../uploads/reportes/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('rep_') . '.' . $ext;
    $target = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        $foto_url = 'uploads/reportes/' . $filename;
    }
}

$query = "INSERT INTO ReportesIncidencias (id_contenedor, id_usuario, tipo_incidencia, descripcion, foto_url, lat_reporte, lng_reporte) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'iisssdd', $id_contenedor, $id_usuario, $tipo, $descripcion, $foto_url, $lat, $lng);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'ok', 'message' => 'Reporte enviado con éxito']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
