<header id="main-header">
    <div class="logo-area">
        <div class="logo-box">
            <img src="assets/img/BIN.png" alt="Nexus Solutions Logo">
        </div>
        <div>
            <h1>PORTAL BIN</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></p>
        </div>
    </div>

    <div class="header-right">
        <div class="tool-icons">
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
            <a href="dashboard.php#seccion-reporte-ia" title="Generar Reporte" style="color: #6c3fc5;"><i class="fa-regular fa-note-sticky"></i></a>
            <?php endif; ?>
            <a href="dashboard.php#tabla-contenedores" title="Alertas Críticas" style="color: #cf222e;">
                <i class="fa-solid fa-triangle-exclamation">
                    <span class="notification-dot" style="display: none;"></span>
                </i>
            </a>
            <i class="fa-regular fa-bell" title="Notificaciones" onclick="alert('No hay notificaciones nuevas')"></i>
            <i class="fa-regular fa-bookmark" title="Guardados"></i>

        </div>

        <div class="user-profile-circle" onclick="toggleUserMenu(event)">
        <?php 
            $nombre_sesion = $_SESSION['nombre'] ?? 'U';
            $inicial = strtoupper(mb_substr($nombre_sesion, 0, 1));
        ?>
        <?php if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])): ?>
            <img src="api/image_proxy.php?path=<?php echo urlencode($_SESSION['foto_perfil']); ?>"
                 class="user-profile-img"
                 alt="Foto de perfil"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span class="user-avatar-initials" style="display:none;"><?php echo $inicial; ?></span>
        <?php else: ?>
            <span class="user-avatar-initials"><?php echo $inicial; ?></span>
        <?php endif; ?>

            
            <div class="user-dropdown" id="userDropdown">
                <a href="perfil.php"><i class="fa-solid fa-circle-user"></i> Ver Perfil</a>
                <a href="ajustes.php"><i class="fa-solid fa-sliders"></i> Ajustes</a>
                <hr>
                <a href="logout.php" style="color: #e74c3c;"><i class="fa-solid fa-power-off"></i> Cerrar Sesión</a>
            </div>
        </div>

    </div>
</header>

<script>
function toggleUserMenu(event) {
    event.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('show');
}

// Cerrar al hacer clic fuera
window.onclick = function(event) {
    if (!event.target.matches('.user-profile-circle') && !event.target.matches('.fa-user')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>
