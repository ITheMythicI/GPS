<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ajustes | BIN</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container { max-width: 820px; margin: 30px auto; display: grid; gap: 25px; }
        .settings-card { background: var(--bg-panel); border-radius: 15px; box-shadow: var(--shadow); padding: 25px; }
        .settings-card h4 { margin-top: 0; color: var(--text-main); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text-sub); }
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; background: var(--bg-input); color: var(--text-main); }
        .toggle-switch { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; transition: .3s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .3s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(24px); }
        .profile-preview { display: flex; align-items: center; gap: 18px; }
        .profile-img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); }
        .btn-update, .btn-toggle-password {
            border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: opacity .2s;
        }
        .btn-update { background: var(--primary); color: #fff; width: 100%; }
        .btn-toggle-password { background: #f3f4f6; color: #374151; width: 100%; margin-top: 8px; }
        .password-form { display: none; margin-top: 15px; }
    </style>
</head>
<body class="settings-page <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <main id="content">
        <div class="settings-container">
            <section class="settings-card">
                <h4><i class="fa-solid fa-user-gear"></i> Perfil y Apariencia</h4>

                <div class="form-group">
                    <label for="input-nombre">Nombre completo</label>
                    <input type="text" id="input-nombre" maxlength="120" placeholder="Tu nombre completo">
                </div>

                <div class="form-group">
                    <label>Foto de perfil</label>
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
                        <span>Modo oscuro</span>
                    </label>
                </div>

                <button class="btn-update" onclick="guardarPerfil()">Guardar cambios</button>
            </section>

            <section class="settings-card">
                <h4><i class="fa-solid fa-lock"></i> Seguridad</h4>
                <button id="btn-toggle-password" class="btn-toggle-password" type="button">
                    Cambiar contraseña
                </button>
                <form id="password-form" class="password-form">
                    <div class="form-group">
                        <label for="password-actual">Contraseña actual</label>
                        <input type="password" id="password-actual" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label for="password-nueva">Nueva contraseña</label>
                        <input type="password" id="password-nueva" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="password-confirmar">Confirmar nueva contraseña</label>
                        <input type="password" id="password-confirmar" autocomplete="new-password">
                    </div>
                    <button class="btn-update" type="submit">Actualizar contraseña</button>
                </form>
            </section>
        </div>
    </main>

    <script src="js/api.js"></script>
    <script>
        async function cargarAjustes() {
            const res = await API.obtenerAjustes();
            if (res.status !== 'ok' || !res.usuario) return;

            document.getElementById('input-nombre').value = res.usuario.nombre || '';

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

        document.getElementById('toggle-dark').addEventListener('change', function() {
            document.body.classList.toggle('dark-theme', this.checked);
        });

        document.getElementById('btn-toggle-password').addEventListener('click', function() {
            const form = document.getElementById('password-form');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        });

        async function guardarPerfil() {
            const fd = new FormData();
            const nombre = document.getElementById('input-nombre').value.trim();
            const foto = document.getElementById('input-foto').files[0];
            const dark = document.getElementById('toggle-dark').checked ? 1 : 0;

            if (!nombre) {
                alert('El nombre es obligatorio');
                return;
            }

            if (foto) fd.append('foto', foto);
            fd.append('dark_mode', dark);
            fd.append('nombre', nombre);

            const res = await API.subirFotoPerfil(fd);
            if (res.status === 'ok') {
                localStorage.setItem('dark_mode', dark.toString());
                alert('Perfil actualizado correctamente');
                location.reload();
            } else {
                alert('Error al guardar perfil: ' + (res.message || 'Error desconocido'));
            }
        }

        document.getElementById('password-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const actual = document.getElementById('password-actual').value;
            const nueva = document.getElementById('password-nueva').value;
            const confirmar = document.getElementById('password-confirmar').value;

            if (nueva.length < 8) {
                alert('La nueva contraseña debe tener al menos 8 caracteres');
                return;
            }
            if (nueva !== confirmar) {
                alert('La confirmación no coincide');
                return;
            }
            if (actual === nueva) {
                alert('La nueva contraseña no puede ser igual a la actual');
                return;
            }

            const res = await API.cambiarPassword({
                password_actual: actual,
                password_nueva: nueva,
                password_confirmar: confirmar
            });

            if (res.status === 'ok') {
                alert('Contraseña actualizada correctamente');
                this.reset();
                this.style.display = 'none';
            } else {
                alert('No se pudo actualizar la contraseña: ' + (res.message || 'Error'));
            }
        });

        document.addEventListener('DOMContentLoaded', cargarAjustes);
    </script>
</body>
</html>
