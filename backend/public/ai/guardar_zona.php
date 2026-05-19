<?php
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Desactivar reporte de errores en pantalla para no corromper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $prioridad = (int)($_POST['prioridad'] ?? 1);
    $color = $_POST['color'] ?? '#3b82f6';
    $coords = $_POST['coords'] ?? '[]';

    if (!$nombre) {
        throw new Exception('El nombre de la zona es obligatorio');
    }

    // Normalizar coordenadas si vienen en formato plano y asegurar JSON válido
    if (!empty($coords)) {
        $decoded = json_decode($coords, true);
        if ($decoded === null) {
            // Intentar convertir formato plano "lat,lng,lat,lng" a array JSON
            $parts = explode(',', $coords);
            if (count($parts) >= 6 && count($parts) % 2 === 0) {
                $newCoords = [];
                for ($i = 0; $i < count($parts); $i += 2) {
                    $lat = floatval(trim($parts[$i]));
                    $lng = floatval(trim($parts[$i+1]));
                    if ($lat !== 0.0 && $lng !== 0.0) {
                        $newCoords[] = [$lat, $lng];
                    }
                }
                if (count($newCoords) >= 3) {
                    $coords = json_encode($newCoords);
                } else {
                    throw new Exception('Formato de coordenadas no válido. Debe ser un JSON válido como [[lat,lng],[lat,lng]...] o una secuencia de al menos 3 puntos separados por comas.');
                }
            } else {
                throw new Exception('Formato de coordenadas no válido. Asegúrate de usar corchetes, ej: [[lat, lng], [lat, lng]...]');
            }
        }
    } else {
        $coords = '[]';
    }

    if (!empty($id)) {
        // UPDATE
        $query = "UPDATE Zonas SET nombre = ?, prioridad_limpieza = ?, color_hex = ?, coordenadas_poligono = ? WHERE id_zona = ?";
        $stmt = mysqli_prepare($db, $query);
        if (!$stmt) throw new Exception("Error preparando la consulta de update: " . mysqli_error($db));
        mysqli_stmt_bind_param($stmt, 'sissi', $nombre, $prioridad, $color, $coords, $id);
        $msg = 'Zona actualizada correctamente';
    } else {
        // INSERT
        $query = "INSERT INTO Zonas (nombre, prioridad_limpieza, color_hex, coordenadas_poligono) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        if (!$stmt) throw new Exception("Error preparando la consulta de insert: " . mysqli_error($db));
        mysqli_stmt_bind_param($stmt, 'siss', $nombre, $prioridad, $color, $coords);
        $msg = 'Zona creada correctamente';
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'ok', 'message' => $msg]);
    } else {
        throw new Exception("Error al ejecutar: " . mysqli_stmt_error($stmt));
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>
