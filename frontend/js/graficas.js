/**
 * graficas.js - Manejo de gráficas con soporte para actualización en vivo
 */

let barChart = null;
let doughnutChart = null;

const Charts = {
    init(labelsBarras, dataBarras, labelsDona, dataDona) {
        const ctxBarras = document.getElementById('tabla_barras');
        const ctxDona = document.getElementById('tabla_dona');

        if (ctxBarras) {
            barChart = new Chart(ctxBarras, {
                type: 'bar',
                data: {
                    labels: labelsBarras,
                    datasets: [{
                        label: 'Llenado (%)',
                        data: dataBarras,
                        backgroundColor: '#3b82f6',
                        borderWidth: 0,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, max: 100 } }
                }
            });
        }

        if (ctxDona) {
            doughnutChart = new Chart(ctxDona, {
                type: 'doughnut',
                data: {
                    labels: labelsDona,
                    datasets: [{
                        label: 'Contenedores',
                        data: dataDona,
                        backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                        hoverOffset: 4
                    }]
                }
            });
        }
    },

    update(labelsBarras, dataBarras, labelsDona, dataDona) {
        if (barChart) {
            barChart.data.labels = labelsBarras;
            barChart.data.datasets[0].data = dataBarras;
            barChart.update();
        }
        if (doughnutChart) {
            doughnutChart.data.labels = labelsDona;
            doughnutChart.data.datasets[0].data = dataDona;
            doughnutChart.update();
        }
    }
};

// Inicialización con datos de PHP (inyectados en el HTML)
document.addEventListener('DOMContentLoaded', () => {
    if (typeof labelsBarras !== 'undefined') {
        Charts.init(labelsBarras, dataBarras, labelsDona, dataDona);
    }
});
