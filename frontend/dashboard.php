<?php
    require __DIR__ . '/../backend/includes/funciones.php';
    $consulta = obtener_tabla();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nexus Solutions - BIN</title>

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body>

    <header id="main-header">

        <div class="logo-area">

            <div class="logo-box">
                <img src="BIN.png" alt="Nexus Solutions Logo">
            </div>

            <div>
                <h1>Portal BIN</h1>
                <p>Bienvenido</p>
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


    <!-- SIDEBAR -->

    <aside id="sidebar">

        <div class="nav-section">GENERAL</div>

        <div class="menu-item">
            <a href="index.html" class="menu-btn active">
                <span><i class="fa-solid fa-gauge-high"></i> Página Principal </span>
            </a>
        </div>

        <div class="menu-item">
            <a href="dashboard.php" class="menu-btn active">
                <span><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
            </a>
        </div>

        <div class="nav-section">DATOS</div>

        <div class="menu-item">

            <input type="checkbox" id="inventario" class="menu-check">

            <label for="inventario" class="menu-btn">
                <span><i class="fa-solid fa-box"></i> Inventario</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>

            <ul class="submenu">
                <li><a href="#">Contenedores</a></li>
                <li><a href="#">Camiones</a></li>
                <li><a href="#">Mapa Interactivo</a></li>
            </ul>

        </div>

        <!-- ADMINISTRACIÓN -->

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


        <div class="nav-section">CONFIGURACIÓN</div>

        <div class="menu-item">
            <a class="menu-btn">
                <span><i class="fa-solid fa-gear"></i> Ajustes del Sistema</span>
            </a>
        </div>

    </aside>


    <!-- CONTENIDO -->

    <main id="content">

        <section class="stats-grid">

            <div class="card">

                <div>

                    <div class="card-label">Registros del mes</div>
                    <div class="card-value">1.5 Ton de basura recolectada</div>

                    <div class="card-note positive">
                        ↑ 12% mayor que el mes pasado
                    </div>

                </div>

                <div class="card-icon" style="background:#e2efda;color:#217346;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

            </div>


            <div class="card">

                <div>

                    <div class="card-label">Alerta de contenedores</div>
                    <div class="card-value">0</div>

                    <div class="card-note">
                        Ninguna anomalía detectada
                    </div>

                </div>

                <div class="card-icon" style="background:#fff5b1;color:#b08800;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

            </div>


            <div class="card">

                <div>

                    <div class="card-label">Camiones activos</div>
                    <div class="card-value">1</div>

                    <div class="card-note">
                        En ruta
                    </div>

                </div>

                <div class="card-icon" style="background:#ddf4ff;color:#0969da;">
                    <i class="fa-solid fa-car"></i>
                </div>

            </div>

        </section>


        <section class="analytics-row">

            <div class="panel-box">

                <div class="panel-header">

                    <h3>Tendencia de recolección</h3>

                    <select class="period-filter">
                        <option>Semanal</option>
                        <option>Último mes</option>
                        <option>Trimestre</option>
                        <option>Año</option>
                    </select>

                </div>

                <div class="chart-visual">

                    <div class="bar" style="height:60%">
                        <span class="month-label">Lunes</span>
                    </div>

                    <div class="bar" style="height:40%">
                        <span class="month-label">Martes</span>
                    </div>

                    <div class="bar" style="height:75%">
                        <span class="month-label">Miercoles</span>
                    </div>

                    <div class="bar" style="height:55%">
                        <span class="month-label">Jueves</span>
                    </div>

                    <div class="bar" style="height:90%">
                        <span class="month-label">Viernes</span>
                    </div>

                    <div class="bar" style="height:45%">
                        <span class="month-label">Sabado</span>
                    </div>

                    <div class="bar" style="height:15%">
                        <span class="month-label">Domingo</span>
                    </div>

                </div>

            </div>


            <div class="panel-box">

                <div class="panel-header">
                    <h3>Servicios frecuentes y costo promedio</h3>
                </div>

                <div class="servicios-list">

                    <div class="servicio-item">
                        <span>Servicio de recolección</span>
                        <span>$850</span>
                    </div>

                    <div class="servicio-item">
                        <span>Recalibración</span>
                        <span>$600</span>
                    </div>

                    <div class="servicio-item">
                        <span>Diagnóstico general del equipo</span>
                        <span>$750</span>
                    </div>

                    <div class="servicio-item">
                        <span>Reparación del equipo</span>
                        <span>$1650</span>
                    </div>

                    <div class="servicio-item">
                        <span>Gestión final de residuos</span>
                        <span>$1400</span>
                    </div>

                </div>

            </div>

        </section>


        <section class="table-box">

            <table>
                <thead>
                    <tr>
                        <th>Ubicación</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($contenedor = mysqli_fetch_assoc($consulta)): ?>
                        <tr>
                            <td><?php echo $contenedor['ubicacion']; ?></td>
                            <td><?php echo $contenedor['latitud']; ?></td>
                            <td><?php echo $contenedor['longitud']; ?></td>
                            <td><?php echo $contenedor['capacidad']; ?></td>
                            <td>
                                <span class="status <?php echo 'st-' . strtolower($contenedor['estado']); ?>">
                                    <?php echo $contenedor['estado']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </section>

    </main>

</body>

</html>
