<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $postData['action'] = 'registro';

    $ch = curl_init("http://localhost/frontend/api/router.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    if (isset($data['status']) && $data['status'] === 'success') {
        $success = "Usuario registrado correctamente.";
    } else {
        $error = $data['message'] ?? "Error al registrarse.";
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
        <?php if ($success): ?>
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: '<?php echo $success; ?>' })
            .then(() => { window.location.href = 'landing.html'; });
        <?php elseif ($error): ?>
            Swal.fire({ icon: 'error', title: 'Error', text: '<?php echo $error; ?>' })
            .then(() => { window.location.href = 'landing.html'; });
        <?php endif; ?>
    </script>
</body>
</html>