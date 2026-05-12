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

$fecha_actual = date('d \d\e F \d\e Y');
$nombre_analista = "Erick"; // Puedes cambiar esto por tu nombre real

$prompt = "Actúa como el Analista del Sistema BIN. Genera un reporte ejecutivo en español siguiendo este formato EXACTO al inicio:\n\n" .
          "**REPORTE EJECUTIVO DEL SISTEMA BIN**\n" .
          "**Fecha:** $fecha_actual\n" .
          "**Analista:** $nombre_analista\n\n" .
          "Contenido del reporte basado en estos datos:\n$detalle\n\n" .
          "Instrucciones: Provee un resumen breve, identifica contenedores críticos y da una recomendación operativa.";

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
