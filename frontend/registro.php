<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $postData['action'] = 'registro';

    $ch = curl_init("http://10.0.2.8/frontend/api/router.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['status']) && $data['status'] === 'success') {
        $success = "Usuario registrado correctamente";
    } else {
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "Error de redd ($httpCode). El servidor no devolvio un fotmato valido";
        } else {
            $error = $data['message'] ?? "Error desconocido en el registro";
        }
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
            Swal.fire({
                icon: 'error',
                title: 'Fallo en el Registro',
                text: errorMsg,
                footer: 'Verifica la conexión con el servidor 10.0.2.8'
            });
        }
    </script>
</body>
</html>