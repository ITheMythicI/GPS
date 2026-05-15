<?php
date_default_timezone_set('America/Mexico_City');


function obtener_tabla(){
    try {
        require __DIR__ . '/database.php';
        
        // Query enriquecida: Trae la última lectura y el último resultado de IA para cada contenedor
        // Incluyendo Zonas y filtrando por tipo (real/simulado)
        $consulta = "
            SELECT 
                c.*, 
                z.nombre as zona_nombre, z.color_hex as zona_color,
                l.temperatura, l.humedad, l.peso, l.distancia, l.fecha_lectura,
                r.prioridad, r.score as confianza, r.fecha_clasificacion as fecha_analisis
            FROM Contenedores c
            LEFT JOIN Zonas z ON c.id_zona = z.id_zona
            LEFT JOIN (
                SELECT l1.* FROM LecturasSensores l1
                INNER JOIN (
                    SELECT id_sensor, MAX(fecha_hora) as max_f 
                    FROM LecturasSensores GROUP BY id_sensor
                ) l2 ON l1.id_sensor = l2.id_sensor AND l1.fecha_hora = l2.max_f
            ) l ON c.id_contenedor = l.id_sensor
            LEFT JOIN (
                SELECT r1.* FROM ResultadosIA r1
                INNER JOIN (
                    SELECT id_contenedor, MAX(fecha_clasificacion) as max_a 
                    FROM ResultadosIA GROUP BY id_contenedor
                ) r2 ON r1.id_contenedor = r2.id_contenedor AND r1.fecha_clasificacion = r2.max_a
            ) r ON c.id_contenedor = r.id_contenedor
            ORDER BY c.es_real DESC, c.id_contenedor ASC;
        ";

        
        return mysqli_query($db, $consulta);
    } catch (\Throwable $th) {
        error_log("Error en obtener_tabla: " . $th->getMessage());
        return false;
    }
}

?>
