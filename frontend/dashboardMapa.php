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

        /* ── Modal Reporte ── */
        .modal {
            display: none;
            position: fixed;
            z-index: 3000;
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center; justify-content: center;
        }
        .modal-content {
            background-color: white;
            padding: 24px;
            border-radius: 12px;
            width: 90%; max-width: 450px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h3 { margin: 0; color: var(--text-main); }
        .close-modal { cursor: pointer; font-size: 20px; color: var(--text-sub); }
        .form-report .form-group { margin-bottom: 15px; }
        .form-report label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .form-report input, .form-report select, .form-report textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
        .btn-send-report { width: 100%; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }
    </style>

</head>

<body>
    <?php include_once 'includes/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include_once 'includes/sidebar.php'; ?>

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

        <!-- MODAL REPORTE -->
        <div id="modalReporte" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Reportar Incidencia</h3>
                    <span class="close-modal" onclick="cerrarModalReporte()">&times;</span>
                </div>
                <form id="formIncidencia" class="form-report">
                    <input type="hidden" id="rep-id-contenedor">
                    <p style="font-size: 13px; margin-bottom: 15px; color: #666;">Contenedor: <strong id="rep-nombre-contenedor"></strong></p>
                    
                    <div class="form-group">
                        <label>Tipo de Problema</label>
                        <select id="rep-tipo">
                            <option value="Exceso de Desechos">Exceso de Desechos</option>
                            <option value="Vandalismo">Vandalismo / Daño</option>
                            <option value="Mal Olor">Mal Olor / Higiene</option>
                            <option value="Sensor Fallido">Falla en Sensor</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="rep-desc" rows="3" placeholder="Detalla lo sucedido..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Evidencia Fotográfica</label>
                        <input type="file" id="rep-foto" accept="image/*">
                    </div>

                    <div id="geo-status" style="font-size: 11px; color: #6c3fc5; margin-bottom: 10px; display: none;">
                        <i class="fa-solid fa-location-crosshairs"></i> Ubicación obtenida correctamente.
                    </div>

                    <button type="button" class="btn-send-report" id="btnEnviarReporte" onclick="enviarReporte()">
                        Enviar Reporte
                    </button>
                </form>
            </div>
        </div>
    </main>


    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const datosContenedores = <?= json_encode($datos_contenedores) ?>;
    </script>
    <script src="js/api.js"></script>
    <script src="js/mapaContenedores.js"></script>

    <!-- ── Lógica Ruta IA ── -->
    <script>
    var _rutaPolyline  = null; // referencia al polyline activo
    var _rutaMarkers   = [];   // marcadores numerados de la ruta

    async function calcularRutaIA() {
        const btn = document.getElementById('btn-ruta-ia');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Calculando...';

        try {
            // Paso 1: clasificar contenedores
            const dataClasif = await API.clasificar();
            if (dataClasif.status !== 'ok') throw new Error(dataClasif.message || 'Error clasificando');

            // Paso 2: calcular ruta con los prioritarios
            const dataRuta = await API.obtenerRutas();
            if (dataRuta.status !== 'ok') throw new Error(dataRuta.message || 'Error en rutas');

            if (!dataRuta.coordenadas || dataRuta.coordenadas.length === 0) {
                alert('No hay contenedores de alta prioridad para rutar.\n\nTodos los contenedores están en niveles normales.');
                return;
            }

            dibujarRuta(dataRuta);
        } catch (err) {
            console.error('[RutaIA]', err);
            alert('Error al calcular ruta: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-route"></i>&nbsp; Ver Ruta IA';
        }
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

        // Iniciar movimiento del camión
        MapService.simulateTruckMovement(data.coordenadas);
    }

    </script>

    <!-- ── Lógica de Notificaciones y Actualización en Vivo ── -->
    <script>
    async function actualizarMapaEnVivo() {
        try {
            // Actualizar Alertas (Campana)
            const dataClasif = await API.clasificar();
            if (dataClasif.status === 'ok') {
                const prioritarios = dataClasif.resultados.filter(r => r.prioridad === 'alta');
                const badge = document.querySelector('.notification-dot');
                if (badge) badge.style.display = prioritarios.length > 0 ? 'block' : 'none';
            }

            // Actualizar Marcadores
            const dataCont = await API.obtenerContenedores();
            if (dataCont.status === 'ok') {
                MapService.renderMarkers(dataCont.data);
            }
        } catch (e) { console.error('Error actualizando mapa:', e); }
    }

    async setInterval(actualizarMapaEnVivo, 30000);
    actualizarMapaEnVivo();

    // ── Lógica de Reportes ──
    let currentPos = { lat: null, lng: null };

    function abrirModalReporte(id, nombre) {
        document.getElementById('rep-id-contenedor').value = id;
        document.getElementById('rep-nombre-contenedor').innerText = nombre;
        document.getElementById('modalReporte').style.display = 'flex';
        
        // Intentar obtener ubicación del usuario
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                currentPos.lat = pos.coords.latitude;
                currentPos.lng = pos.coords.longitude;
                document.getElementById('geo-status').style.display = 'block';
            }, (err) => {
                console.warn("Geolocation error:", err);
            });
        }
    }

    function cerrarModalReporte() {
        document.getElementById('modalReporte').style.display = 'none';
        document.getElementById('formIncidencia').reset();
        document.getElementById('geo-status').style.display = 'none';
        currentPos = { lat: null, lng: null };
    }

    async function enviarReporte() {
        const btn = document.getElementById('btnEnviarReporte');
        const idCont = document.getElementById('rep-id-contenedor').value;
        const tipo = document.getElementById('rep-tipo').value;
        const desc = document.getElementById('rep-desc').value;
        const foto = document.getElementById('rep-foto').files[0];

        if (!desc) return alert("Por favor describe el problema.");

        btn.disabled = true;
        btn.innerText = "Enviando...";

        const fd = new FormData();
        fd.append('id_contenedor', idCont);
        fd.append('id_usuario', '<?= $_SESSION['id_usuario'] ?>');
        fd.append('tipo', tipo);
        fd.append('descripcion', desc);
        if (foto) fd.append('foto', foto);
        if (currentPos.lat) {
            fd.append('lat', currentPos.lat);
            fd.append('lng', currentPos.lng);
        }

        try {
            const res = await API.enviarReporteIncidencia(fd);
            if (res.status === 'ok') {
                alert("¡Gracias! El reporte ha sido enviado a los administradores.");
                cerrarModalReporte();
            } else {
                alert("Error al enviar: " + res.message);
            }
        } catch (e) {
            alert("Error de conexión.");
        } finally {
            btn.disabled = false;
            btn.innerText = "Enviar Reporte";
        }
    }
    </script>

</body>
</html>