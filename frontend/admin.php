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

        <div class="admin-grid" style="margin-top: 30px;">
            <!-- Lista de Zonas -->
            <section class="panel-box">
                <div class="panel-header"><h3><i class="fa-solid fa-list"></i> Zonas Existentes</h3></div>
                <div id="lista-zonas" style="padding: 15px;">
                    <!-- Se llena dinámicamente -->
                </div>
            </section>

            <!-- Lista de Contenedores -->
            <section class="panel-box">
                <div class="panel-header"><h3><i class="fa-solid fa-trash-can"></i> Eliminar Contenedores</h3></div>
                <div id="lista-contenedores" style="padding: 15px;">
                    <!-- Se llena dinámicamente -->
                </div>
            </section>
        </div>

    </main>



    <script src="js/api.js"></script>
    <script>
        // Cargar zonas y contenedores en las listas
        async function cargarDatosAdmin() {
            // Cargar Zonas
            const resZonas = await API.obtenerZonas();
            if (resZonas.status === 'ok') {
                const select = document.getElementById('c-id-zona');
                const listaZonas = document.getElementById('lista-zonas');
                
                select.innerHTML = resZonas.data.map(z => `<option value="${z.id_zona}">${z.nombre}</option>`).join('');
                
                listaZonas.innerHTML = resZonas.data.map(z => `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #eee;">
                        <span><i class="fa-solid fa-circle" style="color:${z.color_hex}; font-size:10px;"></i> ${z.nombre}</span>
                        <button onclick="eliminarZona(${z.id_zona}, '${z.nombre}')" style="background:none; border:none; color:#e74c3c; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                    </div>
                `).join('');
            }

            // Cargar Contenedores
            const resCont = await API.obtenerContenedores();
            if (resCont.status === 'ok') {
                const listaCont = document.getElementById('lista-contenedores');
                listaCont.innerHTML = resCont.data.map(c => `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #eee;">
                        <div>
                            <strong>${c.ubicacion}</strong><br>
                            <small style="color:#666;">Zona: ${c.zona_nombre || 'Sin zona'}</small>
                        </div>
                        <button onclick="eliminarContenedor(${c.id_contenedor}, '${c.ubicacion}')" style="background:none; border:none; color:#e74c3c; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                    </div>
                `).join('');
           }

        async function eliminarZona(id, nombre) {
(id, nombre) {
            if (!confirm(`¿Estás seguro de eliminar la zona "${nombre}"? Los contenedores se quedarán sin zona asignada.`)) return;
            const res = await API.borrarZona(id);
            if (res.status === 'ok') {
                alert("Zona eliminada");
                cargarDatosAdmin();
            } else {
                alert("Error: " + res.message);
            }
        }

        async function eliminarContenedor(id, nombre) {
            if (!confirm(`¿Estás seguro de eliminar el contenedor "${nombre}"?`)) return;
            const res = await API.borrarContenedor(id);
            if (res.status === 'ok') {
                alert("Contenedor eliminado");
                cargarDatosAdmin();
            } else {
                alert("Error: " + res.message);
            }
        }

        async function guardarZona() {
            const datos = {
                nombre: document.getElementById('z-nombre').value,
                prioridad: document.getElementById('z-prioridad').value,
                color: document.getElementById('z-color').value,
                coords: document.getElementById('z-coords').value
            };
            
            if(!datos.nombre) return alert("Nombre requerido");

            const res = await API.guardarZona(datos);
            if (res.status === 'ok') {
                alert("Zona creada exitosamente");
                cargarDatosAdmin();
                document.getElementById('form-zona').reset();
            } else {
                alert("Error: " + res.message);
            }
        }

        async function guardarContenedor() {
            const datos = {
                ubicacion: document.getElementById('c-ubicacion').value,
                id_zona: document.getElementById('c-id-zona').value,
                lat: document.getElementById('c-lat').value,
                lng: document.getElementById('c-lng').value,
                es_real: document.getElementById('c-real').value
            };

            if(!datos.ubicacion) return alert("Ubicación requerida");

            const res = await API.guardarContenedor(datos);
            if (res.status === 'ok') {
                alert("Contenedor creado exitosamente");
                cargarDatosAdmin();
                document.getElementById('form-contenedor').reset();
            } else {
                alert("Error: " + res.message);
            }
        }

        document.addEventListener('DOMContentLoaded', cargarDatosAdmin);

    </script>
</body>
</html>
