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
        // Usamos json_encode para evitar que caracteres raros rompan el JS
        const errorMsg = <?php echo json_encode($error); ?>;
        const successMsg = <?php echo json_encode($success); ?>;
        const debugMsg = <?php echo json_encode($debug_info); ?>;

        if (successMsg) {
            Swal.fire({ 
                icon: 'success', 
                title: '¡Éxito!', 
                text: successMsg 
            }).then(() => { window.location.href = 'landing.html'; });
        } else if (errorMsg) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Fallo en el Registro', 
                text: errorMsg,
                footer: debugMsg ? `<small><b>Debug:</b> ${debugMsg}</small>` : ''
            });
        }
    </script>
</body>
</html>