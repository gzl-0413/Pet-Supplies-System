document.addEventListener('DOMContentLoaded', function () {
    const ctxSatisfaction = document.getElementById('ratingChart').getContext('2d');
    const labels = JSON.parse(document.getElementById('chart-data-labels').textContent);
    const data = JSON.parse(document.getElementById('chart-data').textContent);
    const percentages = JSON.parse(document.getElementById('chart-data-percentages').textContent);
    const chartLabels = labels.map((label, index) => `${label}: ${data[index]} (${percentages[index]}%)`);
    
    new Chart(ctxSatisfaction, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Number of Reviews',
                data: data,
                borderWidth: 1,
                backgroundColor: ['#ffc8c8', '#ffdec8', '#d4ffc8', '#c8f6ff', '#dcc8ff'],
                borderColor: ['#cc0000', '#c48000', '#99cc00', '#0099cc', '#8a66cc'],
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    const revenueDates = JSON.parse(document.getElementById('chart-data-dates').textContent);
    const revenueData = JSON.parse(document.getElementById('chart-data-revenues').textContent);

    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: revenueDates,
            datasets: [{
                label: 'Revenue by Date',
                data: revenueData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                fill: false
            }]
        },
        options: {
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day',
                        tooltipFormat: 'YYYY-MM-DD'
                    },
                    title: {
                        display: true,
                        text: 'Date',
                        font: {
                            size: 14
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue (RM)',
                        font: {
                            size: 14
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });

    const customerLabels = JSON.parse(document.getElementById('chart-data-customers').innerHTML);
    const purchaseData = JSON.parse(document.getElementById('chart-data-purchases').innerHTML);

    const ctxTopCustomers = document.getElementById('topCustomersChart').getContext('2d');
    const topCustomersChart = new Chart(ctxTopCustomers, {
        type: 'bar',
        data: {
            labels: customerLabels,
            datasets: [{
                label: 'Total Purchase (RM)',
                data: purchaseData,
                backgroundColor: ['#ffc8c8', '#ffdec8', '#d4ffc8', '#c8f6ff', '#dcc8ff'],
                borderColor: ['#cc0000', '#c48000', '#99cc00', '#0099cc', '#8a66cc'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Purchase (RM)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Customer ID'
                    }
                }
            }
        }
    });    

    const salesChartCtx = document.getElementById('salesChart').getContext('2d');
    const productNames = JSON.parse(document.getElementById('salesChart').dataset.productNames);
    const productQuantities = JSON.parse(document.getElementById('salesChart').dataset.productQuantities);

    const salesChart = new Chart(salesChartCtx, {
        type: 'pie',
        data: {
            labels: productNames,
            datasets: [{
                data: productQuantities,
                backgroundColor: ['#ffc8c8', '#ffdec8', '#d4ffc8', '#c8f6ff', '#dcc8ff']
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            const total = tooltipItem.chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);
                            const percentage = ((tooltipItem.raw / total) * 100).toFixed(2);
                            return `${tooltipItem.label}: ${tooltipItem.raw} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    formatter: (value, context) => {
                        const total = context.chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);
                        const percentage = ((value / total) * 100).toFixed(2) + '%';
                        return percentage;
                    },
                    color: '#fff',
                }
            }
        }
    });

});
