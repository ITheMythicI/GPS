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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Actividad | BIN</title>
    <link rel="stylesheet" href="css/normalize.css">
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
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> Registro de Actividad</h3>
                    <p style="font-size: 13px; color: var(--text-sub);">Historial de tráfico y acciones de usuarios</p>
                </div>
                <div id="lista-actividad" style="padding: 20px;">
                    <p style="color:var(--text-sub);">Cargando actividad...</p>
                </div>
            </section>
        </div>
    </main>

    <script src="js/api.js"></script>
    <script>
        function escHtml(texto) {
            const d = document.createElement('div');
            d.textContent = texto ?? '';
            return d.innerHTML;
        }

        async function cargarActividad() {
            const lista = document.getElementById('lista-actividad');
            try {
                const res = await API.obtenerActividad();
                if (res.status !== 'ok') {
                    lista.innerHTML = '<p style="text-align:center; padding:40px; color:#cf222e;">No se pudo cargar actividad: ' + escHtml(res.message || 'Error desconocido') + '</p>';
                    return;
                }

                const filas = Array.isArray(res.data) ? res.data : [];
                if (filas.length === 0) {
                    lista.innerHTML = '<p style="text-align:center; padding:40px; color:#999;">No hay actividad registrada recientemente. Inicia sesión para generar nuevos registros.</p>';
                    return;
                }

                lista.innerHTML = `
                    <div class="activity-table-wrap">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead style="background:var(--bg-header); border-bottom:2px solid var(--border);">
                            <tr>
                                <th style="padding:15px; text-align:left;">Fecha / Hora</th>
                                <th style="padding:15px; text-align:left;">Usuario</th>
                                <th style="padding:15px; text-align:left;">Rol</th>
                                <th style="padding:15px; text-align:left;">Acción</th>
                                <th style="padding:15px; text-align:left;">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filas.map(a => `
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:15px; color:var(--text-sub);">${escHtml(a.fecha_hora)}</td>
                                    <td style="padding:15px;"><strong>${escHtml(a.usuario_nombre || 'Sistema')}</strong></td>
                                    <td style="padding:15px;"><span style="font-size:11px; color:var(--text-sub);">${escHtml(a.rol || '-')}</span></td>
                                    <td style="padding:15px;"><i class="fa-solid fa-right-to-bracket" style="color:var(--primary); margin-right:8px;"></i> ${escHtml(a.accion)}</td>
                                    <td style="padding:15px; font-family:monospace; color:var(--text-sub);">${escHtml(a.ip_address || '0.0.0.0')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    </div>
                `;
            } catch (err) {
                lista.innerHTML = '<p style="text-align:center; padding:40px; color:#cf222e;">Error de red al cargar actividad.</p>';
                console.error(err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarActividad();
            setInterval(cargarActividad, 30000);
        });
    </script>
</body>
</html>
