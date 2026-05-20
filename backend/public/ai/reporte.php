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
$criterios       = $body['criterios']       ?? [];
$id_usuario      = (int)($body['id_usuario'] ?? $_GET['id_usuario'] ?? 0);

require __DIR__ . '/../../includes/database.php';

if (empty($clasificaciones)) {
    $sql = "SELECT c.id_contenedor, c.ubicacion, r.prioridad, r.volumen_pct, r.temperatura, r.humedad, r.peso_kg FROM ResultadosIA r INNER JOIN Contenedores c ON c.id_contenedor = r.id_contenedor WHERE r.fecha_clasificacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY r.fecha_clasificacion DESC LIMIT 20";
    $res = mysqli_query($db, $sql);
    while ($fila = mysqli_fetch_assoc($res)) { $clasificaciones[] = $fila; }
}

$detalle = "";
foreach ($clasificaciones as $c) {
    $detalle .= sprintf("- %s: %s (%.1f%% lleno)\n", $c['ubicacion'] ?? '?', $c['prioridad'] ?? '?', $c['volumen_pct'] ?? 0);
}

$fecha_actual = date('d \d\e F \d\e Y');
$nombre_analista = 'Analista BIN';
$rol_analista = 'usuario';
if ($id_usuario > 0) {
    $q = mysqli_prepare($db, "SELECT nombre, rol FROM Usuarios WHERE id_usuario = ? LIMIT 1");
    mysqli_stmt_bind_param($q, 'i', $id_usuario);
    mysqli_stmt_execute($q);
    $r = mysqli_stmt_get_result($q);
    $u = mysqli_fetch_assoc($r);
    mysqli_stmt_close($q);
    if ($u) {
        $nombre_analista = $u['nombre'] ?? $nombre_analista;
        $rol_analista = $u['rol'] ?? $rol_analista;
    }
}
if ($rol_analista !== 'administrador') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado para generar reportes IA']);
    exit;
}

if (empty($criterios)) {
    $criterios = [
        "Humedad > 45% y Densidad > 300 kg/m3 => ORGÁNICO",
        "Humedad < 30% y Densidad < 80 kg/m3 => PLÁSTICO",
        "Humedad < 30% y Densidad < 180 kg/m3 => PAPEL/CARTÓN",
        "Humedad < 35% y Densidad > 250 kg/m3 => VIDRIO/METAL"
    ];
}

$criterios_texto = "";
foreach ($criterios as $cr) {
    $criterios_texto .= "- " . $cr . "\n";
}

$prompt = "Actúa como el Analista del Sistema BIN. Genera un reporte ejecutivo en español siguiendo este formato EXACTO al inicio:\n\n" .
          "**REPORTE EJECUTIVO DEL SISTEMA BIN**\n" .
          "**Fecha:** $fecha_actual\n" .
          "**Analista:** $nombre_analista\n\n" .
          "**Rol del solicitante:** $rol_analista\n\n" .
          "Contenido del reporte basado en estos datos:\n$detalle\n\n" .
          "Criterios de inferencia vigentes:\n$criterios_texto\n" .
          "Instrucciones: Provee un resumen breve, identifica contenedores críticos, da una recomendación operativa y agrega una sección llamada 'Predicción de tipo de residuo por contenedor' aplicando explícitamente esos criterios a cada contenedor listado.";

$gemini_url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$api_key}";

$payload = json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]);

$ch = curl_init($gemini_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(504);
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo contactar a Gemini dentro del tiempo límite',
        'detalle' => $curl_error
    ]);
    exit;
}

if ($status !== 200) {
    echo json_encode(['status' => 'error', 'message' => 'Error API', 'detalle' => json_decode($response, true)]);
} else {
    $data = json_decode($response, true);
    $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sin respuesta";
    echo json_encode(['status' => 'ok', 'reporte' => $texto, 'fecha' => date('d/m/Y H:i')]);
}
