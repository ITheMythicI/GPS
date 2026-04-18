<?php
    require __DIR__ . '/../backend/includes/funciones.php';
    $consulta = obtener_tabla();

    //Arreglo de datos contenedores
    $datos_contenedores = [];
    while ($fila = mysqli_fetch_assoc($consulta)){
        $datos_contenedores[] = $fila;
    }

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

     <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>

     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

</head>

<body>

    <header id="main-header">

        <div class="logo-area">

            <div class="logo-box">
                <img src="BIN.png" alt="Nexus Solutions Logo">
            </div>

            <div>
                <h1>PORTAL BIN</h1>
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

            <label for="inventario" class="menu-btn active">
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

        <div id="map">

        </div>

    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="                
            crossorigin="">
    </script>

    <script>
        const datosContenedores = <?php echo json_encode($datos_contenedores); ?>;
    </script>

    <script src="../backend/js/mapaContenedores.js"></script>

</body>

</html>