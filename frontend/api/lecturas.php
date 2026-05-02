<?php

// Recibir datos del ESP32
$data = $_POST;

// Enviar al backend privado
$url = "http://10.0.2.8/envioArchivos.php";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo "Error proxy: " . curl_error($ch);
    exit;
}

curl_close($ch);

// Responder al ESP32
echo $response;