<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ajustes del Sistema | BIN</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container { max-width: 800px; margin: 30px auto; display: grid; gap: 25px; }
        .settings-card { background: var(--bg-panel); border-radius: 15px; box-shadow: var(--shadow); padding: 25px; }
        .settings-card h4 { margin-top: 0; color: var(--text-main); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text-sub); }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; background: var(--bg-input); color: var(--text-main); }
        .toggle-switch { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(24px); }
        .profile-preview { display: flex; align-items: center; gap: 20px; }
        .profile-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); }
        .btn-update { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; transition: opacity 0.2s; }
        .btn-update:hover { opacity: 0.9; }
    </style>
</head>
<body class="settings-page <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main id="content">

        <div class="settings-container">
            <!-- 1. Perfil y Personalización (Todos) -->
            <section class="settings-card">
                <h4><i class="fa-solid fa-user-gear"></i> Mi Perfil</h4>
                <div class="form-group">
                    <label>Foto de Perfil</label>
                    <div class="profile-preview">
                        <div id="preview-container" class="profile-img" style="display:flex; align-items:center; justify-content:center; background:#eee; font-size:30px; color:#aaa; overflow:hidden;">
                            <i class="fa-solid fa-user" id="preview-icon"></i>
                            <img src="" id="preview-foto" style="display:none; width:100%; height:100%; object-fit:cover;">
                        </div>
                        <input type="file" id="input-foto" accept="image/*" style="flex:1;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="toggle-switch">
                        <span class="switch">
                            <input type="checkbox" id="toggle-dark">
                            <span class="slider"></span>
                        </span>
                        <span>Modo Oscuro (Beta)</span>
                    </label>
                </div>
                <button class="btn-update" onclick="guardarPerfil()">Guardar Cambios de Perfil</button>
            </section>

            <?php if ($rol === 'administrador'): ?>
            <!-- 2. Parámetros de Simulación (Admin) -->
            <section class="settings-card">
                <h4><i class="fa-solid fa-microchip"></i> Simulación y Sensores</h4>
                <div class="form-group">
                    <label>Velocidad de Simulación (Segundos por ciclo)</label>
                    <input type="number" id="s-velocidad" min="1" max="60">
                </div>
                <div class="form-group">
                    <label class="toggle-switch">
                        <span class="switch">
                            <input type="checkbox" id="s-activo">
                            <span class="slider"></span>
                        </span>
                        <span>Simulador en Tiempo Real Activo</span>
                    </label>
                </div>
            </section>

            <!-- 3. Umbrales de Alerta (Admin) -->
            <section class="settings-card">
                <h4><i class="fa-solid fa-triangle-exclamation"></i> Umbrales de Alerta</h4>
                <div class="form-group">
                    <label>Nivel Crítico de Llenado (%)</label>
                    <input type="number" id="u-llenado" min="50" max="100">
                </div>
                <div class="form-group">
                    <label>Alerta de Batería Baja (%)</label>
                    <input type="number" id="u-bateria" min="1" max="50">
                </div>
                <button class="btn-update" onclick="guardarAjustesGlobales()">Guardar Configuración Global</button>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/api.js"></script>
    <script>
        async function cargarAjustes() {
            const res = await API.obtenerAjustes();
            if (res.status === 'ok') {
                // Info Usuario
                if (res.usuario.foto_perfil) {
                    const img = document.getElementById('preview-foto');
                    const icon = document.getElementById('preview-icon');
                    img.src = 'api/image_proxy.php?path=' + encodeURIComponent(res.usuario.foto_perfil);
                    img.style.display = 'block';
                    icon.style.display = 'none';
                }

                const isDark = res.usuario.config_oscuro == 1;
                document.getElementById('toggle-dark').checked = isDark;
                localStorage.setItem('dark_mode', isDark ? '1' : '0');

                // Info Sistema (Si es Admin)
                const s = res.sistema;
                if (document.getElementById('s-velocidad')) {
                    document.getElementById('s-velocidad').value = s.velocidad_simulacion || 1;
                    document.getElementById('s-activo').checked = s.simulador_activo == 1;
                    document.getElementById('u-llenado').value = s.umbral_llenado || 85;
                    document.getElementById('u-bateria').value = s.umbral_bateria || 15;
                }
            }
        }

        // Aplicar dark mode en vivo al cambiar el toggle
        document.getElementById('toggle-dark').addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-theme');
            } else {
                document.body.classList.remove('dark-theme');
            }
        });

        async function guardarPerfil() {
            const fd = new FormData();
            const foto = document.getElementById('input-foto').files[0];
            const dark = document.getElementById('toggle-dark').checked ? 1 : 0;

            if (foto) fd.append('foto', foto);
            fd.append('dark_mode', dark);

            try {
                const res = await API.subirFotoPerfil(fd);
                if (res.status === 'ok') {
                    // Guardar en localStorage para que las demás páginas lo lean
                    localStorage.setItem('dark_mode', dark.toString());
                    alert("Perfil actualizado correctamente");
                    location.reload();
                } else {
                    alert("Error al guardar perfil: " + (res.message || JSON.stringify(res)));
                }
            } catch(e) { alert("Error de red al guardar perfil: " + e); }

        }

        async function guardarAjustesGlobales() {
            const datos = {
                velocidad_simulacion: document.getElementById('s-velocidad').value,
                simulador_activo: document.getElementById('s-activo').checked ? 1 : 0,
                umbral_llenado: document.getElementById('u-llenado').value,
                umbral_bateria: document.getElementById('u-bateria').value
            };

            const res = await API.guardarAjustes(datos);
            if (res.status === 'ok') {
                alert("Ajustes globales guardados");
            } else {
                alert("Error al guardar ajustes");
            }
        }

        document.addEventListener('DOMContentLoaded', cargarAjustes);
    </script>
</body>
</html>
