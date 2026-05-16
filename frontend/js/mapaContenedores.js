/*
 * mapaContenedores.js - Lógica de Mapa Dinámica (DB como fuente de verdad)
 */

// 1. Inicialización del Mapa
var map = L.map('map').setView([25.5334, -103.4358], 18);
var markerLayer = L.layerGroup().addTo(map);
var zoneLayer = L.layerGroup().addTo(map);
var truckMarker = null;

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// 2. Iconos
function createTrashIcon(color, isReal = false) {
    const shadow = isReal ? "filter: drop-shadow(0 0 5px #2e7d32);" : "";
    return L.divIcon({
        className: 'custom-div-icon',
        html: `<div class="trash-icon-container" style="border-color: ${color}; color: ${color}; ${shadow}"><i class="fa-solid fa-trash-can"></i></div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });
}

const MapService = {
    async init() {
        await this.loadZones();
        await this.loadMarkers();
    },

    async loadZones() {
        try {
            const res = await API.obtenerZonas();
            if (res.status === 'ok') {
                zoneLayer.clearLayers();
                const listContainer = document.getElementById('area-list');
                listContainer.innerHTML = '';

                res.data.forEach(zona => {
                    if (zona.coordenadas_poligono) {
                        L.polygon(zona.coordenadas_poligono, {
                            color: zona.color_hex,
                            fillColor: zona.color_hex,
                            fillOpacity: 0.15,
                            dashArray: '5, 10'
                        }).addTo(zoneLayer);
                    }
                    
                    // Actualizar lista de UI
                    const div = document.createElement('div');
                    div.className = 'area-item';
                    div.innerText = zona.nombre;
                    div.onclick = () => this.selectZone(zona);
                    listContainer.appendChild(div);
                });
            }
        } catch (e) { console.error("Error cargando zonas:", e); }
    },

    async loadMarkers() {
        try {
            const res = await API.obtenerContenedores();
            if (res.status === 'ok') {
                this.renderMarkers(res.data);
            }
        } catch (e) { console.error("Error cargando marcadores:", e); }
    },

    renderMarkers(contenedores) {
        markerLayer.clearLayers();
        contenedores.forEach(c => {
            if (c.latitud && c.longitud) {
                let iconColor = "#27ae60"; 
                const prio = (c.prioridad || '').toLowerCase();
                if (prio === 'alta') iconColor = "#e74c3c";
                else if (prio === 'media') iconColor = "#f39c12";

                const marker = L.marker([parseFloat(c.latitud), parseFloat(c.longitud)], {
                    icon: createTrashIcon(iconColor, c.es_real == 1)
                });

                const badgeFisico = c.es_real == 1 ? '<span style="background: #2e7d32; color: white; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; margin-left: 5px;">FISICO</span>' : '';

                marker.bindPopup(`
                    <div style="font-family: 'Poppins', sans-serif; min-width: 180px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                            <b style="color: #2c3e50;">${c.ubicacion}</b>
                            ${badgeFisico}
                        </div>
                        <div style="margin-top: 4px;">
                            <span style="background: ${iconColor}; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;">
                                ${(c.prioridad || 'NORMAL').toUpperCase()}
                            </span>
                            <span style="font-size: 10px; color: #666; margin-left: 5px;">${c.zona_nombre || ''}</span>
                        </div>
                        <hr style="margin: 8px 0; border: 0; border-top: 1px solid #eee;">
                        <div style="font-size: 12px; line-height: 1.6;">
                            🌡️ <b>Temp:</b> ${c.temperatura || '0'}°C<br>
                            💧 <b>Hum:</b> ${c.humedad || '0'}%<br>
                            ⚖️ <b>Peso:</b> ${parseFloat(c.peso || 0).toFixed(1)} kg<br>
                            📅 <b>Lectura:</b> <span style="color: #7f8c8d; font-size: 10px;">${c.fecha_lectura || 'N/A'}</span>
                        </div>
                        <button onclick="abrirModalReporte(${c.id_contenedor}, '${c.ubicacion}')" style="width:100%; margin-top:10px; background:var(--primary); color:white; border:none; border-radius:4px; padding:6px; font-size:11px; cursor:pointer; font-weight:600;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Reportar Problema
                        </button>
                    </div>

                    </div>
                `);
                markerLayer.addLayer(marker);
            }
        });
    },

    selectZone(zona) {
        document.getElementById('area-list').classList.add('collapsed');
        document.getElementById('arrow-icon').classList.remove('rotated');
        document.getElementById('zone-name').innerText = "📍 " + zona.nombre;
        document.getElementById('card-title').innerText = zona.nombre;
        
        // Filtrar contenedores de esta zona para el conteo dinámico
        // Nota: datosContenedores viene de dashboardMapa.php
        const botesEnZona = (typeof datosContenedores !== 'undefined') 
            ? datosContenedores.filter(c => c.id_zona == zona.id_zona)
            : [];
        
        const count = botesEnZona.length;
        document.getElementById('card-cont').innerText = count;
        
        // Determinar prioridad máxima en la zona
        let priorStr = "Baja";
        let priorClass = "st-vacio"; // Azul/Gris
        
        if (botesEnZona.some(b => (b.prioridad || '').toLowerCase() === 'alta')) {
            priorStr = "Alta";
            priorClass = "st-lleno"; // Rojo
        } else if (botesEnZona.some(b => (b.prioridad || '').toLowerCase() === 'media')) {
            priorStr = "Media";
            priorClass = "st-medio"; // Naranja
        }

        const priorEl = document.getElementById('card-prior');
        priorEl.innerText = priorStr;
        priorEl.className = "priority-badge " + priorClass;
        
        document.getElementById('card-reg').innerText = "Actualizado recientemente";
        document.getElementById('card-hr').style.borderColor = zona.color_hex;
        
        document.getElementById('info-card').classList.remove('hidden');
        
        // Si hay coordenadas de polígono, ir al centro del polígono
        if (zona.coordenadas_poligono && zona.coordenadas_poligono.length > 0) {
            const bounds = L.polygon(zona.coordenadas_poligono).getBounds();
            map.flyToBounds(bounds, { padding: [50, 50] });
        }
    },


    async simulateTruckMovement(coords) {
        if (truckMarker) map.removeLayer(truckMarker);
        
        const truckIcon = L.divIcon({
            className: 'truck-icon',
            html: '<div style="background: #2c3e50; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 10px rgba(0,0,0,0.5); border: 2px solid white;"><i class="fa-solid fa-truck"></i></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        truckMarker = L.marker(coords[0], { icon: truckIcon }).addTo(map);
        
        let i = 0;
        const move = setInterval(() => {
            if (i >= coords.length - 1) {
                clearInterval(move);
                return;
            }
            truckMarker.setLatLng(coords[i]);
            i++;
        }, 100); // Movimiento rápido para la demo
    }
};

// 5. Funciones de UI Selector
function toggleAreaList() {
    const list = document.getElementById('area-list');
    const arrow = document.getElementById('arrow-icon');
    list.classList.toggle('collapsed');
    arrow.classList.toggle('rotated');
    document.getElementById('info-card').classList.add('hidden');
}

function resetUI() {
    document.getElementById('info-card').classList.add('hidden');
    document.getElementById('zone-name').innerText = "📍 Seleccionar Área";
    map.flyTo([25.5334, -103.4358], 18);
}

// 6. Carga inicial
document.addEventListener('DOMContentLoaded', () => {
    MapService.init();
});