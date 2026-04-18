var map = L.map('map').setView([25.533367, -103.436089], 18);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, 
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

var totalContenedoresBD = datosContenedores.length;

var polygonS = L.polygon([
    [25.533517258469256, -103.43583979606757],
    [25.5335427070657, -103.4365247285743],
    [25.533248230119426, -103.4365247285743],
    [25.533244594597054, -103.43624269754213],
    [25.53257202106215, -103.43612988512923],
    [25.53256111443319, -103.43581965099384]
]).addTo(map);

polygonS.bindPopup(`Area de Sistemas 💻- Tec Laguna</br>Contenedores encontrados: ${totalContenedoresBD}`);

if (Array.isArray(datosContenedores)) {
    datosContenedores.forEach(function(c) {
        // Verificamos que existan coordenadas válidas
        if (c.latitud && c.longitud) {
            L.marker([parseFloat(c.latitud), parseFloat(c.longitud)])
                .addTo(map)
                .bindPopup(
                    "<b>Contenedor #" + c.id + "</b><br>" +
                    "Estado: " + (c.estado || 'Desconocido') + "<br>" +
                    "Humedad: " + (c.humedad || '0') + "%"
                );
        }
    });
} else {
    console.error("No se recibieron datos de los contenedores.");
}

setTimeout(function(){ 
    map.invalidateSize(); 
}, 500);