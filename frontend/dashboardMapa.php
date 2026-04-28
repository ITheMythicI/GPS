<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$url = "http://10.0.2.8/obtenerContenedores.php";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);

if ($response === false) {
    die("cURL error: " . curl_error($ch));
}

curl_close($ch);

$data = json_decode($response, true);

if (!$data || $data['status'] !== 'ok') {
    die("Error en respuesta del backend");
}

$datos_contenedores = $data['data'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuildNess - Mapa Interactivo</title>

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        /* Contenedor relativo para que la UI flote sobre el mapa */
        #map-wrapper {
            position: relative;
            width: 100%;
            height: 650px; /* Ajusta según tu diseño */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        #map {
            height: 100% !important;
            width: 100%;
            z-index: 1;
        }
    </style>
</head>

<body>
    <header id="main-header">
        <div class="logo-area">
            <div class="logo-box"><img src="BIN.png" alt="Logo"></div>
            <div>
                <h1>PORTAL BIN</h1>
                <p>BuildNess Management</p>
            </div>
        </div>
        <div class="header-right">
            <div class="tool-icons">
                <i class="fa-regular fa-bell"></i>
                <i class="fa-solid fa-triangle-exclamation"><span class="notification-dot"></span></i>
            </div>
            <div class="user-profile-circle"><i class="fa-solid fa-user"></i></div>
        </div>
    </header>

    <aside id="sidebar">
        <div class="nav-section">GENERAL</div>
        <div class="menu-item">
            <a href="dashboard.php" class="menu-btn"><span><i class="fa-solid fa-gauge-high"></i> Dashboard</span></a>
        </div>
        <div class="nav-section">DATOS</div>
        <div class="menu-item">
            <label class="menu-btn active"><span><i class="fa-solid fa-map-location-dot"></i> Mapa Interactivo</span></label>
        </div>
        <div class="menu-item">
            <a href="index.html" class="menu-btn"><span><i class="fa-solid fa-house"></i> Salir</span></a>
        </div>
    </aside>

    <main id="content">
        <div id="map-wrapper">
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

            <div id="map"></div>
        </div>
    </main>

    

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Pasar datos de PHP a una variable Global de JS
       const datosContenedores = <?php echo json_encode($datos_contenedores); ?>;
    </script>
    <script src="js/mapaContenedores.js"></script>
</body>
</html>