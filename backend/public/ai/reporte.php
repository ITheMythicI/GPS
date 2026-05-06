<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$api_key = getenv('GEMINI_API_KEY');
if (!$api_key) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'GEMINI_API_KEY no configurada en el servidor']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$clasificaciones = $body['clasificaciones'] ?? [];
$ruta            = $body['ruta']            ?? [];

if (empty($clasificaciones)) {
    require __DIR__ . '/../../includes/database.php';
    $sql = "SELECT c.id_contenedor, c.ubicacion, r.prioridad, r.volumen_pct, r.temperatura, r.humedad, r.peso_kg FROM ResultadosIA r INNER JOIN Contenedores c ON c.id_contenedor = r.id_contenedor WHERE r.fecha_clasificacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY r.fecha_clasificacion DESC LIMIT 20";
    $res = mysqli_query($db, $sql);
    while ($fila = mysqli_fetch_assoc($res)) { $clasificaciones[] = $fila; }
}

$detalle = "";
foreach ($clasificaciones as $c) {
    $detalle .= sprintf("- %s: %s (%.1f%% lleno)\n", $c['ubicacion'] ?? '?', $c['prioridad'] ?? '?', $c['volumen_pct'] ?? 0);
}

$prompt = "Eres el analista del sistema BIN. Genera un reporte ejecutivo breve en español:\n$detalle";
$gemini_url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$api_key}";

$payload = json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]);

$ch = curl_init($gemini_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status !== 200) {
    echo json_encode(['status' => 'error', 'message' => 'Error API', 'detalle' => json_decode($response, true)]);
} else {
    $data = json_decode($response, true);
    $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sin respuesta";
    echo json_encode(['status' => 'ok', 'reporte' => $texto, 'fecha' => date('d/m/Y H:i')]);
}
