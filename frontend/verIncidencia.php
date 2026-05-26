<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}
$id_reporte = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Incidencia | BIN</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .incidencia-container {
            max-width: 800px;
            margin: 30px auto;
            background: var(--bg-panel);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 25px;
        }
        .header-incidencia {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-incidencia h2 {
            margin: 0;
            color: var(--text-main);
        }
        .detalle-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .detalle-item {
            background: var(--bg-neutral);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        .detalle-item label {
            display: block;
            font-size: 12px;
            color: var(--text-sub);
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .detalle-item > span:not(.status) {
            font-size: 15px;
            color: var(--text-main);
            font-weight: 500;
        }
        .incidencia-foto {
            width: 100%;
            border-radius: 8px;
            border: 2px solid var(--border);
            max-height: 500px;
            object-fit: contain;
            background: #000;
        }
        .select-estado {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding: 5px 25px 5px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid transparent;
            outline: none;
            cursor: pointer;
            transition: all 0.2s ease;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 10px;
        }
        
        .select-estado[data-status="Sin resolver"],
        .select-estado[data-status="Pendiente"] {
            background-color: #ffebe9;
            color: #cf222e;
            border-color: #ffc5c2;
        }
        .select-estado[data-status="En revisión"],
        .select-estado[data-status="En Revisión"] {
            background-color: #fff8c5;
            color: #9e6a00;
            border-color: #f1e05a;
        }
        .select-estado[data-status="Resuelta"],
        .select-estado[data-status="Resuelto"] {
            background-color: #dafbe1;
            color: #1a7f37;
            border-color: #8ae8a1;
        }

        .dark-theme .select-estado {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ccc' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        }
        .dark-theme .select-estado[data-status="Sin resolver"],
        .dark-theme .select-estado[data-status="Pendiente"] {
            background-color: rgba(248,81,73,0.15);
            color: #f85149;
            border-color: rgba(248,81,73,0.4);
        }
        .dark-theme .select-estado[data-status="En revisión"],
        .dark-theme .select-estado[data-status="En Revisión"] {
            background-color: rgba(210,144,40,0.15);
            color: #d29004;
            border-color: rgba(210,144,40,0.4);
        }
        .dark-theme .select-estado[data-status="Resuelta"],
        .dark-theme .select-estado[data-status="Resuelto"] {
            background-color: rgba(56,139,60,0.15);
            color: #56d364;
            border-color: rgba(56,139,60,0.4);
        }
    </style>
</head>
<body class="app-shell <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main id="content">
        <div class="incidencia-container">
            <div class="header-incidencia">
                <h2><i class="fa-solid fa-file-contract"></i> Detalle de Incidencia #<?= htmlspecialchars($id_reporte) ?></h2>
                <a href="incidencias.php" class="btn" style="text-decoration:none; color:var(--text-main);"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            </div>

            <div id="loading" style="text-align:center; padding: 40px; color:var(--text-sub);">
                <i class="fa-solid fa-spinner fa-spin fa-2x"></i><br><br>Cargando información...
            </div>

            <div id="incidencia-content" style="display:none;">
                <div class="detalle-grid">
                    <div class="detalle-item">
                        <label>Contenedor Afectado</label>
                        <span id="d-contenedor"></span>
                    </div>
                    <div class="detalle-item">
                        <label>Tipo de Incidencia</label>
                        <span id="d-tipo" class="status st-cancelado" style="display:inline-block;"></span>
                    </div>
                    <div class="detalle-item">
                        <label>Fecha y Hora</label>
                        <span id="d-fecha"></span>
                    </div>
                    <div class="detalle-item">
                        <label>Reportado por</label>
                        <span id="d-usuario"></span>
                    </div>
                    <div class="detalle-item">
                        <label>Estado de Incidencia</label>
                        <select id="d-estado-select" class="select-estado" style="margin-top:5px;"></select>
                    </div>
                    <div class="detalle-item">
                        <label>Ubicación / Referencia</label>
                        <span id="d-ubicacion" style="display:block; margin-top:5px;"></span>
                    </div>
                </div>

                <div class="detalle-item" style="margin-bottom:20px;">
                    <label>Descripción del Problema</label>
                    <span id="d-desc" style="display:block; padding:10px; background:var(--bg-panel); border-radius:6px; margin-top:5px;"></span>
                </div>

                <h3 style="margin-top:30px; margin-bottom:15px; font-size:16px;"><i class="fa-solid fa-image"></i> Evidencia Multimedia</h3>
                <div id="foto-container" style="text-align:center; padding:20px; background:var(--bg-neutral); border-radius:8px; border:1px dashed var(--border);">
                    <!-- Imagen cargada aquí -->
                </div>
            </div>
        </div>
    </main>

    <script src="js/api.js"></script>
    <script>
        const ID_REPORTE = <?= json_encode($id_reporte) ?>;

        function normalizarEstado(est) {
            if (!est) return 'Sin resolver';
            if (est === 'Pendiente') return 'Sin resolver';
            if (est === 'En Revisión') return 'En revisión';
            if (est === 'Resuelto') return 'Resuelta';
            return est;
        }

        async function cargarDetalle() {
            try {
                const res = await API.obtenerReportes();
                if (res.status === 'ok') {
                    // Encontrar el reporte específico
                    const rep = res.data.find(r => r.id_reporte == ID_REPORTE);
                    
                    if (rep) {
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('incidencia-content').style.display = 'block';

                        document.getElementById('d-contenedor').textContent = rep.contenedor_nombre || 'No asignado';
                        document.getElementById('d-tipo').textContent = rep.tipo_incidencia || 'General';
                        document.getElementById('d-fecha').textContent = rep.fecha_hora;
                        document.getElementById('d-usuario').textContent = rep.usuario_nombre || 'Anónimo';
                        document.getElementById('d-desc').textContent = rep.descripcion || 'Sin descripción';
                        
                        // Ubicación lógica o coordenadas
                        let ubicacionHTML = '';
                        if (rep.lat_reporte && rep.lng_reporte) {
                            ubicacionHTML = `<a href="https://www.google.com/maps?q=${rep.lat_reporte},${rep.lng_reporte}" target="_blank" style="color:var(--primary); font-weight:bold;"><i class="fa-solid fa-location-dot"></i> Ver en Mapa (${rep.lat_reporte}, ${rep.lng_reporte})</a>`;
                        } else if (rep.contenedor_nombre) {
                            ubicacionHTML = `<span style="color:var(--text-sub);"><i class="fa-solid fa-map-pin"></i> Ubicación inferida: <strong>${rep.contenedor_nombre}</strong></span>`;
                        } else {
                            ubicacionHTML = `<span style="color:var(--text-sub);">Ubicación no disponible</span>`;
                        }
                        document.getElementById('d-ubicacion').innerHTML = ubicacionHTML;

                        // Estado
                        const estNorm = normalizarEstado(rep.estado);
                        const stateSelect = document.getElementById('d-estado-select');
                        stateSelect.innerHTML = `
                            <option value="Sin resolver" ${estNorm === 'Sin resolver' ? 'selected' : ''}>Sin resolver</option>
                            <option value="En revisión" ${estNorm === 'En revisión' ? 'selected' : ''}>En revisión</option>
                            <option value="Resuelta" ${estNorm === 'Resuelta' ? 'selected' : ''}>Resuelta</option>
                        `;
                        stateSelect.setAttribute('data-status', estNorm);
                        stateSelect.onchange = async function() {
                            const newStatus = this.value;
                            this.setAttribute('data-status', newStatus);
                            try {
                                const resUpdate = await API.actualizarEstadoReporte(ID_REPORTE, newStatus);
                                if (resUpdate.status !== 'ok') {
                                    alert("Error al actualizar estado: " + resUpdate.message);
                                }
                            } catch (err) {
                                alert("Error de conexión al actualizar estado.");
                            }
                        };

                        // Foto
                        const fotoContainer = document.getElementById('foto-container');
                        if (rep.foto_url) {
                            const img = document.createElement('img');
                            img.src = `api/image_proxy.php?path=${encodeURIComponent(rep.foto_url)}`;
                            img.className = 'incidencia-foto';
                            img.alt = 'Evidencia';
                            img.onerror = function() {
                                this.outerHTML = '<div style="padding:40px; color:var(--text-sub); text-align:center;"><i class="fa-solid fa-image-slash fa-2x"></i><br><br>No se pudo cargar la imagen de evidencia</div>';
                            };
                            fotoContainer.innerHTML = '';
                            fotoContainer.appendChild(img);
                        } else {
                            fotoContainer.innerHTML = `<p style="color:var(--text-sub);"><i class="fa-solid fa-camera-slash fa-2x"></i><br><br>No se adjuntó evidencia fotográfica</p>`;
                        }

                    } else {
                        document.getElementById('loading').innerHTML = `<p style="color:#e74c3c;">Reporte no encontrado.</p>`;
                    }
                }
            } catch (err) {
                document.getElementById('loading').innerHTML = `<p style="color:#e74c3c;">Error al cargar reporte: ${err.message}</p>`;
            }
        }

        document.addEventListener('DOMContentLoaded', cargarDetalle);
    </script>
</body>
</html>
