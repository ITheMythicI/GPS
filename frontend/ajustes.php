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
                        <span>Modo Oscuro</span>
                    </label>
                </div>
                <button class="btn-update" onclick="guardarPerfil()">Guardar Cambios de Perfil</button>
            </section>

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

        document.addEventListener('DOMContentLoaded', cargarAjustes);
    </script>
</body>
</html>
