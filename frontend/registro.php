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
        // Guardamos la respuesta cruda si el JSON falla para depurar
        $error = $data['message'] ?? "Error desconocido en el backend: " . strip_tags($response);
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
        // Usamos json_encode para pasar los mensajes de PHP a JS de forma segura
        const successMsg = <?php echo json_encode($success); ?>;
        const errorMsg = <?php echo json_encode($error); ?>;

        if (successMsg) {
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: successMsg })
            .then(() => { window.location.href = 'landing.html'; });
        } else if (errorMsg) {
            console.error("Detalle del error:", errorMsg);
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: errorMsg,
                footer: 'Consulta la consola (F12) para detalles técnicos.'
            });
        }
    </script>
</body>
</html>