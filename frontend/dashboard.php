<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$url = "http://10.0.2.8/obtenerContenedores.php";

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

    <header id="main-header">

        <div class="logo-area">

            <div class="logo-box">
                <img src="BIN.png" alt="Nexus Solutions Logo">
            </div>

            <div>
                <h1>PORTAL BIN</h1>
                <p>Bienvenido</p>
            </div>

        </div>

        <div class="header-right">

            <div class="tool-icons">

                <i class="fa-regular fa-note-sticky"></i>

                <i class="fa-solid fa-triangle-exclamation">
                    <span class="notification-dot"></span>
                </i>

                <i class="fa-regular fa-bell"></i>
                <i class="fa-regular fa-bookmark"></i>

            </div>

            <div class="user-profile-circle">
                <i class="fa-solid fa-user"></i>
            </div>

        </div>

    </header>


    <!-- SIDEBAR -->

    <aside id="sidebar">

        <div class="nav-section">GENERAL</div>

        <div class="menu-item">
            <a href="index.html" class="menu-btn active">
                <span><i class="fa-solid fa-gauge-high"></i> Página Principal </span>
            </a>
        </div>

        <div class="menu-item">
            <a href="dashboard.php" class="menu-btn">
                <span><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
            </a>
        </div>

        <div class="nav-section">DATOS</div>

        <div class="menu-item">

            <input type="checkbox" id="inventario" class="menu-check">

            <label for="inventario" class="menu-btn">
                <span><i class="fa-solid fa-box"></i> Inventario</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>

            <ul class="submenu">
                <li><a href="#">Contenedores</a></li>
                <li><a href="#">Camiones</a></li>
                <li><a href="dashboardMapa.php">Mapa Interactivo</a></li>
            </ul>

        </div>

        <!-- ADMINISTRACIÓN -->

        <div class="nav-section">ADMINISTRACIÓN</div>

        <div class="menu-item">
            <input type="checkbox" id="m-bit" class="menu-check">
            <label for="m-bit" class="menu-btn">
                <span><i class="fa-solid fa-book"></i> Registros</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Registro de Actividad</a></li>
            </ul>
        </div>

        <div class="menu-item">
            <input type="checkbox" id="m-fin" class="menu-check">
            <label for="m-fin" class="menu-btn">
                <span><i class="fa-solid fa-landmark"></i> Finanzas</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Flujo de Caja</a></li>
                <li><a href="#">Facturacion</a></li>
            </ul>
        </div>


        <div class="menu-item">
            <input type="checkbox" id="m-gas" class="menu-check">
            <label for="m-gas" class="menu-btn">
                <span><i class="fa-solid fa-file-invoice-dollar"></i> Gastos</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Socios</a></li>
            </ul>
        </div>


        <div class="nav-section">CONFIGURACIÓN</div>

        <div class="menu-item">
            <a class="menu-btn">
                <span><i class="fa-solid fa-gear"></i> Ajustes del Sistema</span>
            </a>
        </div>

    </aside>


    <!-- CONTENIDO -->

    <main id="content">

        <section class="stats-grid">

            <div class="card">

                <div>

                    <div class="card-label">Registros del mes</div>
                    <div class="card-value">1.5 Ton de basura recolectada</div>

                    <div class="card-note positive">
                        ↑ 12% mayor que el mes pasado
                    </div>

                </div>

                <div class="card-icon" style="background:#e2efda;color:#217346;">
                    <i class="fa-solid fa-chart-line"></i>
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
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos_contenedores as $contenedor): ?>
                        <tr>
                            <td><?= htmlspecialchars($contenedor['ubicacion'] ?? '') ?></td>
                            <td><?= htmlspecialchars($contenedor['latitud'] ?? '') ?></td>
                            <td><?= htmlspecialchars($contenedor['longitud'] ?? '') ?></td>
                            <td><?= htmlspecialchars($contenedor['capacidad'] ?? '') ?></td>
                            <td>
                                <span class="status <?= 'st-' . strtolower($contenedor['estado'] ?? '') ?>">
                                    <?= htmlspecialchars($contenedor['estado'] ?? 'Sin estado') ?>
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
    <script src="js/graficas.js"></script>

    <!-- ── Lógica Reporte IA ── -->
    <script>
    function generarReporteIA() {
        var btn = document.getElementById('btn-generar-reporte');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Generando...';

        var contenido = document.getElementById('reporte-ia-contenido');
        var resumen   = document.getElementById('reporte-ia-resumen');
        contenido.style.display = 'none';
        resumen.style.display   = 'none';

        fetch('api/ia_proxy.php?action=reporte', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
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
            })
            .catch(err => {
                contenido.textContent = '⚠️ Error al generar reporte: ' + err.message;
                contenido.style.display = 'block';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-robot"></i>&nbsp; Generar Reporte IA';
            });
    }
    </script>

</body>

</html>
