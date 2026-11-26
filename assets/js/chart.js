// Chart.js custom configurations dan helper functions

class FitnessCharts {
    constructor() {
        this.colors = {
            primary: '#3B82F6',
            secondary: '#6366F1',
            success: '#10B981',
            danger: '#EF4444',
            warning: '#F59E0B',
            info: '#06B6D4',
            light: '#6B7280',
            dark: '#1F2937'
        };
    }

    // Grafik pembayaran bulanan
    createPaymentChart(ctx, data) {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Total Pembayaran',
                    data: data,
                    backgroundColor: this.colors.primary,
                    borderColor: this.colors.dark,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Rp ${context.raw.toLocaleString('id-ID')}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Grafik line untuk trend
    createTrendChart(ctx, data, label) {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: this.colors.success,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: this.colors.success,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Pie chart untuk status member
    createMemberStatusChart(ctx, active, inactive) {
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Tidak Aktif'],
                datasets: [{
                    data: [active, inactive],
                    backgroundColor: [
                        this.colors.success,
                        this.colors.danger
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // Grafik kehadiran harian
    createAttendanceChart(ctx, data) {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Kehadiran Harian',
                    data: data,
                    borderColor: this.colors.info,
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Grafik perbandingan bulanan
    createComparisonChart(ctx, currentData, previousData) {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Member', 'Pembayaran', 'Kehadiran'],
                datasets: [
                    {
                        label: 'Bulan Ini',
                        data: currentData,
                        backgroundColor: this.colors.primary,
                        borderColor: this.colors.dark,
                        borderWidth: 1
                    },
                    {
                        label: 'Bulan Lalu',
                        data: previousData,
                        backgroundColor: this.colors.light,
                        borderColor: this.colors.dark,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    const fitnessCharts = new FitnessCharts();
    
    // Auto-initialize charts with data attributes
    const chartElements = document.querySelectorAll('[data-chart]');
    
    chartElements.forEach(element => {
        const chartType = element.getAttribute('data-chart');
        const chartData = element.getAttribute('data-chart-data');
        
        if (chartData) {
            try {
                const data = JSON.parse(chartData);
                
                switch(chartType) {
                    case 'payment':
                        fitnessCharts.createPaymentChart(element, data);
                        break;
                    case 'attendance':
                        fitnessCharts.createAttendanceChart(element, data);
                        break;
                    case 'member-status':
                        const [active, inactive] = data;
                        fitnessCharts.createMemberStatusChart(element, active, inactive);
                        break;
                    case 'trend':
                        const label = element.getAttribute('data-chart-label') || 'Trend';
                        fitnessCharts.createTrendChart(element, data, label);
                        break;
                }
            } catch (error) {
                console.error('Error initializing chart:', error);
            }
        }
    });
});

// Utility function untuk update chart data
function updateChart(chart, newData) {
    chart.data.datasets[0].data = newData;
    chart.update();
}

// Export untuk penggunaan global
window.FitnessCharts = FitnessCharts;