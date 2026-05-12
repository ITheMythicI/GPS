<?php
require_once 'config.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$url = BACKEND_URL . "/obtenerContenedores.php";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);

$datos_contenedores = [];

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && $data['status'] === 'ok') {
        $datos_contenedores = $data['data'] ?? [];
    }
}

// Gráfica de barras: estado convertido a porcentaje por contenedor
$labels_barras = [];
$data_barras   = [];
$estado_map    = ['lleno' => 100, 'medio' => 50, 'vacío' => 0, 'vacio' => 0];

foreach ($datos_contenedores as $c) {
    $labels_barras[] = $c['ubicacion'];
    $data_barras[]   = $estado_map[strtolower($c['estado'] ?? '')] ?? 0;
}

// Gráfica de dona: contenedores agrupados por capacidad
$dona_map = [];
foreach ($datos_contenedores as $c) {
    $cap = $c['capacidad'] ?? 'Sin datos';
    $dona_map[$cap] = ($dona_map[$cap] ?? 0) + 1;
}
$labels_dona = array_keys($dona_map);
$data_dona   = array_values($dona_map);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nexus Solutions - BIN</title>

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body>

    <?php include_once 'includes/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include_once 'includes/sidebar.php'; ?>

    <!-- CONTENIDO -->
    <main id="content">

        <section class="stats-grid">

            <div class="card">
                <div>
                    <div class="card-label" style="color:black; text-align:center; white-space:nowrap; 
                    font-size:20px; margin-bottom:7px;">Condiciones para inferir el tipo de residuo</div>
                    <table border="0">
                        <thead>
                            <tr>
                            <th>CONDICIÓN</th>
                            <th style="text-align:center;"">TIPO ASIGNADO</th>
                            </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td style="font-size:10px;">HUMEDAD > 45% Y DENSIDAD > 300 KG/M³ (POR LITRO) 0.3X28 = 8.4 KG</td>
                            <td style="white-space: nowrap; font-size:13px; text-align:center;">Orgánico</td>
                        </tr>
                        <tr>
                            <td style="font-size:10px;">HUMEDAD < 30% Y DENSIDAD < 80 KG/M³ 0.08X28 = 2.24 KG</td>
                            <td style="white-space: nowrap; font-size:13px; text-align:center;">Plástico</td>
                        </tr>
                        <tr>
                            <td style="font-size:10px;">HUMEDAD < 30% Y DENSIDAD < 180 KG/M³ 0.180X28 = 5.04KG</td>
                            <td style="white-space: nowrap; font-size:13px; text-align:center;">Papel/Cartón</td>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap; font-size:10px;">DENSIDAD > 250 KG/M³ Y HUMEDAD < 35% 0.25X28 = 7 KG</td>
                            <td style="white-space: nowrap; font-size:13px; text-align:center;">Vidrio/Metal</td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>


            <div class="card">

                <div>

                    <div class="card-label">Alerta de contenedores</div>
                    <div class="card-value">0</div>

                    <div class="card-note">
                        Ninguna anomalía detectada
                    </div>

                </div>

                <div class="card-icon" style="background:#fff5b1;color:#b08800;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

            </div>


            <div class="card">

                <div>

                    <div class="card-label">Camiones activos</div>
                    <div class="card-value">1</div>

                    <div class="card-note">
                        En ruta
                    </div>

                </div>

                <div class="card-icon" style="background:#ddf4ff;color:#0969da;">
                    <i class="fa-solid fa-car"></i>
                </div>

            </div>

        </section>


        <section class="analytics-row">

            <div class="panel-box">
                <div class="panel-header">
                    <h3>PORCENTAJE DE LLENADO</h3>
                    <div>
                        <canvas id="tabla_barras" height="300px" width="450px"></canvas>
                    </div>
                </div>
            </div>

            <div class="panel-box">
                <div class="panel-header">
                    <h3>ESTADOS DE LOS CONTENEDORES</h3>
                    <div>
                        <canvas id="tabla_dona"></canvas>
                    </div>
                </div>
            </div>

        </section>


        <section class="table-box">

            <table>
                <thead>
                    <tr>
                        <th>Ubicación</th>
                        <th>Temperatura</th>
                        <th>Humedad</th>
                        <th>Estado / Llenado</th>
                        <th>Prioridad IA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos_contenedores as $contenedor): ?>
                        <tr>
                            <td>
                                <b><?= htmlspecialchars($contenedor['ubicacion'] ?? '') ?></b><br>
                                <span style="font-size: 10px; color: #888;">ID: <?= $contenedor['id_contenedor'] ?></span>
                            </td>
                            <td><?= htmlspecialchars($contenedor['temperatura'] ?? '0') ?>°C</td>
                            <td><?= htmlspecialchars($contenedor['humedad'] ?? '0') ?>%</td>
                            <td>
                                <span class="status <?= 'st-' . strtolower($contenedor['estado'] ?? '') ?>">
                                    <?= htmlspecialchars($contenedor['estado'] ?? 'Sin estado') ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $prio = strtolower($contenedor['prioridad'] ?? 'normal');
                                    $prio_label = strtoupper($prio);
                                    $prio_class = "st-normal"; // Default
                                    if($prio === 'alta') $prio_class = "st-lleno"; 
                                    if($prio === 'media') $prio_class = "st-medio";
                                ?>
                                <span class="status <?= $prio_class ?>">
                                    <?= $prio_label ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </section>


        <!-- ── Sección Reporte IA (Gemini) ── -->
        <section class="panel-box" id="seccion-reporte-ia" style="margin-top: 24px;">
            <div class="panel-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <h3 style="margin:0;">REPORTE INTELIGENTE (IA)</h3>
                <button
                    id="btn-generar-reporte"
                    onclick="generarReporteIA()"
                    style="
                        background: linear-gradient(135deg, #6c3fc5, #3b82f6);
                        color: #fff; border: none; border-radius: 8px;
                        padding: 9px 18px; font-family: 'Poppins', sans-serif;
                        font-size: 13px; font-weight: 600; cursor: pointer;
                        box-shadow: 0 4px 14px rgba(108,63,197,0.35);
                        transition: opacity 0.2s;
                    ">
                    <i class="fa-solid fa-robot"></i>&nbsp; Generar Reporte IA
                </button>
            </div>

            <!-- Contenido del reporte -->
            <div id="reporte-ia-contenido" style="
                margin-top: 16px;
                padding: 16px 20px;
                background: #f9fafb;
                border-radius: 8px;
                border-left: 4px solid #6c3fc5;
                font-family: 'Poppins', sans-serif;
                font-size: 13.5px;
                line-height: 1.7;
                color: #2c3e50;
                white-space: pre-wrap;
                display: none;
            ">
            </div>

            <!-- Resumen numérico -->
            <div id="reporte-ia-resumen" style="display:none; margin-top:12px;">
                <span style="font-size:12px; color:#888;">
                    Generado el: <span id="reporte-fecha"></span>
                    &nbsp;|
                    Alta: <span id="r-alta" style="color:#e74c3c; font-weight:700;"></span>
                    &nbsp;·
                    Media: <span id="r-media" style="color:#f39c12; font-weight:700;"></span>
                    &nbsp;·
                    Baja: <span id="r-baja" style="color:#27ae60; font-weight:700;"></span>
                </span>
            </div>
        </section>

    </main>

    <!-- SCRIPTS -->
    <script>
        const labelsBarras = <?= json_encode($labels_barras) ?>;
        const dataBarras   = <?= json_encode($data_barras) ?>;
        const labelsDona   = <?= json_encode($labels_dona) ?>;
        const dataDona     = <?= json_encode($data_dona) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="js/api.js"></script>
    <script src="js/graficas.js"></script>

    <!-- ── Lógica Reporte IA ── -->
    <script>
    async function generarReporteIA() {
        const btn = document.getElementById('btn-generar-reporte');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Generando...';

        const contenido = document.getElementById('reporte-ia-contenido');
        const resumen   = document.getElementById('reporte-ia-resumen');
        contenido.style.display = 'none';
        resumen.style.display   = 'none';

        try {
            const data = await API.generarReporte();
            if (data.status !== 'ok') throw new Error(data.message || 'Error generando reporte');

            contenido.textContent = data.reporte;
            contenido.style.display = 'block';

            if (data.resumen) {
                document.getElementById('r-alta').textContent  = data.resumen.alta;
                document.getElementById('r-media').textContent = data.resumen.media;
                document.getElementById('r-baja').textContent  = data.resumen.baja;
                document.getElementById('reporte-fecha').textContent = data.fecha_reporte;
                resumen.style.display = 'block';
            }
        } catch (err) {
            contenido.textContent = '⚠️ Error al generar reporte: ' + err.message;
            contenido.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-robot"></i>&nbsp; Generar Reporte IA';
        }
    }
    </script>

    <!-- ── Lógica de Actualización en Vivo (Dashboard Vivo) ── -->
    <script>
    async function actualizarDashboard() {
        try {
            const data = await API.obtenerContenedores();
            if (data.status !== 'ok') return;

            const contenedores = data.data;
            
            // 1. Actualizar Tabla
            const tbody = document.querySelector('table tbody');
            let html = '';
            
            // Variables para gráficas
            let labelsB = [];
            let dataB   = [];
            let donaMap = { 'alta': 0, 'media': 0, 'normal': 0 };

            contenedores.forEach(c => {
                const prio = (c.prioridad || 'normal').toLowerCase();
                const prioLabel = prio.toUpperCase();
                let prioClass = 'st-normal';
                if(prio === 'alta') { prioClass = 'st-lleno'; donaMap['alta']++; }
                else if(prio === 'media') { prioClass = 'st-medio'; donaMap['media']++; }
                else { donaMap['normal']++; }

                labelsB.push(c.ubicacion);
                // Asumimos distancia como proxy de llenado (ejemplo inverso)
                const dist = parseInt(c.distancia) || 0;
                const percent = Math.min(100, Math.max(0, 100 - (dist * 2))); 
                dataB.push(percent);

                html += `
                    <tr>
                        <td><b>${c.ubicacion}</b><br><span style="font-size: 10px; color: #888;">ID: ${c.id_contenedor}</span></td>
                        <td>${c.temperatura || '0'}°C</td>
                        <td>${c.humedad || '0'}%</td>
                        <td><span class="status st-${(c.estado || '').toLowerCase()}">${c.estado || 'Sin datos'}</span></td>
                        <td><span class="status ${prioClass}">${prioLabel}</span></td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;

            // 2. Actualizar Gráficas
            Charts.update(labelsB, dataB, ['ALTA', 'MEDIA', 'NORMAL'], [donaMap['alta'], donaMap['media'], donaMap['normal']]);

            // 3. Actualizar tarjetas superiores (ejemplo con Alertas)
            const alertCard = document.querySelector('.card-value');
            if(alertCard) alertCard.textContent = donaMap['alta'];

            const dot = document.querySelector('.notification-dot');
            if(dot) dot.style.display = donaMap['alta'] > 0 ? 'block' : 'none';

        } catch (e) { console.error('Error actualizando dashboard:', e); }
    }

    // Polling cada 30 segundos
    setInterval(actualizarDashboard, 30000);
    actualizarDashboard(); // Carga inicial en vivo
    </script>
</body>

</html>
