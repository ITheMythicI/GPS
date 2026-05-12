/*
 * mapaContenedores.js - Lógica de Mapa con soporte para actualización en vivo
 */

var map = L.map('map').setView([25.5334, -103.4358], 18);
var markerLayer = L.layerGroup().addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

function createTrashIcon(color) {
    return L.divIcon({
        className: 'custom-div-icon',
        html: `<div class="trash-icon-container" style="border-color: ${color}; color: ${color};"><i class="fa-solid fa-trash-can"></i></div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });
}

const MapService = {
    renderMarkers(contenedores) {
        markerLayer.clearLayers();
        if (!Array.isArray(contenedores)) return;

        contenedores.forEach(c => {
            if (c.latitud && c.longitud) {
                let iconColor = "#27ae60"; 
                const prio = (c.prioridad || '').toLowerCase();
                if (prio === 'alta') iconColor = "#e74c3c";
                else if (prio === 'media') iconColor = "#f39c12";

                const marker = L.marker([parseFloat(c.latitud), parseFloat(c.longitud)], {
                    icon: createTrashIcon(iconColor)
                });

                marker.bindPopup(`
                    <div style="font-family: 'Poppins', sans-serif; min-width: 150px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <b style="color: #2c3e50;">Contenedor #${c.id_contenedor}</b>
                            <span style="background: ${iconColor}; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;">
                                ${(c.prioridad || 'NORMAL').toUpperCase()}
                            </span>
                        </div>
                        <hr style="margin: 8px 0; border: 0; border-top: 1px solid #eee;">
                        <div style="font-size: 12px; line-height: 1.6;">
                            📍 <b>Ubicación:</b> ${c.ubicacion}<br>
                            🌡️ <b>Temp:</b> ${c.temperatura || '0'}°C<br>
                            💧 <b>Hum:</b> ${c.humedad || '0'}%<br>
                            ⚖️ <b>Peso:</b> ${c.peso || '0'} kg<br>
                            📅 <b>Lectura:</b> <span style="color: #7f8c8d; font-size: 10px;">${c.fecha_lectura || 'N/A'}</span>
                        </div>
                    </div>
                `);
                markerLayer.addLayer(marker);
            }
        });
    }
};

// Carga inicial
document.addEventListener('DOMContentLoaded', () => {
    if (typeof datosContenedores !== 'undefined') {
        MapService.renderMarkers(datosContenedores);
    }
});