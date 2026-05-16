<aside id="sidebar">
    <div class="nav-section">GENERAL</div>

    <div class="menu-item">
        <a href="dashboard.php" class="menu-btn <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
        </a>
    </div>

    <div class="nav-section">DATOS</div>

    <div class="menu-item">
        <input type="checkbox" id="inventario" class="menu-check" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboardMapa.php') ? 'checked' : ''; ?>>
        <label for="inventario" class="menu-btn">
            <span><i class="fa-solid fa-box"></i> Inventario</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="dashboard.php#tabla-contenedores">Contenedores</a></li>
            <li><a href="#">Camiones</a></li>
            <li><a href="dashboardMapa.php" style="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboardMapa.php' ? 'color: var(--primary); font-weight: bold;' : ''; ?>">Mapa Interactivo</a></li>
        </ul>
    </div>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
    <div class="nav-section">ADMINISTRACIÓN</div>

    <div class="menu-item">
        <a href="admin.php" class="menu-btn <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-screwdriver-wrench"></i> Gestión de Zonas</span>
        </a>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="check-registros" class="menu-check">
        <label for="check-registros" class="menu-btn">
            <span><i class="fa-solid fa-book"></i> Registros</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="registroActividad.php">Registro de Actividad</a></li>
            <li><a href="incidencias.php">Incidencias</a></li>
        </ul>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="m-fin" class="menu-check">
        <label for="m-fin" class="menu-btn">
            <span><i class="fa-solid fa-landmark"></i> Finanzas (Proximamente...)</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="#">Flujo de Caja</a></li>
            <li><a href="#">Facturacion</a></li>
        </ul>
    </div>
    <?php endif; ?>

    <div class="nav-section">CONFIGURACIÓN</div>

    <div class="menu-item">
        <a href="ajustes.php" class="menu-btn">
            <span><i class="fa-solid fa-gear"></i> Ajustes del Sistema</span>
        </a>
    </div>


    <div class="menu-item">
        <a href="logout.php" class="menu-btn" style="color: #e74c3c;">
            <span><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</span>
        </a>
    </div>
</aside>
