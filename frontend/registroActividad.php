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
    <title>Registro de Actividad | BIN</title>
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
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> Registro de Actividad</h3>
                    <p style="font-size: 13px; color: #666;">Historial de tráfico y acciones de usuarios</p>
                </div>
                <div id="lista-actividad" style="padding: 20px;">
                    <p style="color:#666;">Cargando actividad...</p>
                </div>
            </section>
        </div>
    </main>

    <script src="js/api.js"></script>
    <script>
        async function cargarActividad() {
            const res = await API.obtenerActividad();
            if (res.status === 'ok') {
                const lista = document.getElementById('lista-actividad');
                if (res.data.length === 0) {
                    lista.innerHTML = '<p style="text-align:center; padding:40px; color:#999;">No hay actividad registrada recientemente.</p>';
                } else {
                    lista.innerHTML = `
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead style="background:#f8f9fa; border-bottom:2px solid #eee;">
                                <tr>
                                    <th style="padding:15px; text-align:left;">Fecha / Hora</th>
                                    <th style="padding:15px; text-align:left;">Usuario</th>
                                    <th style="padding:15px; text-align:left;">Rol</th>
                                    <th style="padding:15px; text-align:left;">Acción</th>
                                    <th style="padding:15px; text-align:left;">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${res.data.map(a => `
                                    <tr style="border-bottom:1px solid #eee;">
                                        <td style="padding:15px; color:#666;">${a.fecha_hora}</td>
                                        <td style="padding:15px;"><strong>${a.usuario_nombre || 'Sistema'}</strong></td>
                                        <td style="padding:15px;"><span style="font-size:11px; color:#7f8c8d;">${a.rol || '-'}</span></td>
                                        <td style="padding:15px;"><i class="fa-solid fa-right-to-bracket" style="color:var(--primary); margin-right:8px;"></i> ${a.accion}</td>
                                        <td style="padding:15px; font-family:monospace; color:#666;">${a.ip_address || '0.0.0.0'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                }
            }
        }
        document.addEventListener('DOMContentLoaded', cargarActividad);
    </script>
</body>
</html>
