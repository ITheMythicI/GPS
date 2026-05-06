<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Ruta corregida: backend/public/ai/ → backend/includes/
require __DIR__ . '/../../includes/database.php';

$prioridad_input = $_GET['prioridad'] ?? 'alta';
$prioridad_minima = in_array($prioridad_input, ['alta', 'media', 'baja']) ? $prioridad_input : 'alta';

$prioridades_incluidas = match($prioridad_minima) {
    'alta'  => "'alta'",
    'media' => "'alta','media'",
    'baja'  => "'alta','media','baja'",
};

$sql = "
    SELECT DISTINCT
        c.id_contenedor, c.ubicacion, c.latitud, c.longitud,
        r.prioridad, r.score, r.volumen_pct
    FROM ResultadosIA r
    INNER JOIN Contenedores c ON c.id_contenedor = r.id_contenedor
    WHERE r.prioridad IN ($prioridades_incluidas)
      AND r.fecha_clasificacion = (
          SELECT MAX(r2.fecha_clasificacion) FROM ResultadosIA r2
          WHERE r2.id_contenedor = r.id_contenedor
      )
      AND c.latitud IS NOT NULL AND c.longitud IS NOT NULL
      AND c.latitud != 0 AND c.longitud != 0
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
        'status' => 'ok', 'ruta_ordenada' => [], 'distancia_km' => 0, 'coordenadas' => [],
        'message' => "No hay contenedores con prioridad '$prioridad_minima'. Ejecuta /ai/clasificar.php primero."
    ]);
    exit;
}

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
    echo json_encode(['status' => 'error', 'message' => 'Error microservicio Python', 'detalle' => $curl_error ?: "HTTP $py_status"]);
    exit;
}

$py_data = json_decode($py_response, true);
if (!$py_data || $py_data['status'] !== 'ok') {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Respuesta inválida del microservicio']);
    exit;
}

echo json_encode([
    'status'        => 'ok',
    'ruta_ordenada' => $py_data['ruta_ordenada'],
    'distancia_km'  => $py_data['distancia_km'],
    'coordenadas'   => $py_data['coordenadas'],
    'total_paradas' => count($py_data['ruta_ordenada']),
]);
