<?php
/**
 * backend/ai/clasificar.php
 * ──────────────────────────
 * Orquestador PHP para la clasificación de contenedores.
 *
 * Flujo:
 *   1. Consulta MySQL: última lectura de cada contenedor
 *   2. Calcula volumen_pct a partir de distanciaBoteTapa
 *   3. Llama al microservicio Python (localhost:5000/clasificar)
 *   4. Persiste resultados en ResultadosIA
 *   5. Devuelve JSON con clasificaciones
 *
 * Llamado por: frontend/api/ia_proxy.php (VM Frontend)
 * Método:      GET  (sin body; lee de MySQL directamente)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Restringir en producción a IP del frontend

require __DIR__ . '/../includes/database.php';

// ── 1. Obtener última lectura por contenedor ──────────────────────────────────
$sql = "
    SELECT
        c.id_contenedor,
        c.ubicacion,
        c.latitud,
        c.longitud,
        c.capacidad,
        ls.tempCelsius       AS temperatura,
        ls.humedad,
        ls.distanciaBoteTapa AS distancia_cm,
        ls.pesoKg            AS peso_kg
    FROM Contenedores c
    LEFT JOIN LecturasSensores ls ON ls.id_sensor = c.id_contenedor
        AND ls.fecha_hora = (
            SELECT MAX(ls2.fecha_hora)
            FROM LecturasSensores ls2
            WHERE ls2.id_sensor = c.id_contenedor
        )
";

$resultado = mysqli_query($db, $sql);

if (!$resultado) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al consultar MySQL: ' . mysqli_error($db)]);
    exit;
}

$contenedores = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    // ── 2. Calcular porcentaje de llenado ─────────────────────────────────────
    // distanciaBoteTapa: distancia en cm desde el sensor (tapa) hasta la basura.
    // A menor distancia → más lleno. Se asume tapa a 60 cm del fondo como referencia.
    $altura_contenedor_cm = 60.0; // ajustar si la altura real es diferente
    $dist = floatval($fila['distancia_cm'] ?? $altura_contenedor_cm);
    $volumen_pct = max(0, min(100, (($altura_contenedor_cm - $dist) / $altura_contenedor_cm) * 100));

    $contenedores[] = [
        'id_contenedor' => intval($fila['id_contenedor']),
        'ubicacion'     => $fila['ubicacion']    ?? '',
        'latitud'       => floatval($fila['latitud']    ?? 0),
        'longitud'      => floatval($fila['longitud']   ?? 0),
        'capacidad'     => $fila['capacidad']    ?? '',
        'volumen_pct'   => round($volumen_pct, 2),
        'humedad'       => floatval($fila['humedad']    ?? 0),
        'temperatura'   => floatval($fila['temperatura'] ?? 0),
        'peso_kg'       => floatval($fila['peso_kg']    ?? 0),
    ];
}

if (empty($contenedores)) {
    echo json_encode(['status' => 'ok', 'resultados' => [], 'message' => 'Sin contenedores registrados']);
    exit;
}

// ── 3. Llamar al microservicio Python ─────────────────────────────────────────
$payload = json_encode(['contenedores' => $contenedores]);

$ch = curl_init('http://127.0.0.1:5000/clasificar');
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

$resultados = $py_data['resultados'];

// ── 4. Persistir en ResultadosIA ─────────────────────────────────────────────
$stmt = mysqli_prepare($db, "
    INSERT INTO ResultadosIA
        (id_contenedor, prioridad, score, volumen_pct, temperatura, humedad, peso_kg)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

foreach ($resultados as $r) {
    $id        = intval($r['id_contenedor']);
    $prioridad = $r['prioridad'];
    $score     = floatval($r['score']);
    $vol       = floatval($r['volumen_pct']);
    $temp      = floatval($r['temperatura']);
    $hum       = floatval($r['humedad']);
    $peso      = floatval($r['peso_kg']);

    mysqli_stmt_bind_param($stmt, 'isdddd', $id, $prioridad, $score, $vol, $temp, $hum, $peso);
    mysqli_stmt_execute($stmt);
}
mysqli_stmt_close($stmt);

// ── 5. Responder al frontend ───────────────────────────────────────────────────
echo json_encode([
    'status'     => 'ok',
    'resultados' => $resultados,
    'total'      => count($resultados),
]);
