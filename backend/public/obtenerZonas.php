<?php
header('Content-Type: application/json');
require __DIR__ . '/../includes/database.php';

try {
    $query = "SELECT * FROM Zonas";
    $res = mysqli_query($db, $query);
    $zonas = [];
    while ($row = mysqli_fetch_assoc($res)) {
        if ($row['coordenadas_poligono']) {
            $row['coordenadas_poligono'] = json_decode($row['coordenadas_poligono'], true);
        }
        $zonas[] = $row;
    }
    echo json_encode(["status" => "ok", "data" => $zonas]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
