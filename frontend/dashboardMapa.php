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
        <div id="ui-controls" class="ui-container">
    <div id="selector-card" class="header-card" onclick="toggleAreaList()">
        <span id="zone-name">📍 Seleccionar Área</span>
        <i class="fa-solid fa-chevron-down" id="arrow-icon"></i>
    </div>

    <div id="area-list" class="area-list collapsed">
        <div class="area-item" onclick="selectArea('sistemas')">Área de Sistemas</div>
        <div class="area-item" onclick="selectArea('quimica')">Área de Química</div>
    </div>

    <div id="info-card" class="info-card hidden">
        <div class="info-header">
            <h4 id="card-title"></h4>
            <button class="btn-back" onclick="resetUI()">⬅ Volver</button>
        </div>
        <hr id="card-hr" class="card-hr">
        <div class="info-body">
            <p><strong>📦 Contenedores:</strong> <span id="card-cont"></span></p>
            <p><strong>📋 Registros:</strong> <span id="card-reg"></span></p>
            <p><strong>⚠️ Prioridad:</strong> <span id="card-prior" class="priority-badge"></span></p>
        </div>
    </div>
</div>

      <div id="map">


        <script>
    var map = L.map('map').setView([25.5334, -103.4358], 18);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // --- PALETA DE COLORES ---
    const colors = {
        sistemas: '#34495e', // Azul oscuro grisáceo
        quimica: '#27ae60'   // Verde esmeralda
    };

    const customOptions = { 'className': 'custom-popup' };

    // --- FUNCIÓN PARA CREAR ICONOS DINÁMICOS ---
    // Función actualizada para usar FontAwesome
function createTrashIcon(color) {
    return L.divIcon({
        className: 'custom-div-icon', // Clase limpia de Leaflet
        html: `
            <div class="trash-icon-container" style="border-color: ${color}; color: ${color};">
                <i class="fa-solid fa-trash-can"></i>
            </div>
        `,
        iconSize: [34, 34],
        iconAnchor: [17, 17] // Centrado exacto
    });
}

// --- Aplicación en tus áreas ---

// Marcadores Sistemas (Azul Grisáceo)
var iconSistemas = createTrashIcon(colors.sistemas);
L.marker([25.533254, -103.436151], {icon: iconSistemas}).addTo(map)
    .bindPopup("<b>Contenedor de Basura #1</b>", customOptions);

L.marker([25.532843052441912, -103.43611992777953], {icon: iconSistemas}).addTo(map)
    .bindPopup("<b>Contenedor de Basura #2</b>", customOptions);

L.marker([25.53295438435178, -103.43585170688664], {icon: iconSistemas}).addTo(map)
    .bindPopup("<b>Contenedor de Basura #3</b>", customOptions);

L.marker([25.533060875647113, -103.4361091989438], {icon: iconSistemas}).addTo(map)
    .bindPopup("<b>Contenedor de Basura #4</b>", customOptions);

    // --- ÁREA DE SISTEMAS ---
    var polygonS = L.polygon([
        [25.533525597142507, -103.43583022883226],
        [25.53257201631325, -103.43583022883226],
        [25.53257201631325, -103.43617355157481],
        [25.533244848235757, -103.43623792458911],
        [25.533244848235757, -103.43652760315341],
        [25.533525597142507, -103.43652760315341]
    ]).addTo(map);

    polygonS.setStyle({ color: colors.sistemas, fillColor: colors.sistemas, fillOpacity: 0.3, dashArray: '5, 10' });
    polygonS.bindPopup("<b>Área de Sistemas 💻</b><br>Contenedores: 4", customOptions);

    // --- ÁREA DE QUÍMICA ---
    var polygonQ = L.polygon([
        [25.53358168, -103.4365923], [25.53392837, -103.4365890], [25.53417858, -103.4360845],
        [25.53431399, -103.4360876], [25.53431399, -103.4357003], [25.53417256, -103.4356970],
        [25.53413161, -103.4356970], [25.53413161, -103.4352519], [25.53435042, -103.4349653],
        [25.53435042, -103.4345123], [25.53420736, -103.4345123], [25.53373844, -103.4347080],
        [25.53374832, -103.4353462], [25.53388083, -103.4354353], [25.53388083, -103.4359624],
        [25.53394708, -103.4359624], [25.53394344, -103.4361046], [25.53358168, -103.4361112]
    ]).addTo(map);

    polygonQ.setStyle({ color: colors.quimica, fillColor: colors.quimica, fillOpacity: 0.3, dashArray: '5, 10' });
    polygonQ.bindPopup("<b>Área de Química 🔬</b><br>Contenedores: 5", customOptions);

   // Marcadores Química (Verde)
var iconQuimica = createTrashIcon(colors.quimica);
L.marker([25.534036270985673, -103.43542918420991], {icon: iconQuimica}).addTo(map)
.bindPopup("<b>Contenedor de Basura #1</b>", customOptions);

var iconQuimica = createTrashIcon(colors.quimica);
L.marker([25.53376014359723, -103.43626221962181], {icon: iconQuimica}).addTo(map)
.bindPopup("<b>Contenedor de Basura #1</b>", customOptions);

var iconQuimica = createTrashIcon(colors.quimica);
L.marker([25.5340552825665, -103.43594631675464], {icon: iconQuimica}).addTo(map)
.bindPopup("<b>Contenedor de Basura #1</b>", customOptions);

var iconQuimica = createTrashIcon(colors.quimica);
L.marker([25.53389735180388, -103.43488324120955], {icon: iconQuimica}).addTo(map)
.bindPopup("<b>Contenedor de Basura #1</b>", customOptions);

var iconQuimica = createTrashIcon(colors.quimica);
L.marker([25.533882815132063, -103.4352017462659], {icon: iconQuimica}).addTo(map)
.bindPopup("<b>Contenedor de Basura #1</b>", customOptions);

</script>

      </div>

    </main>

</body>

</html>
