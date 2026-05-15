<?php
require_once 'config.php';
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración GPS - Zonas y Contenedores</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; }
        .btn-save { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-save:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <?php include_once 'includes/header.php'; ?>
    <?php include_once 'includes/sidebar.php'; ?>

    <main id="content">
        <header class="section-header">
            <h2>Panel Administrativo</h2>
            <p>Gestiona zonas de recolección y despliegue de contenedores.</p>
        </header>

        <div class="admin-grid">
            <!-- Gestión de Zonas -->
            <section class="panel-box">
                <div class="panel-header"><h3><i class="fa-solid fa-map-location-dot"></i> Nueva Zona</h3></div>
                <form id="form-zona" style="padding: 20px;">
                    <div class="form-group">
                        <label>Nombre de la Zona</label>
                        <input type="text" id="z-nombre" placeholder="Ej. Campus Norte">
                    </div>
                    <div class="form-group">
                        <label>Prioridad</label>
                        <select id="z-prioridad">
                            <option value="1">Baja</option>
                            <option value="2">Media</option>
                            <option value="3">Alta</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color (HEX)</label>
                        <input type="color" id="z-color" value="#3b82f6">
                    </div>
                    <div class="form-group">
                        <label>Polígono (JSON Coords)</label>
                        <textarea id="z-coords" rows="4" placeholder="[[lat, lng], [lat, lng]...]"></textarea>
                    </div>
                    <button type="button" class="btn-save" onclick="guardarZona()">Crear Zona</button>
                </form>
            </section>

            <!-- Gestión de Contenedores -->
            <section class="panel-box">
                <div class="panel-header"><h3><i class="fa-solid fa-box"></i> Nuevo Contenedor</h3></div>
                <form id="form-contenedor" style="padding: 20px;">
                    <div class="form-group">
                        <label>Ubicación / Nombre</label>
                        <input type="text" id="c-ubicacion" placeholder="Ej. Puerta Principal">
                    </div>
                    <div class="form-group">
                        <label>Zona</label>
                        <select id="c-id-zona">
                            <!-- Se llena dinámicamente -->
                        </select>
                    </div>
                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1;">
                            <label>Latitud</label>
                            <input type="number" step="any" id="c-lat">
                        </div>
                        <div style="flex:1;">
                            <label>Longitud</label>
                            <input type="number" step="any" id="c-lng">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select id="c-real">
                            <option value="0">Simulado</option>
                            <option value="1">Físico (Real)</option>
                        </select>
                    </div>
                    <button type="button" class="btn-save" onclick="guardarContenedor()">Guardar Contenedor</button>
                </form>
            </section>
        </div>
    </main>

    <script src="js/api.js"></script>
    <script>
        // Cargar zonas en el select de contenedores
        async function cargarZonasAdmin() {
            const res = await API.obtenerZonas();
            if (res.status === 'ok') {
                const select = document.getElementById('c-id-zona');
                select.innerHTML = res.data.map(z => `<option value="${z.id_zona}">${z.nombre}</option>`).join('');
            }
        }

        async function guardarZona() {
            // Aquí iría la llamada al API de guardado (que crearé a continuación)
            alert("Zona guardada exitosamente (Simulado)");
        }

        async function guardarContenedor() {
            // Aquí iría la llamada al API de guardado
            alert("Contenedor guardado exitosamente (Simulado)");
        }

        document.addEventListener('DOMContentLoaded', cargarZonasAdmin);
    </script>
</body>
</html>
