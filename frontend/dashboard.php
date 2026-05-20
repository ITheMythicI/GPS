<?php
require_once 'config.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}


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

<body class="<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
    <?php include_once 'includes/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include_once 'includes/sidebar.php'; ?>

    <!-- CONTENIDO -->
    <main id="content">

        <!-- KPI CARDS -->
        <section class="stats-grid">
            <div class="card">
                <div>
                    <div class="card-label">TOTAL CONTENEDORES</div>
                    <div class="card-value" id="kpi-total">0</div>
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
                <div class="card-icon" style="background:var(--bg-cancelado); color:var(--st-cancelado);"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">HUMEDAD PROMEDIO</div>
                    <div class="card-value" id="kpi-humedad">--%</div>
                    <div class="card-note">Promedio de red BIN</div>
                </div>
                <div class="card-icon" style="background:var(--bg-nuevo); color:var(--st-nuevo);"><i class="fa-solid fa-droplet"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">TEMP. PROMEDIO</div>
                    <div class="card-value" id="kpi-temp">--°C</div>
                    <div class="card-note">Estado térmico global</div>
                </div>
                <div class="card-icon" style="background:var(--bg-pendiente); color:var(--st-pendiente);"><i class="fa-solid fa-temperature-half"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">CAPACIDAD TOTAL</div>
                    <div class="card-value" id="kpi-capacidad">0 L</div>
                    <div class="card-note">Volumen total instalado</div>
                </div>
                <div class="card-icon" style="background:var(--bg-cotizado); color:var(--st-cotizado);"><i class="fa-solid fa-weight-hanging"></i></div>
            </div>

            <div class="card">
                <div>
                    <div class="card-label">CONFIANZA IA</div>
                    <div class="card-value" id="kpi-confianza">--%</div>
                    <div class="card-note">Precisión de clasificación</div>
                </div>
                <div class="card-icon" style="background:var(--bg-ia); color:var(--st-ia);"><i class="fa-solid fa-robot"></i></div>
            </div>
        </section>

        <!-- ANALYTICS (Tamaño Optimizado) -->
        <section class="analytics-row">
            <div class="panel-box chart-panel">
                <div class="panel-header">
                    <h3>PORCENTAJE DE LLENADO</h3>
                </div>
                <div class="chart-wrap">
                    <canvas id="tabla_barras"></canvas>
                </div>
            </div>

            <div class="panel-box chart-panel">
                <div class="panel-header">
                    <h3>ESTADOS DE LOS CONTENEDORES</h3>
                </div>
                <div class="chart-wrap chart-wrap--dona">
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
                        <tr style="background: var(--bg-header); border-bottom: 2px solid var(--border);">
                            <th style="padding: 12px; text-align: left; color: var(--text-sub);">CONDICIÓN TÉCNICA (SENSORES)</th>
                            <th style="padding: 12px; text-align: center; color: var(--text-sub);">TIPO ASIGNADO</th>
                            <th style="padding: 12px; text-align: left; color: var(--text-sub);">REGLA DE INFERENCIA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px; color: var(--text-main);">Humedad > 45% & Densidad > 300 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status st-terminado">ORGÁNICO</span></td>
                            <td style="padding: 10px; color: var(--text-sub);">Residuos con alto contenido de agua y peso alto.</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px; color: var(--text-main);">Humedad < 30% & Densidad < 80 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status st-nuevo">PLÁSTICO</span></td>
                            <td style="padding: 10px; color: var(--text-sub);">Materiales ligeros y secos (PET, PEAD).</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px; color: var(--text-main);">Humedad < 30% & Densidad < 180 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status st-pendiente">PAPEL/CARTÓN</span></td>
                            <td style="padding: 10px; color: var(--text-sub);">Celulosas secas de densidad media.</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; color: var(--text-main);">Humedad < 35% & Densidad > 250 kg/m³</td>
                            <td style="padding: 10px; text-align: center;"><span class="status st-cotizado">VIDRIO/METAL</span></td>
                            <td style="padding: 10px; color: var(--text-sub);">Inorgánicos pesados sin presencia de líquidos.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- LISTADO -->
        <section class="table-box scroll-anchor-section" id="tabla-contenedores">
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
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-sub);">Cargando contenedores...</td></tr>
                </tbody>
            </table>
        </section>

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
        <!-- REPORTE IA -->
        <section class="panel-box scroll-anchor-section" id="seccion-reporte-ia" style="margin-top: 24px;">
            <div class="panel-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <h3 style="margin:0;">REPORTE INTELIGENTE (IA)</h3>
            <div style="display:flex; gap:10px;">
                    <button id="btn-reiniciar" onclick="triggerReinicio()" style="background: #fff3cd; color: #856404; border: 1px solid #ffc107; border-radius: 8px; padding: 9px 18px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        <i class="fa-solid fa-rotate-left"></i>&nbsp; Reiniciar Datos
                    </button>
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
        <?php endif; ?>
    </main>

    <!-- SCRIPTS -->
    <script>
        // Data is now loaded completely via API in actualizarDashboard
        const labelsBarras = [];
        const dataBarras   = [];
        const labelsDona   = [];
        const dataDona     = [];
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="js/api.js"></script>
    <script src="js/graficas.js"></script>

    <script>
    let ultimosContenedores = [];

    async function triggerSimulacion() {
        const btn = document.getElementById('btn-simular');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Simulando...';
        try {
            const simRes = await API.simular();
            if (simRes.status === 'warning' && simRes.errores) {
                alert("Algunos contenedores fallaron al simular:\n" + simRes.errores.join('\n'));
            }
            await API.clasificar();
            await actualizarDashboard();
        } catch (err) {
            console.error(err);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play"></i>&nbsp; Simular Actividad';
        }
    }

    async function triggerReinicio() {
        if (!confirm('¿Reiniciar todos los contenedores simulados a estado vacío?')) return;
        const btn = document.getElementById('btn-reiniciar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Reiniciando...';
        try {
            const res = await API.fetch('reiniciar_simulacion');
            if (res.status === 'ok') {
                await actualizarDashboard();
                alert('✅ Contenedores simulados reiniciados.');
            } else {
                alert('Error: ' + (res.message || 'No se pudo reiniciar'));
            }
        } catch (err) {
            alert('Error de red: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-rotate-left"></i>&nbsp; Reiniciar Datos';
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
            const criterios = [
                'Humedad > 45% y Densidad > 300 kg/m3 => ORGÁNICO',
                'Humedad < 30% y Densidad < 80 kg/m3 => PLÁSTICO',
                'Humedad < 30% y Densidad < 180 kg/m3 => PAPEL/CARTÓN',
                'Humedad < 35% y Densidad > 250 kg/m3 => VIDRIO/METAL'
            ];
            const data = await API.generarReporte({
                clasificaciones: ultimosContenedores,
                criterios: criterios
            });
            if (data.status !== 'ok') {
                const detalle = data.detalle ? (' | Detalle: ' + (typeof data.detalle === 'string' ? data.detalle : JSON.stringify(data.detalle))) : '';
                throw new Error((data.message || 'Error') + detalle);
            }
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
            ultimosContenedores = contenedores;
            const tbody = document.getElementById('tbody-contenedores');
            let html = '';
            let labelsB = [], dataB = [], donaMap = { 'alta': 0, 'media': 0, 'normal': 0 };
            let totalHum = 0, totalTemp = 0, totalConf = 0, totalCap = 0;

            contenedores.forEach(c => {
                const prio = (c.prioridad || 'normal').toLowerCase();
                let prioClass = 'st-normal';
                if(prio === 'alta') { prioClass = 'st-lleno'; donaMap['alta']++; }
                else if(prio === 'media') { prioClass = 'st-medio'; donaMap['media']++; }
                else { donaMap['normal']++; }

                labelsB.push(c.ubicacion);
                // Si la distancia es nula, vacía o inválida (no hay lecturas), asumimos 60cm (vacío al 0%)
                let dist = parseInt(c.distancia);
                if (isNaN(dist)) dist = 60;

                // Cálculo de porcentaje basado en 60cm de altura total
                const fillingPct = Math.min(100, Math.max(0, ((60 - dist) / 60) * 100));
                dataB.push(fillingPct.toFixed(1));
                
                totalHum += parseFloat(c.humedad || 0);
                totalTemp += parseFloat(c.temperatura || 0);
                totalConf += parseFloat(c.confianza || 0);
                totalCap += parseFloat(c.capacidad || 0);

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
                        <small style="display:block; font-size:10px; color:var(--text-sub); margin-top:2px;">${fillingPct.toFixed(0)}% de llenado</small>
                    </td>
                    <td><span class="status ${prioClass}">${prio.toUpperCase()}</span></td>
                </tr>`;


            });

            tbody.innerHTML = html;
            Charts.update(labelsB, dataB, ['ALTA', 'MEDIA', 'NORMAL'], [donaMap['alta'], donaMap['media'], donaMap['normal']]);
            
            const count = contenedores.length || 1;
            document.getElementById('kpi-total').textContent = contenedores.length;
            document.getElementById('kpi-capacidad').textContent = totalCap + ' L';
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
