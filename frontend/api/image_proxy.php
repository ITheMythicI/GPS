<?php
if (!isset($_GET['path'])) {
    http_response_code(400);
    exit;
}
$path = $_GET['path'];
$url = 'http://10.0.2.8/' . ltrim($path, '/');

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($response !== false) {
    header('Content-Type: ' . $contentType);
    echo $response;
} else {
    http_response_code(404);
}
?>