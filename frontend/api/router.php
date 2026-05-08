<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Destino (Login/Registro)
    $action = $_POST['action'] ?? '';
    $url_backend = "";

    if ($action === 'login') {
        $url_backend = "http://10.0.2.8/auth/procesar_login.php";
    } elseif ($action === 'registro') {
        $url_backend = "http://10.0.2.8/auth/procesar_registro.php";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        exit;
    }

    //Reenvio de datos mediante el cURL
    $ch = curl_init($url_backend);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo $response;
    } else {
        echo json_encode(['status' => 'error', 'message' => "Error de conexión con el backend ($httpCode)"]);
    }
}
?>