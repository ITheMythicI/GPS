<?php
/**
 * backend/ai/reporte.php
 * ────────────────────────
 * Genera un reporte de texto con Google Gemini API.
 *
 * Flujo:
 *   1. Recibe vía POST: resultados de clasificación + datos de ruta (JSON)
 *   2. Construye un prompt contextualizado con los datos reales del sistema
 *   3. Llama a Gemini API (REST directo, sin SDK)
 *   4. Devuelve el texto del reporte como JSON
 *
 * IMPORTANTE: Gemini solo interpreta y genera texto. NO toma decisiones
 * críticas. Los cálculos ya fueron realizados por Python.
 *
 * Configuración:
 *   - La clave API se lee de la variable de entorno GEMINI_API_KEY
 *   - Configúrala en la VM Backend: export GEMINI_API_KEY="tu_clave"
 *   - O en /etc/environment para que persista entre reinicios
 *
 * Llamado por: frontend/api/ia_proxy.php (VM Frontend)
 * Método:      POST
 * Body:        JSON { "clasificaciones": [...], "ruta": {...} }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ── Leer clave API de variable de entorno (nunca en código) ───────────────────
$api_key = getenv('GEMINI_API_KEY');

if (!$api_key) {
    http_response_code(503);
    echo json_encode([
        'status'  => 'error',
        'message' => 'GEMINI_API_KEY no configurada en el servidor. Agrégala con: export GEMINI_API_KEY="tu_clave"'
    ]);
    exit;
}

// ── Leer body del request ─────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);

$clasificaciones = $body['clasificaciones'] ?? [];
$ruta            = $body['ruta']            ?? [];

// Si no llegan datos del body, los leemos de MySQL como fallback
if (empty($clasificaciones)) {
    require __DIR__ . '/../includes/database.php';

    $sql = "
        SELECT
            c.id_contenedor, c.ubicacion,
            r.prioridad, r.score, r.volumen_pct, r.temperatura, r.humedad, r.peso_kg,
            r.fecha_clasificacion
        FROM ResultadosIA r
        INNER JOIN Contenedores c ON c.id_contenedor = r.id_contenedor
        WHERE r.fecha_clasificacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY r.fecha_clasificacion DESC
        LIMIT 20
    ";
    $res = mysqli_query($db, $sql);
    while ($fila = mysqli_fetch_assoc($res)) {
        $clasificaciones[] = $fila;
    }
}

// ── Construir el prompt ───────────────────────────────────────────────────────
$total     = count($clasificaciones);
$alta      = count(array_filter($clasificaciones, fn($c) => ($c['prioridad'] ?? '') === 'alta'));
$media     = count(array_filter($clasificaciones, fn($c) => ($c['prioridad'] ?? '') === 'media'));
$baja      = count(array_filter($clasificaciones, fn($c) => ($c['prioridad'] ?? '') === 'baja'));

$detalle_contenedores = '';
foreach ($clasificaciones as $c) {
    $detalle_contenedores .= sprintf(
        "- Contenedor #%s (%s): prioridad=%s, llenado=%.1f%%, temp=%.1f°C, humedad=%.1f%%, peso=%.1fkg\n",
        $c['id_contenedor'] ?? '?',
        $c['ubicacion']     ?? 'Desconocida',
        strtoupper($c['prioridad'] ?? '?'),
        floatval($c['volumen_pct']  ?? 0),
        floatval($c['temperatura']  ?? 0),
        floatval($c['humedad']      ?? 0),
        floatval($c['peso_kg']      ?? 0)
    );
}

$info_ruta = '';
if (!empty($ruta['ruta_ordenada'])) {
    $paradas = array_map(
        fn($p) => "#" . ($p['id_contenedor'] ?? '?') . " (" . ($p['ubicacion'] ?? '') . ")",
        $ruta['ruta_ordenada']
    );
    $info_ruta = sprintf(
        "\nRuta óptima calculada: %s\nDistancia total estimada: %.3f km",
        implode(' → ', $paradas),
        floatval($ruta['distancia_km'] ?? 0)
    );
}

$fecha_reporte = date('d/m/Y H:i');

$prompt = <<<PROMPT
Eres el sistema de análisis inteligente del proyecto BIN (gestión de contenedores de basura en el Tecnológico de Laguna). 
Genera un reporte ejecutivo conciso en español, profesional y orientado a la operación.

DATOS DEL SISTEMA (generados automáticamente el $fecha_reporte):
- Total de contenedores monitoreados: $total
- Prioridad ALTA (requieren recolección inmediata): $alta
- Prioridad MEDIA (recolección pronto): $media
- Prioridad BAJA (sin urgencia): $baja

DETALLE POR CONTENEDOR:
$detalle_contenedores
$info_ruta

INSTRUCCIONES PARA EL REPORTE:
1. Resumen ejecutivo (2-3 oraciones)
2. Contenedores críticos que requieren atención inmediata (solo los de prioridad ALTA)
3. Observaciones sobre condiciones ambientales (temperatura/humedad) si hay valores inusuales
4. Recomendación operativa para el día
5. Conclusión breve

NO inventes datos. Usa solo los datos proporcionados arriba.
Usa emojis moderadamente para hacer el reporte más legible.
Formato: texto plano con saltos de línea, sin markdown complejo.
PROMPT;

// ── Llamar a Gemini API ───────────────────────────────────────────────────────
$gemini_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$api_key}";

$gemini_payload = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature'     => 0.4,
        'maxOutputTokens' => 1024,
    ]
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
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al contactar Gemini API',
        'detalle' => $curl_error ?: "HTTP $gemini_status"
    ]);
    exit;
}

$gemini_data = json_decode($gemini_response, true);

// Extraer texto del response de Gemini
$texto_reporte = $gemini_data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$texto_reporte) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gemini no devolvió texto válido', 'raw' => $gemini_data]);
    exit;
}

echo json_encode([
    'status'         => 'ok',
    'reporte'        => $texto_reporte,
    'fecha_reporte'  => $fecha_reporte,
    'resumen'        => compact('total', 'alta', 'media', 'baja'),
]);
