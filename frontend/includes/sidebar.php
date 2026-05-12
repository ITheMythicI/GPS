<aside id="sidebar">
    <div class="nav-section">GENERAL</div>

    <div class="menu-item">
        <a href="index.html" class="menu-btn <?php echo basename($_SERVER['PHP_SELF']) == 'index.html' ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-gauge-high"></i> Página Principal </span>
        </a>
    </div>

    <div class="menu-item">
        <a href="dashboard.php" class="menu-btn <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
        </a>
    </div>

    <div class="nav-section">DATOS</div>

    <div class="menu-item">
        <input type="checkbox" id="inventario" class="menu-check">
        <label for="inventario" class="menu-btn <?php echo basename($_SERVER['PHP_SELF']) == 'dashboardMapa.php' ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-box"></i> Inventario</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="#">Contenedores</a></li>
            <li><a href="#">Camiones</a></li>
            <li><a href="dashboardMapa.php">Mapa Interactivo</a></li>
        </ul>
    </div>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
    <div class="nav-section">ADMINISTRACIÓN</div>

    <div class="menu-item">
        <input type="checkbox" id="m-bit" class="menu-check">
        <label for="m-bit" class="menu-btn">
            <span><i class="fa-solid fa-book"></i> Registros</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="#">Registro de Actividad</a></li>
        </ul>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="m-fin" class="menu-check">
        <label for="m-fin" class="menu-btn">
            <span><i class="fa-solid fa-landmark"></i> Finanzas</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="#">Flujo de Caja</a></li>
            <li><a href="#">Facturacion</a></li>
        </ul>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="m-gas" class="menu-check">
        <label for="m-gas" class="menu-btn">
            <span><i class="fa-solid fa-file-invoice-dollar"></i> Gastos</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="#">Socios</a></li>
        </ul>
    </div>
    <?php endif; ?>

    <div class="nav-section">CONFIGURACIÓN</div>

    <div class="menu-item">
        <a class="menu-btn">
            <span><i class="fa-solid fa-gear"></i> Ajustes del Sistema</span>
        </a>
    </div>

    <div class="menu-item">
        <a href="logout.php" class="menu-btn" style="color: #e74c3c;">
            <span><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</span>
        </a>
    </div>
</aside>
