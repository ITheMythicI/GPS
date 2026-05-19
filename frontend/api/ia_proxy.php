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
$acciones_permitidas = ['clasificar', 'rutas', 'reporte', 'contenedores', 'simular', 'zonas', 'migrar', 'test_db', 'normalizar', 'guardar_zona', 'guardar_contenedor', 'actualizar_nombres', 'reparar_zonas', 'borrar_zona', 'borrar_contenedor', 'crear_reporte', 'migrar_reportes', 'obtener_reportes', 'obtener_actividad', 'migrar_actividad', 'registrar_actividad', 'obtener_ajustes', 'guardar_ajustes', 'subir_foto_perfil', 'migrar_ajustes', 'imagen', 'reiniciar_simulacion'];

if (!in_array($action, $acciones_permitidas)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "Acción '$action' no válida"]);
    exit;
}

// ── Construir URL del backend ─────────────────────────────────────────────────
$backend_base = 'http://10.0.2.8/ai';

$url_backend = match($action) {
    'clasificar' => "$backend_base/clasificar.php",
    'rutas'        => "$backend_base/rutas.php" . (isset($_GET['prioridad']) ? '?prioridad=' . urlencode($_GET['prioridad']) : ''),
    'reporte'      => "$backend_base/reporte.php",
    'contenedores' => "http://10.0.2.8/obtenerContenedores.php",
    'simular'      => "$backend_base/simulador.php",
    'zonas'        => "http://10.0.2.8/obtenerZonas.php",
    'migrar'       => "$backend_base/run_migration.php",
    'test_db'      => "$backend_base/test_db.php",
    'normalizar'   => "$backend_base/normalizar_sensores.php",
    'guardar_zona' => "$backend_base/guardar_zona.php",
    'guardar_contenedor' => "$backend_base/guardar_contenedor.php",
    'actualizar_nombres' => "$backend_base/update_zones.php",
    'reparar_zonas'      => "$backend_base/reparar_zonas.php",
    'borrar_zona'        => "$backend_base/borrar_zona.php",
    'borrar_contenedor'  => "$backend_base/borrar_contenedor.php",
    'crear_reporte'      => "$backend_base/crear_reporte.php",
    'migrar_reportes'    => "$backend_base/migration_reportes.php",
    'obtener_reportes'   => "$backend_base/obtener_reportes.php",
    'obtener_actividad'  => "$backend_base/obtener_actividad.php",
    'migrar_actividad'   => "$backend_base/migration_actividad.php",
    'registrar_actividad' => "$backend_base/registrar_actividad.php",
    'obtener_ajustes'    => "$backend_base/obtener_ajustes.php",
    'guardar_ajustes'    => "$backend_base/guardar_ajustes.php",
    'subir_foto_perfil'  => "$backend_base/subir_foto_perfil.php",
    'migrar_ajustes'     => "$backend_base/migration_ajustes.php",
    'reiniciar_simulacion' => "$backend_base/reiniciar_simulacion.php",
};


session_start();
$id_usuario_sesion = $_SESSION['id_usuario'] ?? 0;

// Añadir parámetros GET adicionales (como id_contenedor, y id_usuario de sesión)
$queryParams = $_GET;
unset($queryParams['action']); // Quitar 'action'
$queryParams['id_usuario'] = $id_usuario_sesion;

if (!empty($queryParams)) {
    $separator = (strpos($url_backend, '?') !== false) ? '&' : '?';
    $url_backend .= $separator . http_build_query($queryParams);
}

// ── Reenviar la request ────────────────────────────────────────────────────────
$ch = curl_init($url_backend);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT,        25);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    curl_setopt($ch, CURLOPT_POST, true);
    
    // Si es JSON
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $body = file_get_contents('php://input');
        $body_arr = json_decode($body, true) ?: [];
        $body_arr['id_usuario'] = $id_usuario_sesion;
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body_arr));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } else {
        // Si es FormData o x-www-form-urlencoded
        $post_data = $_POST;
        $post_data['id_usuario'] = $id_usuario_sesion;
        
        // Adjuntar archivos si existen
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $post_data[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
                }
            }
        }
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
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

// Intentar validar si es JSON, si no, envolverlo en un error
json_decode($response);
if (json_last_error() === JSON_ERROR_NONE) {
    // Si estamos subiendo perfil y fue exitoso, actualizar sesión local
    if ($action === 'subir_foto_perfil') {
        $resObj = json_decode($response, true);
        if (isset($resObj['status']) && $resObj['status'] === 'ok') {
            $_SESSION['dark_mode'] = $_POST['dark_mode'] ?? 0;
            if (!empty($resObj['foto_url'])) {
                $_SESSION['foto_perfil'] = $resObj['foto_url'];
            }
        }
    }
    echo $response;
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'El backend devolvió una respuesta no válida (posible error de PHP)',
        'debug' => substr(strip_tags($response), 0, 200)
    ]);
}

