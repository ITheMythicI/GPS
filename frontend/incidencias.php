<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Incidencias | BIN</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .select-estado {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid transparent;
            outline: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .select-estado[data-status="Sin resolver"],
        .select-estado[data-status="Pendiente"] {
            background: #ffebe9;
            color: #cf222e;
            border-color: #ffc5c2;
        }
        .select-estado[data-status="En revisión"],
        .select-estado[data-status="En Revisión"] {
            background: #fff8c5;
            color: #9e6a00;
            border-color: #f1e05a;
        }
        .select-estado[data-status="Resuelta"],
        .select-estado[data-status="Resuelto"] {
            background: #dafbe1;
            color: #1a7f37;
            border-color: #8ae8a1;
        }

        .dark-theme .select-estado[data-status="Sin resolver"],
        .dark-theme .select-estado[data-status="Pendiente"] {
            background: rgba(248,81,73,0.15);
            color: #f85149;
            border-color: rgba(248,81,73,0.4);
        }
        .dark-theme .select-estado[data-status="En revisión"],
        .dark-theme .select-estado[data-status="En Revisión"] {
            background: rgba(210,144,40,0.15);
            color: #d29004;
            border-color: rgba(210,144,40,0.4);
        }
        .dark-theme .select-estado[data-status="Resuelta"],
        .dark-theme .select-estado[data-status="Resuelto"] {
            background: rgba(56,139,60,0.15);
            color: #56d364;
            border-color: rgba(56,139,60,0.4);
        }
    </style>
</head>
<body class="<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main id="content">

        <div style="margin-top: 30px;">
            <section class="panel-box" style="width: 100%;">
                <div class="panel-header">
                    <h3><i class="fa-solid fa-flag"></i> Reportes de Incidencias</h3>
                    <p style="font-size: 13px; color: var(--text-sub);">Monitoreo de problemas reportados por usuarios</p>
                </div>
                <div id="lista-reportes-incidencias" style="padding: 20px;">
                    <p style="color:var(--text-sub);">Cargando reportes...</p>
                </div>
            </section>
        </div>
    </main>

    <script src="js/api.js"></script>
    <script>
        function normalizarEstado(est) {
            if (!est) return 'Sin resolver';
            if (est === 'Pendiente') return 'Sin resolver';
            if (est === 'En Revisión') return 'En revisión';
            if (est === 'Resuelto') return 'Resuelta';
            return est;
        }

        async function cambiarEstado(idReporte, nuevoEstado) {
            try {
                const res = await API.actualizarEstadoReporte(idReporte, nuevoEstado);
                if (res.status === 'ok') {
                    console.log(`Estado de reporte #${idReporte} actualizado a ${nuevoEstado}`);
                } else {
                    alert("Error al actualizar estado: " + res.message);
                    cargarIncidencias(); // Recargar para revertir visualmente
                }
            } catch (err) {
                alert("Error de conexión al actualizar estado.");
                cargarIncidencias();
            }
        }

        async function confirmarEliminar(idReporte) {
            if (confirm(`¿Estás seguro de que deseas eliminar permanentemente el reporte #${idReporte}?`)) {
                try {
                    const res = await API.borrarReporte(idReporte);
                    if (res.status === 'ok') {
                        alert("Reporte eliminado correctamente.");
                        cargarIncidencias();
                    } else {
                        alert("Error al eliminar reporte: " + res.message);
                    }
                } catch (err) {
                    alert("Error de conexión al eliminar reporte.");
                }
            }
        }

        async function cargarIncidencias() {
            const resRep = await API.obtenerReportes();
            if (resRep.status === 'ok') {
                const listaRep = document.getElementById('lista-reportes-incidencias');
                if (resRep.data.length === 0) {
                    listaRep.innerHTML = '<p style="text-align:center; padding:40px; color:#999;">No hay reportes registrados.</p>';
                } else {
                    listaRep.innerHTML = `
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead style="background:var(--bg-header); border-bottom:2px solid var(--border);">
                                <tr>
                                    <th style="padding:15px; text-align:left;">Fecha</th>
                                    <th style="padding:15px; text-align:left;">Contenedor / Ubicación</th>
                                    <th style="padding:15px; text-align:left;">Usuario</th>
                                    <th style="padding:15px; text-align:left;">Tipo</th>
                                    <th style="padding:15px; text-align:left;">Descripción</th>
                                    <th style="padding:15px; text-align:left;">Estado</th>
                                    <th style="padding:15px; text-align:left;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${resRep.data.map(r => {
                                    const estNorm = normalizarEstado(r.estado);
                                    return `
                                        <tr style="border-bottom:1px solid var(--border);">
                                            <td style="padding:15px;">${r.fecha_hora}</td>
                                            <td style="padding:15px;">
                                                <strong>${r.contenedor_nombre || 'No Asignado'}</strong><br>
                                                <span style="font-size:11px; color:var(--text-sub);"><i class="fa-solid fa-location-dot"></i> ${r.lat_reporte ? 'GPS Local' : 'Por ubicación de contenedor'}</span>
                                            </td>
                                            <td style="padding:15px;">${r.usuario_nombre || 'Anónimo'}</td>
                                            <td style="padding:15px;"><span class="status st-cancelado">${r.tipo_incidencia}</span></td>
                                            <td style="padding:15px;">${r.descripcion.substring(0, 40)}${r.descripcion.length > 40 ? '...' : ''}</td>
                                            <td style="padding:15px;">
                                                <select class="select-estado" data-status="${estNorm}" onchange="this.setAttribute('data-status', this.value); cambiarEstado(${r.id_reporte}, this.value)">
                                                    <option value="Sin resolver" ${estNorm === 'Sin resolver' ? 'selected' : ''}>Sin resolver</option>
                                                    <option value="En revisión" ${estNorm === 'En revisión' ? 'selected' : ''}>En revisión</option>
                                                    <option value="Resuelta" ${estNorm === 'Resuelta' ? 'selected' : ''}>Resuelta</option>
                                                </select>
                                            </td>
                                            <td style="padding:15px;">
                                                <div style="display:flex; gap:8px;">
                                                    <a href="verIncidencia.php?id=${r.id_reporte}" style="display:inline-block; padding:6px 12px; background:var(--primary); color:white; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;"><i class="fa-solid fa-eye"></i> Detalles</a>
                                                    <button onclick="confirmarEliminar(${r.id_reporte})" style="display:inline-block; padding:6px 12px; background:#e74c3c; color:white; border-radius:6px; border:none; font-size:12px; font-weight:600; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#c0392b'" onmouseout="this.style.background='#e74c3c'"><i class="fa-solid fa-trash"></i> Borrar</button>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    `;
                }
            }
        }
        document.addEventListener('DOMContentLoaded', cargarIncidencias);
    </script>
</body>
</html>
