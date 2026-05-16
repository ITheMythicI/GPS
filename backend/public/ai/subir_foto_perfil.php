<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

$id_usuario = $_POST['id_usuario'] ?? null;
if (!$id_usuario) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada']);
    exit;
}


$dark_mode = $_POST['dark_mode'] ?? 0;
$foto_url = null;

// Manejar foto si existe
if (!empty($_FILES['foto'])) {
    $upload_dir = __DIR__ . '/../../uploads/perfiles/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $filename = "perfil_" . $id_usuario . "_" . time() . "." . $ext;
    $target = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        $foto_url = 'uploads/perfiles/' . $filename;
    }
}

// Actualizar BD
if ($foto_url) {
    $query = "UPDATE Usuarios SET foto_perfil = ?, config_oscuro = ? WHERE id_usuario = ?";
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) mysqli_stmt_bind_param($stmt, 'sii', $foto_url, $dark_mode, $id_usuario);
} else {
    $query = "UPDATE Usuarios SET config_oscuro = ? WHERE id_usuario = ?";
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) mysqli_stmt_bind_param($stmt, 'ii', $dark_mode, $id_usuario);
}

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error de BD. ¿Corriste la migración? ' . mysqli_error($db)]);
    exit;
}


if (mysqli_stmt_execute($stmt)) {
    // Actualizar sesión para el frontend
    $_SESSION['dark_mode'] = $dark_mode;
    if ($foto_url) $_SESSION['foto_perfil'] = $foto_url;
    
    echo json_encode(['status' => 'ok', 'foto_url' => $foto_url]);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
?>
