<?php
if (!isset($_GET['path'])) {
    http_response_code(400);
    exit;
}
$path = $_GET['path'];
// Codificar cada segmento para soportar espacios en la ruta
$segments = explode('/', ltrim($path, '/'));
$encoded_segments = array_map('rawurlencode', $segments);
$url = 'http://10.0.2.8/' . implode('/', $encoded_segments);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($response !== false && $http_code == 200) {
    header('Content-Type: ' . $contentType);
    echo $response;
} else {
    // Intentar con el subdirectorio 'ai' si falla en la raiz
    $url2 = 'http://10.0.2.8/ai/' . implode('/', $encoded_segments);
    $ch2 = curl_init($url2);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HEADER, false);
    $response2 = curl_exec($ch2);
    $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $contentType2 = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
    curl_close($ch2);

    if ($response2 !== false && $http_code2 == 200) {
        header('Content-Type: ' . $contentType2);
        echo $response2;
    } else {
        http_response_code(404);
        echo 'Not found: ' . $url;
    }
}
?>