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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include_once 'includes/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include_once 'includes/sidebar.php'; ?>

    <!-- CONTENIDO -->
    <main id="content">

        <!-- KPI CARDS -->
        <section class="stats-grid" style="grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 40px;">
            <div class="card">
                <div>
                    <div class="card-label">TOTAL CONTENEDORES</div>
                    <div class="card-value"><?= count($datos_contenedores) ?></div>
                    <div class="card-note">Red activa BIN</div>
                </div>
                <div class="card-icon" style="background:var(--accent-soft); color:var(--primary);"><i class="fa-solid fa-box"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">ALERTAS CRÍTICAS</div>
                    <div class="card-value" id="kpi-alertas">0</div>
                    <div class="card-note">Prioridad Alta detectada</div>
                </div>
                <div class="card-icon" style="background:#ffebe9; color:#cf222e;"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">HUMEDAD PROMEDIO</div>
                    <div class="card-value" id="kpi-humedad">--%</div>
                    <div class="card-note">Promedio de red BIN</div>
                </div>
                <div class="card-icon" style="background:#e6f7ff; color:#1890ff;"><i class="fa-solid fa-droplet"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">TEMP. PROMEDIO</div>
                    <div class="card-value" id="kpi-temp">--°C</div>
                    <div class="card-note">Estado térmico global</div>
                </div>
                <div class="card-icon" style="background:#fff7e6; color:#fa8c16;"><i class="fa-solid fa-temperature-half"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">CAPACIDAD TOTAL</div>
                    <div class="card-value">
                        <?php 
                            $caps = array_column($datos_contenedores, 'capacidad');
                            echo array_sum($caps);
                        ?> L
                    </div>
                    <div class="card-note">Volumen total instalado</div>
                </div>
                <div class="card-icon" style="background:#f6f8fa; color:#57606a;"><i class="fa-solid fa-weight-hanging"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">CONFIANZA IA</div>
                    <div class="card-value" id="kpi-confianza">--%</div>
                    <div class="card-note">Precisión de clasificación</div>
                </div>
                <div class="card-icon" style="background:#f5f0ff; color:#6c3fc5;"><i class="fa-solid fa-robot"></i></div>
            </div>
        </section>

        <!-- ANALYTICS (Tamaño Optimizado) -->
        <section class="analytics-row" style="margin-bottom: 30px;">
            <div class="panel-box" style="padding: 15px;">
                <div class="panel-header" style="margin-bottom: 10px;">
                    <h3 style="font-size: 14px;">PORCENTAJE DE LLENADO</h3>
                </div>
                <div style="height: 220px; position: relative;">
                    <canvas id="tabla_barras"></canvas>
                </div>
            </div>

            <div class="panel-box" style="padding: 15px;">
                <div class="panel-header" style="margin-bottom: 10px;">
                    <h3 style="font-size: 14px;">ESTADOS DE LOS CONTENEDORES</h3>
                </div>
                <div style="height: 220px; position: relative; display: flex; justify-content: center;">
                    <canvas id="tabla_dona"></canvas>
                </div>
            </div>
        </section>

        <!-- CRITERIOS DE CLASIFICACIÓN (Rescatada y Mejorada) -->
        <section class="panel-box" style="margin-bottom: 24px;">
            <div class="panel-header">
                <h3 style="margin:0;"><i class="fa-solid fa-circle-info" style="color:var(--primary);"></i> CRITERIOS PARA INFERIR TIPO DE RESIDUO</h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid var(--border);">
                            <th style="padding: 12px; text-align: left;">CONDICIÓN TÉCNICA (SENSORES)</th>
                            <th style="padding: 12px; text-align: center;">TIPO ASIGNADO</th>
                            <th style="padding: 12px; text-align: left;">REGLA DE INFERENCIA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">Humedad > 45% & Densidad > 300 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status" style="background:#e8f5e9; color:#2e7d32;">ORGÁNICO</span></td>
                            <td style="padding: 10px; color: #666;">Residuos con alto contenido de agua y peso alto.</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">Humedad < 30% & Densidad < 80 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status" style="background:#e3f2fd; color:#1565c0;">PLÁSTICO</span></td>
                            <td style="padding: 10px; color: #666;">Materiales ligeros y secos (PET, PEAD).</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">Humedad < 30% & Densidad < 180 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status" style="background:#fff3e0; color:#ef6c00;">PAPEL/CARTÓN</span></td>
                            <td style="padding: 10px; color: #666;">Celulosas secas de densidad media.</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;">Humedad < 35% & Densidad > 250 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status" style="background:#f3e5f5; color:#7b1fa2;">VIDRIO/METAL</span></td>
                            <td style="padding: 10px; color: #666;">Inorgánicos pesados sin presencia de líquidos.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- LISTADO -->
        <section class="table-box" id="tabla-contenedores">
            <table>
                <thead>
                    <tr>
                        <th>Ubicación / Zona</th>
                        <th>Temperatura</th>
                        <th>Humedad</th>
                        <th>Estado / Llenado</th>
                        <th>Prioridad IA</th>
                    </tr>

                </thead>
                <tbody id="tbody-contenedores">
                    <?php foreach ($datos_contenedores as $contenedor): ?>
                        <tr>
                            <td>
                                <b><?= htmlspecialchars($contenedor['ubicacion'] ?? '') ?></b>
                                <?php if($contenedor['es_real']): ?>
                                    <span style="font-size: 9px; background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-left: 5px;">FISICO</span>
                                <?php endif; ?>
                                <br>
                                <span style="font-size: 11px; color: var(--primary); font-weight: 500;"><?= htmlspecialchars($contenedor['zona_nombre'] ?? 'Sin zona') ?></span>
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
                                    $prio_class = "st-normal";
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

        <!-- REPORTE IA -->
        <section class="panel-box" id="seccion-reporte-ia" style="margin-top: 24px;">
            <div class="panel-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <h3 style="margin:0;">REPORTE INTELIGENTE (IA)</h3>
                <div style="display:flex; gap:10px;">
                    <button id="btn-simular" onclick="triggerSimulacion()" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; padding: 9px 18px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        <i class="fa-solid fa-play"></i>&nbsp; Simular Actividad
                    </button>
                    <button id="btn-generar-reporte" onclick="generarReporteIA()" style="background: linear-gradient(135deg, #6c3fc5, #3b82f6); color: #fff; border: none; border-radius: 8px; padding: 9px 18px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 14px rgba(108,63,197,0.35); transition: opacity 0.2s;">
                        <i class="fa-solid fa-robot"></i>&nbsp; Generar Reporte IA
                    </button>
                </div>
            </div>

            <div id="reporte-ia-contenido" style="margin-top: 16px; padding: 16px 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #6c3fc5; font-family: 'Poppins', sans-serif; font-size: 13.5px; line-height: 1.7; color: #2c3e50; white-space: pre-wrap; display: none;"></div>
            <div id="reporte-ia-resumen" style="display:none; margin-top:12px;">
                <span style="font-size:12px; color:#888;">
                    Generado el: <span id="reporte-fecha"></span> |
                    Alta: <span id="r-alta" style="color:#e74c3c; font-weight:700;"></span> ·
                    Media: <span id="r-media" style="color:#f39c12; font-weight:700;"></span> ·
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

    <script>
    async function triggerSimulacion() {
        const btn = document.getElementById('btn-simular');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Simulando...';
        try {
            await API.simular();
            await API.clasificar(); // Actualizar prioridades tras simular
            await actualizarDashboard();
        } catch (err) {
            console.error(err);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play"></i>&nbsp; Simular Actividad';
        }
    }

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
            if (data.status !== 'ok') throw new Error(data.message || 'Error');
            contenido.textContent = data.reporte;
            contenido.style.display = 'block';
            if (data.resumen) {
                document.getElementById('r-alta').textContent = data.resumen.alta;
                document.getElementById('r-media').textContent = data.resumen.media;
                document.getElementById('r-baja').textContent = data.resumen.baja;
                document.getElementById('reporte-fecha').textContent = data.fecha_reporte;
                resumen.style.display = 'block';
            }
        } catch (err) {
            contenido.textContent = '⚠️ Error: ' + err.message;
            contenido.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-robot"></i>&nbsp; Generar Reporte IA';
        }
    }

    async function actualizarDashboard() {
        try {
            const data = await API.obtenerContenedores();
            if (data.status !== 'ok') return;
            const contenedores = data.data;
            const tbody = document.getElementById('tbody-contenedores');
            let html = '';
            let labelsB = [], dataB = [], donaMap = { 'alta': 0, 'media': 0, 'normal': 0 };
            let totalHum = 0, totalTemp = 0, totalConf = 0;

            contenedores.forEach(c => {
                const prio = (c.prioridad || 'normal').toLowerCase();
                let prioClass = 'st-normal';
                if(prio === 'alta') { prioClass = 'st-lleno'; donaMap['alta']++; }
                else if(prio === 'media') { prioClass = 'st-medio'; donaMap['media']++; }
                else { donaMap['normal']++; }

                labelsB.push(c.ubicacion);
                const dist = parseInt(c.distancia) || 0;
                // Cálculo de porcentaje basado en 60cm de altura total
                const fillingPct = Math.min(100, Math.max(0, ((60 - dist) / 60) * 100));
                dataB.push(fillingPct.toFixed(1));
                
                totalHum += parseFloat(c.humedad || 0);
                totalTemp += parseFloat(c.temperatura || 0);
                totalConf += parseFloat(c.confianza || 0);

                const badgeFisico = c.es_real == 1 ? '<span style="font-size: 9px; background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-left: 5px;">FISICO</span>' : '';

                // Determinar estado dinámico basado en distancia
                let statusText = 'Vacío', statusClass = 'st-vacio';
                if (dist < 15) { statusText = 'Lleno'; statusClass = 'st-lleno'; }
                else if (dist < 40) { statusText = 'Medio'; statusClass = 'st-medio'; }

                html += `<tr>
                    <td>
                        <b>${c.ubicacion}</b> ${badgeFisico}<br>
                        <span style="font-size:11px; color:var(--primary); font-weight:500;">${c.zona_nombre || 'Sin zona'}</span>
                    </td>
                    <td>${c.temperatura || '0'}°C</td>
                    <td>${c.humedad || '0'}%</td>
                    <td>
                        <span class="status ${statusClass}">${statusText}</span>
                        <small style="display:block; font-size:10px; color:#666; margin-top:2px;">${fillingPct.toFixed(0)}% de llenado</small>
                    </td>
                    <td><span class="status ${prioClass}">${prio.toUpperCase()}</span></td>
                </tr>`;


            });

            tbody.innerHTML = html;
            Charts.update(labelsB, dataB, ['ALTA', 'MEDIA', 'NORMAL'], [donaMap['alta'], donaMap['media'], donaMap['normal']]);
            
            const count = contenedores.length || 1;
            document.getElementById('kpi-alertas').textContent = donaMap['alta'];
            document.getElementById('kpi-humedad').textContent = (totalHum/count).toFixed(1) + '%';
            document.getElementById('kpi-temp').textContent    = (totalTemp/count).toFixed(1) + '°C';
            document.getElementById('kpi-confianza').textContent = (totalConf/count).toFixed(1) + '%';
            
            const dot = document.querySelector('.notification-dot');
            if(dot) dot.style.display = donaMap['alta'] > 0 ? 'block' : 'none';
        } catch (e) { console.error(e); }
    }
    setInterval(actualizarDashboard, 30000);
    actualizarDashboard();
    </script>
</body>
</html>
