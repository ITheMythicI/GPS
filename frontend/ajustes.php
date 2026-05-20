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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes | BIN</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="app-shell settings-page <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
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
