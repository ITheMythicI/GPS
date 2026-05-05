const estados = datosCont.map(c => c.estado);
const conteo_estados = {};
estados.forEach(e=>{ conteo[e]=(conteo[e] || 0 )+1;});

//Configuración del gráfico de dona

//
document.addEventListener('DOMContentLoaded', function () {
  
  const barras = document.getElementById('tabla_barras');

  new Chart(document.getElementById('tabla_barras'), {
    type: 'bar',
    data: {
      labels: labelsBarras,
      datasets: [{
        label: 'Porcentaje de llenado',
        data: dataBarras,
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true, max: 100 }
      }
    }
  });

  new Chart(document.getElementById('tabla_dona'), {
    type: 'doughnut',
    data: {
      labels: labelsDona,
      datasets: [{
        label: 'Contenedores',
        data: dataDona,
        labels: Object.keys(conteo_estados),
        datasets: [{
            label: 'My First Dataset',
        data: Object.values(conteo_estados),
        backgroundColor: [
          'rgb(255, 99, 132)',
          'rgb(54, 162, 235)',
          'rgb(255, 205, 86)'
        ],
        hoverOffset: 4
      }]
    }
  });

});
