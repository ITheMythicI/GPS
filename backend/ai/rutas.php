<?php
/**
 * backend/ai/rutas.php
 * ─────────────────────
 * Orquestador PHP para el cálculo de rutas óptimas (Dijkstra).
 *
 * Flujo:
 *   1. Consulta ResultadosIA: contenedores con prioridad 'alta' (último registro)
 *   2. JOIN con Contenedores para obtener lat/lon
 *   3. Llama al microservicio Python (localhost:5000/rutas)
 *   4. Devuelve JSON con ruta ordenada y coordenadas para Leaflet
 *
 * Llamado por: frontend/api/ia_proxy.php (VM Frontend)
 * Método:      GET
 *
 * Opcional: enviar ?prioridad=media para incluir también media prioridad.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/../includes/database.php';

// Prioridad mínima a incluir en la ruta (por defecto solo 'alta')
$prioridad_minima = in_array($_GET['prioridad'] ?? 'alta', ['alta', 'media', 'baja'])
    ? $_GET['prioridad']
    : 'alta';

$prioridades_incluidas = match($prioridad_minima) {
    'alta'  => "'alta'",
    'media' => "'alta','media'",
    'baja'  => "'alta','media','baja'",
};

// ── 1 & 2. Obtener contenedores prioritarios con coordenadas ──────────────────
$sql = "
    SELECT DISTINCT
        c.id_contenedor,
        c.ubicacion,
        c.latitud,
        c.longitud,
        r.prioridad,
        r.score,
        r.volumen_pct
    FROM ResultadosIA r
    INNER JOIN Contenedores c ON c.id_contenedor = r.id_contenedor
    WHERE r.prioridad IN ($prioridades_incluidas)
      AND r.fecha_clasificacion = (
          SELECT MAX(r2.fecha_clasificacion)
          FROM ResultadosIA r2
          WHERE r2.id_contenedor = r.id_contenedor
      )
      AND c.latitud  IS NOT NULL
      AND c.longitud IS NOT NULL
      AND c.latitud  != 0
      AND c.longitud != 0
    ORDER BY r.score DESC
";

$resultado = mysqli_query($db, $sql);

if (!$resultado) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error MySQL: ' . mysqli_error($db)]);
    exit;
}

$contenedores = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $contenedores[] = [
        'id_contenedor' => intval($fila['id_contenedor']),
        'ubicacion'     => $fila['ubicacion'] ?? '',
        'latitud'       => floatval($fila['latitud']),
        'longitud'      => floatval($fila['longitud']),
        'prioridad'     => $fila['prioridad'],
        'score'         => floatval($fila['score']),
        'volumen_pct'   => floatval($fila['volumen_pct'] ?? 0),
    ];
}

if (empty($contenedores)) {
    echo json_encode([
        'status'        => 'ok',
        'ruta_ordenada' => [],
        'distancia_km'  => 0,
        'coordenadas'   => [],
        'message'       => "No hay contenedores con prioridad '$prioridad_minima' clasificados aún. Ejecuta /clasificar primero."
    ]);
    exit;
}

// ── 3. Llamar al microservicio Python ─────────────────────────────────────────
$payload = json_encode(['contenedores' => $contenedores]);

$ch = curl_init('http://127.0.0.1:5000/rutas');
curl_setopt($ch, CURLOPT_POST,           true);
curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT,        10);
curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);

$py_response = curl_exec($ch);
$py_status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error  = curl_error($ch);
curl_close($ch);

if ($py_response === false || $py_status !== 200) {
    http_response_code(502);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al contactar microservicio Python',
        'detalle' => $curl_error ?: "HTTP $py_status"
    ]);
    exit;
}

$py_data = json_decode($py_response, true);

if (!$py_data || $py_data['status'] !== 'ok') {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Respuesta inválida del microservicio']);
    exit;
}

// ── 4. Responder ──────────────────────────────────────────────────────────────
echo json_encode([
    'status'        => 'ok',
    'ruta_ordenada' => $py_data['ruta_ordenada'],
    'distancia_km'  => $py_data['distancia_km'],
    'coordenadas'   => $py_data['coordenadas'],
    'total_paradas' => count($py_data['ruta_ordenada']),
]);
