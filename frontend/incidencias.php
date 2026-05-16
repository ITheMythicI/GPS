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
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <main id="content">
        <?php include 'includes/header.php'; ?>

        <div style="margin-top: 30px;">
            <section class="panel-box" style="width: 100%;">
                <div class="panel-header">
                    <h3><i class="fa-solid fa-flag"></i> Reportes de Incidencias</h3>
                    <p style="font-size: 13px; color: #666;">Monitoreo de problemas reportados por usuarios</p>
                </div>
                <div id="lista-reportes-incidencias" style="padding: 20px;">
                    <p style="color:#666;">Cargando reportes...</p>
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
                            <thead style="background:#f8f9fa; border-bottom:2px solid #eee;">
                                <tr>
                                    <th style="padding:15px; text-align:left;">Fecha</th>
                                    <th style="padding:15px; text-align:left;">Contenedor</th>
                                    <th style="padding:15px; text-align:left;">Usuario</th>
                                    <th style="padding:15px; text-align:left;">Tipo</th>
                                    <th style="padding:15px; text-align:left;">Descripción</th>
                                    <th style="padding:15px; text-align:left;">Multimedia</th>
                                    <th style="padding:15px; text-align:left;">Ubicación</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${resRep.data.map(r => `
                                    <tr style="border-bottom:1px solid #eee;">
                                        <td style="padding:15px;">${r.fecha_hora}</td>
                                        <td style="padding:15px;"><strong>${r.contenedor_nombre || 'N/A'}</strong></td>
                                        <td style="padding:15px;">${r.usuario_nombre || 'Anónimo'}</td>
                                        <td style="padding:15px;"><span style="padding:4px 10px; border-radius:12px; background:#ffebe9; color:#cf222e; font-size:10px; font-weight:bold; text-transform:uppercase;">${r.tipo_incidencia}</span></td>
                                        <td style="padding:15px;">${r.descripcion}</td>
                                        <td style="padding:15px;">
                                            ${r.foto_url ? `<a href="http://129.146.115.127/${r.foto_url}" target="_blank" style="color:var(--primary); font-weight:600;"><i class="fa-solid fa-image"></i> Ver Foto</a>` : '<span style="color:#ccc;">Sin foto</span>'}
                                        </td>
                                        <td style="padding:15px;">
                                            ${r.lat_reporte ? `<a href="https://www.google.com/maps?q=${r.lat_reporte},${r.lng_reporte}" target="_blank" style="color:#6c3fc5; font-weight:600;"><i class="fa-solid fa-location-dot"></i> Mapa</a>` : '<span style="color:#ccc;">N/A</span>'}
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
