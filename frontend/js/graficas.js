document.addEventListener('DOMContentLoaded', function () {

  new Chart(document.getElementById('tabla_barras'), {
    type: 'bar',
    data: {
      labels: labelsBarras,
      datasets: [{
        label: 'Peso (kg)',
        data: dataBarras,
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true }
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
