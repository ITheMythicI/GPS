<?php
session_start();
$error = '';
$success = '';
$debug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $postData['action'] = 'registro';

    $ch = curl_init("http://localhost/frontend/api/router.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $data = "Fallp de coneccion cURL: " . curl_error($ch);
    } else {
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] === 'success') {
            $success = " Usuario Registrado Correctamente";
        } else {
            $error = $data['message'] ?? "Error desconocido en el backend";
            $debug = "Codigo HTTP: $httpCode | Respuesta: " . htmlspecialchars($response);
        }
    }
    curl_close($ch);
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
            Swal.fire({ 
                icon: 'error', 
                title: 'Fallo en el Registro', 
                text: '<?php echo $error; ?>',
                footer: '<b>Debug:</b> <?php echo $debug; ?>'
            }).then(() => { 
                // Comentamos la redirección para que puedas leer el error en la consola si es necesario
                // window.location.href = 'landing.html'; 
            });
        <?php endif; ?>
    </script>
</body>
</html>