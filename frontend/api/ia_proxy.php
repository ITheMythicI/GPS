<?php
/**
 * frontend/api/ia_proxy.php
 * ──────────────────────────
 * Proxy cURL para los endpoints de IA del backend privado.
 * Sigue el mismo patrón que router.php y lecturas.php existentes.
 *
 * Acciones disponibles (GET param ?action=...):
 *   - clasificar  →  GET  http://10.0.2.8/backend/ai/clasificar.php
 *   - rutas       →  GET  http://10.0.2.8/backend/ai/rutas.php
 *   - reporte     →  POST http://10.0.2.8/backend/ai/reporte.php
 *
 * Para 'rutas' se puede pasar ?prioridad=media para incluir prioridad media.
 * Para 'reporte' el body JSON se retransmite tal cual al backend.
 */

header('Content-Type: application/json');

// ── Validar acción ─────────────────────────────────────────────────────────────
$action = $_GET['action'] ?? '';
$acciones_permitidas = ['clasificar', 'rutas', 'reporte'];

if (!in_array($action, $acciones_permitidas)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "Acción '$action' no válida"]);
    exit;
}

// ── Construir URL del backend ─────────────────────────────────────────────────
$backend_base = 'http://10.0.2.8/backend/ai';

$url_backend = match($action) {
    'clasificar' => "$backend_base/clasificar.php",
    'rutas'      => "$backend_base/rutas.php" . (isset($_GET['prioridad']) ? '?prioridad=' . urlencode($_GET['prioridad']) : ''),
    'reporte'    => "$backend_base/reporte.php",
};

// ── Reenviar la request ────────────────────────────────────────────────────────
$ch = curl_init($url_backend);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT,        25);

if ($action === 'reporte') {
    // Reenviar body JSON del frontend al backend
    $body = file_get_contents('php://input');
    curl_setopt($ch, CURLOPT_POST,        true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,  $body ?: '{}');
    curl_setopt($ch, CURLOPT_HTTPHEADER,  ['Content-Type: application/json']);
} else {
    // GET simple para clasificar y rutas
    curl_setopt($ch, CURLOPT_HTTPGET, true);
}

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error     = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => "Error de conexión con el backend: $error"]);
    exit;
}

// Propagar el código HTTP del backend
http_response_code($http_code);
echo $response;
