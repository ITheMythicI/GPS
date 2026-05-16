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
                                    <th style="padding:15px; text-align:left;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${resRep.data.map(r => `
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
                                            <a href="verIncidencia.php?id=${r.id_reporte}" style="display:inline-block; padding:6px 12px; background:var(--primary); color:white; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;"><i class="fa-solid fa-eye"></i> Detalles</a>
                                        </td>
                                    </tr>
                                `).join('')}
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
