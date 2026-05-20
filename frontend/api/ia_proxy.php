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

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$id_usuario_sesion = $_SESSION['id_usuario'] ?? 0;
$rol_sesion = $_SESSION['rol'] ?? '';

if ($id_usuario_sesion <= 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada']);
    exit;
}

// ── Validar acción ─────────────────────────────────────────────────────────────
$action = $_GET['action'] ?? '';
$acciones_permitidas = ['clasificar', 'rutas', 'reporte', 'contenedores', 'simular', 'zonas', 'migrar', 'test_db', 'normalizar', 'guardar_zona', 'guardar_contenedor', 'actualizar_nombres', 'reparar_zonas', 'borrar_zona', 'borrar_contenedor', 'crear_reporte', 'migrar_reportes', 'obtener_reportes', 'obtener_actividad', 'migrar_actividad', 'registrar_actividad', 'obtener_ajustes', 'guardar_ajustes', 'subir_foto_perfil', 'cambiar_password', 'migrar_ajustes', 'imagen', 'reiniciar_simulacion', 'actualizar_estado_reporte', 'borrar_reporte'];

if (!in_array($action, $acciones_permitidas)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "Acción '$action' no válida"]);
    exit;
}

// Bloquear acciones administrativas si no es administrador
$acciones_admin = ['guardar_ajustes', 'guardar_zona', 'guardar_contenedor', 'borrar_zona', 'borrar_contenedor', 'migrar', 'normalizar', 'actualizar_estado_reporte', 'borrar_reporte', 'simular', 'reiniciar_simulacion', 'reporte', 'obtener_actividad', 'obtener_reportes'];
if (in_array($action, $acciones_admin) && $rol_sesion !== 'administrador') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

// ── Construir ruta de backend y candidatos de host ────────────────────────────
$ruta_backend = match($action) {
    'clasificar' => '/ai/clasificar.php',
    'rutas' => '/ai/rutas.php',
    'reporte' => '/ai/reporte.php',
    'contenedores' => '/obtenerContenedores.php',
    'simular' => '/ai/simulador.php',
    'zonas' => '/obtenerZonas.php',
    'migrar' => '/ai/run_migration.php',
    'test_db' => '/ai/test_db.php',
    'normalizar' => '/ai/normalizar_sensores.php',
    'guardar_zona' => '/ai/guardar_zona.php',
    'guardar_contenedor' => '/ai/guardar_contenedor.php',
    'actualizar_nombres' => '/ai/update_zones.php',
    'reparar_zonas' => '/ai/reparar_zonas.php',
    'borrar_zona' => '/ai/borrar_zona.php',
    'borrar_contenedor' => '/ai/borrar_contenedor.php',
    'crear_reporte' => '/ai/crear_reporte.php',
    'migrar_reportes' => '/ai/migration_reportes.php',
    'obtener_reportes' => '/ai/obtener_reportes.php',
    'obtener_actividad' => '/ai/obtener_actividad.php',
    'migrar_actividad' => '/ai/migration_actividad.php',
    'registrar_actividad' => '/ai/registrar_actividad.php',
    'obtener_ajustes' => '/ai/obtener_ajustes.php',
    'guardar_ajustes' => '/ai/guardar_ajustes.php',
    'subir_foto_perfil' => '/ai/subir_foto_perfil.php',
    'cambiar_password' => '/ai/cambiar_password.php',
    'migrar_ajustes' => '/ai/migration_ajustes.php',
    'reiniciar_simulacion' => '/ai/reiniciar_simulacion.php',
    'actualizar_estado_reporte' => '/ai/actualizar_estado_reporte.php',
    'borrar_reporte' => '/ai/borrar_reporte.php',
    'imagen' => '',
};

$backend_hosts = defined('BACKEND_URLS') ? BACKEND_URLS : [defined('BACKEND_URL') ? BACKEND_URL : 'http://10.0.2.8'];
$backend_hosts = array_values(array_unique(array_map(function ($h) {
    return rtrim((string)$h, '/');
}, $backend_hosts)));


// Añadir parámetros GET adicionales (como id_contenedor, y id_usuario de sesión)
$queryParams = $_GET;
unset($queryParams['action']); // Quitar 'action'
$queryParams['id_usuario'] = $id_usuario_sesion;

$response = false;
$http_code = 0;
$error = '';
$url_backend = '';
$raw_json_body = file_get_contents('php://input');

foreach ($backend_hosts as $backend_host) {
    $url_backend = $backend_host . $ruta_backend;

    if (!empty($queryParams)) {
        $separator = (strpos($url_backend, '?') !== false) ? '&' : '?';
        $url_backend .= $separator . http_build_query($queryParams);
    }

    $ch = curl_init($url_backend);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);

        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $body_arr = json_decode($raw_json_body, true) ?: [];
            $body_arr['id_usuario'] = $id_usuario_sesion;
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body_arr));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            $post_data = $_POST;
            $post_data['id_usuario'] = $id_usuario_sesion;
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

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Si conecta y devuelve algo útil, detener fallback.
    if ($response !== false && $http_code !== 404) {
        break;
    }
}

if ($response === false || $http_code === 0) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => "Error de conexión con el backend: $error",
        'backend_intentado' => $url_backend,
        'hosts_candidatos' => $backend_hosts
    ]);
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
            if (!empty($_POST['nombre'])) {
                $_SESSION['nombre'] = trim($_POST['nombre']);
            }
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

