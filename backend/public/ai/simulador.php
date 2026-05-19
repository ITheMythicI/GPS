<?php
/**
 * simulador.php - Motor de simulación inteligente para el sistema GPS.
 * Genera datos realistas para contenedores no físicos.
 */
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../../includes/database.php';


$errores = [];

try {
    // 1. Obtener contenedores simulados
    $query = "SELECT c.*, z.nombre as zona_nombre FROM Contenedores c 
              LEFT JOIN Zonas z ON c.id_zona = z.id_zona 
              WHERE c.es_real = 0";
    $resultado = mysqli_query($db, $query);
    if (!$resultado) {
        throw new Exception("Error al consultar contenedores: " . mysqli_error($db));
    }

    $ahora = date('H'); // Hora actual (0-23)
    $dia = date('N');   // Día de la semana (1-7)

    while ($c = mysqli_fetch_assoc($resultado)) {
        try {
            $id_contenedor = $c['id_contenedor'];
            $zona = $c['zona_nombre'];

            // --- Lógica de Simulación de Llenado (distanciaBoteTapa) ---
            // 0 cm = Lleno, 60 cm = Vacío
            // Obtenemos la última lectura para que el crecimiento sea incremental
            $query_last = "SELECT distanciaBoteTapa FROM LecturasSensores WHERE id_sensor = $id_contenedor ORDER BY fecha_hora DESC LIMIT 1";
            $res_last = mysqli_query($db, $query_last);
            
            if ($row = mysqli_fetch_assoc($res_last)) {
                $last_dist = $row['distanciaBoteTapa'];
                $tasa_llenado = 1.2; // Crecimiento más rápido para la demo
            } else {
                // PRIMERA VEZ: Iniciar con un valor aleatorio entre 10 y 55 cm
                $last_dist = rand(10, 55);
                $tasa_llenado = 0;
            }

            // Tasa de llenado base (cm que baja la tapa por lectura)
            $tasa_llenado = 0.5; 

            // Ajustar por zona y hora
            if ($zona === 'Zona Académica') {
                if ($ahora >= 7 && $ahora <= 14) $tasa_llenado *= 3; // Mucha actividad escolar
            } elseif ($zona === 'Zona Recreativa') {
                if ($ahora >= 13 && $ahora <= 16) $tasa_llenado *= 4; // Hora de comida
                if ($ahora >= 19 && $ahora <= 22) $tasa_llenado *= 3; // Tarde/noche
            } elseif ($zona === 'Zona Administrativa') {
                if ($ahora >= 9 && $ahora <= 18) $tasa_llenado *= 2; // Horario oficina
            }

            // Si ya está lleno (distancia < 5), simular recolección aleatoria o quedarse lleno
            if ($last_dist < 5) {
                if (rand(1, 10) > 8) { // 20% de probabilidad de ser vaciado por el camión simulado
                    $nueva_dist = 60.0;
                } else {
                    $nueva_dist = $last_dist;
                }
            } else {
                $nueva_dist = max(0, $last_dist - (rand(50, 150) / 100 * $tasa_llenado));
            }

            // --- Otros Sensores ---
            $temp = 22 + (sin(($ahora - 6) * M_PI / 12) * 8) + (rand(-10, 10) / 10); // Ciclo térmico día/noche
            $hum = 40 + (rand(-5, 5));
            $peso = (60 - $nueva_dist) * 0.8 + (rand(0, 50) / 10); // Peso proporcional al llenado

            // Asegurar que exista el sensor correspondiente para evitar fallos de Foreign Key
            mysqli_query($db, "INSERT IGNORE INTO Sensores (id_sensor, tipo_sensor, id_contenedor) VALUES ($id_contenedor, 'Simulado', $id_contenedor)");

            // 2. Insertar lectura
            $sql_insert = "INSERT INTO LecturasSensores (id_sensor, fecha_hora, tempCelsius, humedad, distanciaBoteTapa, pesoKg) 
                           VALUES (?, NOW(), ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $sql_insert);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'idddd', $id_contenedor, $temp, $hum, $nueva_dist, $peso);
                if (!mysqli_stmt_execute($stmt)) {
                    $err = mysqli_stmt_error($stmt);
                    error_log("Error simulando contenedor $id_contenedor: " . $err);
                    $errores[] = "Contenedor $id_contenedor: $err";
                }
                mysqli_stmt_close($stmt);
            } else {
                $err = mysqli_error($db);
                error_log("Error preparando simulacion para $id_contenedor: " . $err);
                $errores[] = "Error prepare $id_contenedor: $err";
            }

            // 3. Actualizar estado del contenedor (para consistencia legacy)
            $estado = 'Vacío';
            if ($nueva_dist < 15) $estado = 'Lleno';
            elseif ($nueva_dist < 40) $estado = 'Medio';

            mysqli_query($db, "UPDATE Contenedores SET estado = '$estado' WHERE id_contenedor = $id_contenedor");
            
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            error_log("Excepcion simulando contenedor " . ($id_contenedor ?? 'Desconocido') . ": " . $msg);
            $errores[] = "Error PHP en " . ($id_contenedor ?? '?') . ": " . $msg;
        }
    }
} catch (Throwable $e) {
    $errores[] = "Error general del script: " . $e->getMessage();
}

if (!empty($errores)) {
    echo json_encode(["status" => "warning", "message" => "Simulación terminó con errores.", "errores" => $errores]);
} else {
    echo json_encode(["status" => "ok", "message" => "Simulación completada para botes secundarios."]);
}
?>
