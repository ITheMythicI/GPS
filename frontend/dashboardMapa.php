<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$url = "http://10.0.2.8/obtenerContenedores.php";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);

if ($response === false) {
    die("cURL error: " . curl_error($ch));
}

curl_close($ch);

$data = json_decode($response, true);

if (!$data || $data['status'] !== 'ok') {
    die("Error en respuesta del backend");
}

$datos_contenedores = $data['data'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bin - Mapa Interactivo</title>

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        /* Contenedor relativo para que la UI flote sobre el mapa */
        #map-wrapper {
            position: relative;
            width: 100%;
            height: 650px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        #map {
            height: 100% !important;
            width: 100%;
            z-index: 1;
        }

        /* ── Botón Ruta IA ── */
        #btn-ruta-ia {
            position: absolute;
            bottom: 20px;
            right: 12px;
            z-index: 999;
            background: linear-gradient(135deg, #6c3fc5, #3b82f6);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(108,63,197,0.45);
            transition: opacity 0.2s, transform 0.15s;
        }
        #btn-ruta-ia:hover  { opacity: 0.9; transform: translateY(-1px); }
        #btn-ruta-ia:active { transform: translateY(0); }
        #btn-ruta-ia:disabled { opacity: 0.55; cursor: default; }

        /* ── Info panel ruta IA ── */
        #ruta-info {
            position: absolute;
            bottom: 62px;
            right: 12px;
            z-index: 999;
            background: rgba(255,255,255,0.96);
            border-radius: 8px;
            padding: 10px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            max-width: 220px;
            display: none;
        }
        #ruta-info strong { color: #6c3fc5; }
    </style>
</head>

<body>
    <header id="main-header">
        <div class="logo-area">
            <div class="logo-box"><img src="BIN.png" alt="Logo"></div>
            <div>
                <h1>PORTAL BIN</h1>
                <p>BuildNess Management</p>
            </div>
        </div>
        <div class="header-right">
            <div class="tool-icons">
                <i class="fa-regular fa-bell"></i>
                <i class="fa-solid fa-triangle-exclamation"><span class="notification-dot"></span></i>
            </div>
            <div class="user-profile-circle"><i class="fa-solid fa-user"></i></div>
        </div>
    </header>

    <aside id="sidebar">
        <div class="nav-section">GENERAL</div>
        <div class="menu-item">
            <a href="dashboard.php" class="menu-btn"><span><i class="fa-solid fa-gauge-high"></i> Dashboard</span></a>
        </div>
        <div class="nav-section">DATOS</div>
        <div class="menu-item">
            <label class="menu-btn active"><span><i class="fa-solid fa-map-location-dot"></i> Mapa Interactivo</span></label>
        </div>
        <div class="menu-item">
            <a href="index.html" class="menu-btn"><span><i class="fa-solid fa-house"></i> Salir</span></a>
        </div>
    </aside>

    <main id="content">
        <div id="map-wrapper">
            <div id="map">
                <div id="ui-controls" class="ui-container">
                    <div id="selector-card" class="header-card" onclick="toggleAreaList()">
                        <span id="zone-name">📍 Seleccionar Área</span>
                        <i class="fa-solid fa-chevron-down" id="arrow-icon"></i>
                    </div>
                    <div id="area-list" class="area-list collapsed">
                        <div class="area-item" onclick="selectArea('sistemas')">Área de Sistemas</div>
                        <div class="area-item" onclick="selectArea('quimica')">Área de Química</div>
                    </div>
                    <div id="info-card" class="info-card hidden">
                        <div class="info-header">
                            <h4 id="card-title"></h4>
                            <button class="btn-back" onclick="resetUI()">⬅ Volver</button>
                        </div>
                        <hr id="card-hr" class="card-hr">
                        <div class="info-body">
                            <p><strong>📦 Contenedores:</strong> <span id="card-cont"></span></p>
                            <p><strong>📋 Registros:</strong> <span id="card-reg"></span></p>
                            <p><strong>⚠️ Prioridad:</strong> <span id="card-prior" class="priority-badge"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- ── Botón Ruta IA (flota sobre el mapa) ── -->
            <button id="btn-ruta-ia" onclick="calcularRutaIA()">
                <i class="fa-solid fa-route"></i>&nbsp; Ver Ruta IA
            </button>

            <!-- ── Panel info de ruta ── -->
            <div id="ruta-info">
                <strong>🛣️ Ruta Óptima</strong><br>
                <span id="ruta-distancia"></span><br>
                <span id="ruta-paradas"></span>
            </div>

        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Pasar datos de PHP a una variable Global de JS
       const datosContenedores = <?php echo json_encode($datos_contenedores); ?>;
    </script>
    
    <script src="js/mapaContenedores.js"></script>

    <!-- ── Lógica Ruta IA ── -->
    <script>
    var _rutaPolyline  = null; // referencia al polyline activo
    var _rutaMarkers   = [];   // marcadores numerados de la ruta

    function calcularRutaIA() {
        var btn = document.getElementById('btn-ruta-ia');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Calculando...';

        // Paso 1: clasificar contenedores
        fetch('api/ia_proxy.php?action=clasificar')
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'ok') throw new Error(data.message || 'Error clasificando');

                // Paso 2: calcular ruta con los prioritarios
                return fetch('api/ia_proxy.php?action=rutas');
            })
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'ok') throw new Error(data.message || 'Error en rutas');

                if (!data.coordenadas || data.coordenadas.length === 0) {
                    alert('No hay contenedores de alta prioridad para rutar.\n\nTodos los contenedores están en niveles normales.');
                    return;
                }

                dibujarRuta(data);
            })
            .catch(err => {
                console.error('[RutaIA]', err);
                alert('Error al calcular ruta: ' + err.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-route"></i>&nbsp; Ver Ruta IA';
            });
    }

    function dibujarRuta(data) {
        // Limpiar ruta anterior
        if (_rutaPolyline) { map.removeLayer(_rutaPolyline); _rutaPolyline = null; }
        _rutaMarkers.forEach(m => map.removeLayer(m));
        _rutaMarkers = [];

        // Dibujar polyline
        _rutaPolyline = L.polyline(data.coordenadas, {
            color:     '#6c3fc5',
            weight:    4,
            opacity:   0.85,
            dashArray: '8, 6'
        }).addTo(map);

        // Marcadores numerados en la ruta
        data.ruta_ordenada.forEach(function(c, idx) {
            var numIcon = L.divIcon({
                className: 'custom-div-icon',
                html: '<div style="background:#6c3fc5;color:#fff;border-radius:50%;'
                    + 'width:24px;height:24px;display:flex;align-items:center;'
                    + 'justify-content:center;font-weight:700;font-size:12px;'
                    + 'box-shadow:0 2px 6px rgba(0,0,0,0.3);">' + (idx + 1) + '</div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            var m = L.marker([c.latitud, c.longitud], { icon: numIcon })
                .bindPopup('<b>Parada ' + (idx+1) + '</b><br>' + (c.ubicacion || '') +
                    '<br>Prioridad: <b>' + (c.prioridad || '') + '</b>' +
                    '<br>Llenado: ' + (c.volumen_pct || 0).toFixed(1) + '%')
                .addTo(map);
            _rutaMarkers.push(m);
        });

        // Ajustar vista al polyline
        map.fitBounds(_rutaPolyline.getBounds(), { padding: [30, 30] });

        // Mostrar panel info
        var panel = document.getElementById('ruta-info');
        document.getElementById('ruta-distancia').textContent =
            '📏 Distancia total: ' + data.distancia_km + ' km';
        document.getElementById('ruta-paradas').textContent =
            '📦 Paradas: ' + data.total_paradas + ' contenedor(es)';
        panel.style.display = 'block';
    }
    </script>

</body>
</html>