<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$ch = curl_init('http://10.0.2.8/ai/simulador.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

header('Content-Type: text/plain');
echo "=== SIMULATION DIAGNOSTIC ===\n";
echo "HTTP CODE: $code\n";
if ($err) {
    echo "CURL ERROR: $err\n";
}
echo "RAW RESPONSE:\n";
echo $res ? $res : "[Empty Response]";
