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
    // Ruta corregida: backend/public/ai/ → backend/includes/
    require __DIR__ . '/../../includes/database.php';

    $sql = "
        SELECT c.id_contenedor, c.ubicacion,
               r.prioridad, r.score, r.volumen_pct, r.temperatura, r.humedad, r.peso_kg,
               r.fecha_clasificacion
        FROM ResultadosIA r
        INNER JOIN Contenedores c ON c.id_contenedor = r.id_contenedor
        WHERE r.fecha_clasificacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY r.fecha_clasificacion DESC LIMIT 20
    ";
    $res = mysqli_query($db, $sql);
    while ($fila = mysqli_fetch_assoc($res)) {
        $clasificaciones[] = $fila;
    }
}

$total = count($clasificaciones);
$alta  = count(array_filter($clasificaciones, fn($c) => ($c['prioridad'] ?? '') === 'alta'));
$media = count(array_filter($clasificaciones, fn($c) => ($c['prioridad'] ?? '') === 'media'));
$baja  = count(array_filter($clasificaciones, fn($c) => ($c['prioridad'] ?? '') === 'baja'));

$detalle = '';
foreach ($clasificaciones as $c) {
    $detalle .= sprintf(
        "- Contenedor #%s (%s): prioridad=%s, llenado=%.1f%%, temp=%.1f°C, humedad=%.1f%%, peso=%.1fkg\n",
        $c['id_contenedor'] ?? '?', $c['ubicacion'] ?? 'Desconocida',
        strtoupper($c['prioridad'] ?? '?'), floatval($c['volumen_pct'] ?? 0),
        floatval($c['temperatura'] ?? 0), floatval($c['humedad'] ?? 0), floatval($c['peso_kg'] ?? 0)
    );
}

$info_ruta = '';
if (!empty($ruta['ruta_ordenada'])) {
    $paradas = array_map(fn($p) => "#" . ($p['id_contenedor'] ?? '?') . " (" . ($p['ubicacion'] ?? '') . ")", $ruta['ruta_ordenada']);
    $info_ruta = "\nRuta óptima: " . implode(' → ', $paradas) . "\nDistancia total: " . floatval($ruta['distancia_km'] ?? 0) . " km";
}

$fecha = date('d/m/Y H:i');
$prompt = "Eres el sistema de análisis del proyecto BIN (gestión de contenedores de basura).\nGenera un reporte ejecutivo conciso en español.\n\nDATOS ($fecha):\n- Total contenedores: $total | Alta: $alta | Media: $media | Baja: $baja\n\nDETALLE:\n$detalle$info_ruta\n\nEstructura:\n1. Resumen ejecutivo (2-3 oraciones)\n2. Contenedores críticos (prioridad ALTA únicamente)\n3. Observaciones ambientales si hay valores inusuales\n4. Recomendación operativa\n\nUsa solo los datos proporcionados. Formato texto plano con emojis moderados.";

$gemini_url     = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$api_key}";
$gemini_payload = json_encode([
    'contents' => [['parts' => [['text' => $prompt]]]],
    'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 1024]
]);

$ch = curl_init($gemini_url);
curl_setopt($ch, CURLOPT_POST,           true);
curl_setopt($ch, CURLOPT_POSTFIELDS,     $gemini_payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT,        20);
curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
$gemini_response = curl_exec($ch);
$gemini_status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error      = curl_error($ch);
curl_close($ch);

if ($gemini_response === false || $gemini_status !== 200) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Error Gemini API', 'detalle' => $curl_error ?: "HTTP $gemini_status"]);
    exit;
}

$gemini_data  = json_decode($gemini_response, true);
$texto        = $gemini_data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$texto) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gemini no devolvió texto válido']);
    exit;
}

echo json_encode([
    'status'        => 'ok',
    'reporte'       => $texto,
    'fecha_reporte' => $fecha,
    'resumen'       => compact('total', 'alta', 'media', 'baja'),
]);
