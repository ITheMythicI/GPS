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
    <title>Perfil | BIN</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="app-shell <?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <main id="content">
        <section class="profile-page">
            <article class="profile-card">
                <div class="profile-photo" id="profile-photo-container">
                    <i class="fa-solid fa-user" id="profile-fallback"></i>
                    <img src="" alt="Foto de perfil" id="profile-photo" style="display:none;">
                </div>
                <h2 class="profile-name" id="profile-name"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h2>
                <div class="profile-role" id="profile-role">
                    <i class="fa-solid fa-shield-halved"></i>
                    Cargando rol...
                </div>
            </article>
        </section>
    </main>

    <script src="js/api.js"></script>
    <script>
        function rolLegible(rol) {
            return rol === 'administrador' ? 'Administrador' : 'Usuario';
        }

        async function cargarPerfil() {
            const res = await API.obtenerAjustes();
            if (res.status !== 'ok' || !res.usuario) return;

            document.getElementById('profile-name').textContent = res.usuario.nombre || 'Usuario';
            document.getElementById('profile-role').innerHTML = '<i class="fa-solid fa-shield-halved"></i> ' + rolLegible(res.usuario.rol || '');

            if (res.usuario.foto_perfil) {
                const img = document.getElementById('profile-photo');
                const icon = document.getElementById('profile-fallback');
                img.src = 'api/image_proxy.php?path=' + encodeURIComponent(res.usuario.foto_perfil);
                img.style.display = 'block';
                icon.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', cargarPerfil);
    </script>
</body>
</html>
