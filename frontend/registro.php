<?php
session_start();
$error = '';
$success = '';
$debug_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $postData['action'] = 'registro';

    $ch = curl_init("http:/localhost/frontend/api/router.php");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = "Fallo de conexion interna: " . curl_error($ch);
    } else {
        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['status']) && $data['status'] === 'success') {
            $success = "Usuario registrado correctamente";
        } else {
            if (json_last_error() !== JSON_ERROR_NONE) {
                $debug_info = strip_tags($response);
            } else {
                $error = $data['message'] ?? "No se pudo completar el registro";
            }
        }
    }
    curl_close($ch)
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Solutions - Procesando Registro</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
    </style>
</head>
<body>

    <script>
        const successMsg = <?php echo json_encode($success); ?>;
        const errorMsg = <?php echo json_encode($error); ?>;
        const debugData = <?php echo json_encode($debug_info); ?>;

        if (successMsg) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: successMsg,
                confirmButtonColor: '#4CAF50'
            }).then(() => {
                window.location.href = 'landing.html';
            });
        } else if (errorMsg) {
            if (debugData) {
                console.error("Detalle técnico del error:", debugData);
            }

            Swal.fire({
                icon: 'error',
                title: 'Error en el Registro',
                text: errorMsg,
                footer: '<small>Revisa la consola para más detalles técnicos</small>',
                confirmButtonColor: '#d33'
            }).then(() => {
                window.history.back();
            });
        }
    </script>
</body>
</html>