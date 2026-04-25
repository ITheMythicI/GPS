// Datos de ejemplo
const areaData = {
    sistemas: {
        coords: [25.5330, -103.4361],
        title: "Área de Sistemas",
        contenedores: 4,
        registros: "24 hoy",
        prioridad: "Alta",
        color: "#e74c3c",
        colorArea: "#34495e"
    },
    quimica: {
        coords: [25.5340, -103.4355],
        title: "Área de Química",
        contenedores: 5,
        registros: "12 hoy",
        prioridad: "Media",
        color: "#f39c12",
        colorArea: "#27ae60"
    }
};

function toggleAreaList() {
    const list = document.getElementById('area-list');
    const arrow = document.getElementById('arrow-icon');
    list.classList.toggle('collapsed');
    arrow.classList.toggle('rotated');
    
    // Si abrimos la lista, ocultamos la tarjeta de info (si estaba abierta)
    document.getElementById('info-card').classList.add('hidden');
}

function selectArea(key) {
    const data = areaData[key];

    // 1. "Retraer" la lista y actualizar cabecera
    document.getElementById('area-list').classList.add('collapsed');
    document.getElementById('arrow-icon').classList.remove('rotated');
    document.getElementById('zone-name').innerText = "📍 " + data.title;

    // 2. Llenar y mostrar la tarjeta de información debajo
    document.getElementById('card-title').innerText = data.title;
    document.getElementById('card-cont').innerText = data.contenedores;
    document.getElementById('card-reg').innerText = data.registros;
    
    // Usando borderColor
    document.getElementById('card-hr').style.borderColor = data.colorArea;
    
    const badge = document.getElementById('card-prior');
    badge.innerText = data.prioridad;
    badge.style.backgroundColor = data.color;

    document.getElementById('info-card').classList.remove('hidden');

    // 3. Mover mapa
    map.flyTo(data.coords, 19);
}

function resetUI() {
    document.getElementById('info-card').classList.add('hidden');
    document.getElementById('zone-name').innerText = "📍 Seleccionar Área";
}
