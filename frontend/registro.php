<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $postData['action'] = 'registro';

    $ch = curl_init("https://129.146.115.127/api/router.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  //httpCode Verificar el codigo de error
    
    $data = json_decode($response, true);
    curl_close($ch);

    if ($httpCode === 200 && isset($data['status']) && $data['status'] === 'success') {
        $success = "Usuario registrado correctamente.";
    } else {
        $error = $data['message'] ?? "Fallo en el registro (Código HTTP: $httpCode). Detalle: " . substr(strip_tags($response), 0, 200);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesando Registro</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        const successMsg = <?php echo json_encode($success); ?>;
        const errorMsg = <?php echo json_encode($error); ?>;

        if (successMsg) {
            Swal.fire({ 
                icon: 'success', 
                title: '¡Éxito!', 
                text: successMsg 
            }).then(() => { 
                window.location.href = 'landing.html'; 
            });
        } else if (errorMsg) {
            console.error("Error detallado del servidor:", errorMsg);
            Swal.fire({ 
                icon: 'error', 
                title: 'Error de Registro', 
                text: errorMsg,
                footer: 'Abre la consola (F12) para ver el reporte completo.'
            });
        }
    </script>
</body>
</html>