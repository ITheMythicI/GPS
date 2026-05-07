document.addEventListener('DOMContentLoaded', function () {
  const barras = document.getElementById('tabla_barras');

  new Chart(barras, {
    type: 'bar',
    data: {
      labels: ['1', '2', '3', '4', '5', '6'],
      datasets: [{
        label: 'PORCENTAJE',
        data: [95, 10, 55, 75, 85, 60],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });

  new Chart(document.getElementById('tabla_dona'), {

    type: 'doughnut',
    data: {
        labels: labelsDona,
        datasets: [{
            label: 'My First Dataset',
        data: dataDona,
        backgroundColor: [
        'rgb(255, 99, 132)',
        'rgb(54, 162, 235)',
        'rgb(255, 205, 86)'
        ],
        hoverOffset: 4
        }]
    },
     options: {
    }
    
});

});

