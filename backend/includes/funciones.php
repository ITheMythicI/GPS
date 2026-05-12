<?php

function obtener_tabla(){
    try {
        require __DIR__ . '/database.php';
        
        // Query enriquecida: Trae la última lectura y el último resultado de IA para cada contenedor
        $consulta = "
            SELECT 
                c.*, 
                l.temperatura, l.humedad, l.peso, l.distancia, l.fecha_lectura,
                r.prioridad, r.confianza, r.fecha_analisis
            FROM Contenedores c
            LEFT JOIN (
                SELECT l1.* FROM LecturasSensores l1
                INNER JOIN (
                    SELECT id_contenedor, MAX(fecha_lectura) as max_f 
                    FROM LecturasSensores GROUP BY id_contenedor
                ) l2 ON l1.id_contenedor = l2.id_contenedor AND l1.fecha_lectura = l2.max_f
            ) l ON c.id_contenedor = l.id_contenedor
            LEFT JOIN (
                SELECT r1.* FROM ResultadosIA r1
                INNER JOIN (
                    SELECT id_contenedor, MAX(fecha_analisis) as max_a 
                    FROM ResultadosIA GROUP BY id_contenedor
                ) r2 ON r1.id_contenedor = r2.id_contenedor AND r1.fecha_analisis = r2.max_a
            ) r ON c.id_contenedor = r.id_contenedor
            ORDER BY c.id_contenedor ASC;
        ";
        
        return mysqli_query($db, $consulta);
    } catch (\Throwable $th) {
        error_log("Error en obtener_tabla: " . $th->getMessage());
        return false;
    }
}

?>
