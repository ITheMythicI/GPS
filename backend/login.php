<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos para el Router
    $postData = $_POST;
    $postData['action'] = 'login';

    // Llamada interna al Router
    $ch = curl_init("http://localhost/frontend/api/router.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    if (isset($data['status']) && $data['status'] === 'success') {
        $_SESSION['id_usuario'] = $data['user']['id'];
        $_SESSION['nombre'] = $data['user']['nombre'];
        $_SESSION['rol'] = $data['user']['rol'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = $data['message'] ?? "Error de autenticación";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        <?php if ($error): ?>
            Swal.fire({ icon: 'error', title: 'Error', text: '<?php echo $error; ?>' })
            .then(() => { window.location.href = 'landing.html'; });
        <?php endif; ?>
    </script>
</body>
</html>