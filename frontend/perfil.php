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
    <title>Perfil | BIN</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-page { max-width: 760px; margin: 35px auto; }
        .profile-card {
            background: var(--bg-panel);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 28px;
            text-align: center;
        }
        .profile-photo {
            width: 170px;
            height: 170px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--primary);
            background: var(--bg-input);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 62px;
            color: var(--text-sub);
        }
        .profile-photo img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { margin: 0 0 8px; color: var(--text-main); font-size: 30px; }
        .profile-role {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 16px;
            background: var(--accent-soft);
            color: var(--primary);
            font-weight: 700;
        }
    </style>
</head>
<body class="<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-theme' : '' ?>">
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
