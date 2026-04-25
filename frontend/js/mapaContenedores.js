/**
 * LÓGICA DE MAPA Y UI - BUILDNESS
 */

// 1. Inicialización del Mapa (Centrado en Tec Laguna)
var map = L.map('map').setView([25.5334, -103.4358], 18);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// 2. Configuración Estética de Áreas
const areaSettings = {
    sistemas: {
        coords: [25.5330, -103.4361],
        title: "Área de Sistemas",
        prioridad: "Alta",
        colorBadge: "#e74c3c",
        colorArea: "#34495e",
        polygon: [
            [25.533525597142507, -103.43583022883226], [25.53257201631325, -103.43583022883226],
            [25.53257201631325, -103.43617355157481], [25.533244848235757, -103.43623792458911],
            [25.533244848235757, -103.43652760315341], [25.533525597142507, -103.43652760315341]
        ]
    },
    quimica: {
        coords: [25.5340, -103.4355],
        title: "Área de Química",
        prioridad: "Media",
        colorBadge: "#f39c12",
        colorArea: "#27ae60",
        polygon: [
            [25.53358168, -103.4361112], [25.53358168, -103.4365923], [25.53392837, -103.4365890], [25.53417858, -103.4360845],
            [25.53431399, -103.4360876], [25.53431399, -103.4357003], [25.53417256, -103.4356970], [25.53413161, -103.4356970], 
            [25.53413161, -103.4352519], [25.53435042, -103.4349653], [25.53435042, -103.4345123], [25.53420736, -103.4345123], 
            [25.53373844, -103.4347080], [25.53374832, -103.4353462], [25.53388083, -103.4354353], [25.53388083, -103.4359624],
            [25.53394708, -103.4359624], [25.53394344, -103.4361046]
        ]
    }
};

// 3. Función para crear Iconos Dinámicos
function createTrashIcon(color) {
    return L.divIcon({
        className: 'custom-div-icon',
        html: `
            <div class="trash-icon-container" style="border-color: ${color}; color: ${color};">
                <i class="fa-solid fa-trash-can"></i>
            </div>
        `,
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });
}

// 4. Dibujar Polígonos de Áreas
Object.keys(areaSettings).forEach(key => {
    const area = areaSettings[key];
    L.polygon(area.polygon, {
        color: area.colorArea,
        fillColor: area.colorArea,
        fillOpacity: 0.2,
        dashArray: '5, 10'
    }).addTo(map);
});

// 5. Renderizar Marcadores desde la Base de Datos
if (Array.isArray(datosContenedores) && datosContenedores.length > 0) {
    datosContenedores.forEach(c => {
        if (c.latitud && c.longitud) {
            // Color por defecto (puedes cambiarlo según el id_contenedor o área)
            let iconColor = "#34495e"; 

            var marker = L.marker([parseFloat(c.latitud), parseFloat(c.longitud)], {
                icon: createTrashIcon(iconColor)
            }).addTo(map);

            // Popup con datos reales de la BD
            marker.bindPopup(`
                <div style="font-family: 'Poppins', sans-serif;">
                    <b style="color: #2c3e50;">Contenedor #${c.id_contenedor}</b><br>
                    <hr style="margin: 5px 0; border: 0; border-top: 1px solid #eee;">
                    <div style="font-size: 12px;">
                        📍 <b>Estado:</b> ${c.estado || 'Activo'}<br>
                        🌡️ <b>Temp:</b> ${c.temperatura || '0'}°C<br>
                        💧 <b>Hum:</b> ${c.humedad || '0'}%<br>
                        ⚖️ <b>Peso:</b> ${c.peso || '0'} kg
                    </div>
                </div>
            `);
        }
    });
}

// 6. Funciones de Interfaz de Usuario (UI)
function toggleAreaList() {
    const list = document.getElementById('area-list');
    const arrow = document.getElementById('arrow-icon');
    list.classList.toggle('collapsed');
    arrow.classList.toggle('rotated');
    document.getElementById('info-card').classList.add('hidden');
}

function selectArea(key) {
    const data = areaSettings[key];
    
    // UI Updates
    document.getElementById('area-list').classList.add('collapsed');
    document.getElementById('arrow-icon').classList.remove('rotated');
    document.getElementById('zone-name').innerText = "📍 " + data.title;

    document.getElementById('card-title').innerText = data.title;
    document.getElementById('card-cont').innerText = (key === 'sistemas') ? "4" : "5"; // Ejemplo estático
    document.getElementById('card-reg').innerText = "Actualizado recientemente";
    document.getElementById('card-hr').style.borderColor = data.colorArea;
    
    const badge = document.getElementById('card-prior');
    badge.innerText = data.prioridad;
    badge.style.backgroundColor = data.colorBadge;

    document.getElementById('info-card').classList.remove('hidden');

    // Movimiento de cámara
    map.flyTo(data.coords, 19);
}

function resetUI() {
    document.getElementById('info-card').classList.add('hidden');
    document.getElementById('zone-name').innerText = "📍 Seleccionar Área";
    map.flyTo([25.5334, -103.4358], 18);
}