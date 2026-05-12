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
            <i class="fa-regular fa-note-sticky"></i>
            <i class="fa-solid fa-triangle-exclamation">
                <span class="notification-dot"></span>
            </i>
            <i class="fa-regular fa-bell"></i>
            <i class="fa-regular fa-bookmark"></i>
        </div>

        <div class="user-profile-circle">
            <i class="fa-solid fa-user"></i>
        </div>
    </div>
</header>
