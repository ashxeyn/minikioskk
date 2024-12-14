function loadOrderSection() {
    $.ajax({
        url: "../orders/orderSection.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading accounts section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Accounts section. Please try again.</p>');
        }
    });
}

function loadAccountsSection() {
    $.ajax({
        url: "../users/accountsSection.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading accounts section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Accounts section. Please try again.</p>');
        }
    });
}

f

function loadCanteenSection() {
    $.ajax({
        url: "../canteen/canteenSection.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading canteen section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Canteen section. Please try again.</p>');
        }
    });
}

function loadProgramSection() {
    $.ajax({
        url: "../programs/programSection.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading program section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Program section. Please try again.</p>');
        }
    });
}

function loadOrderSection() {
    $.ajax({
        url: "../orders/orderSection.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading order section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Order section. Please try again.</p>');
        }
    });
}

function loadRegistrationSection() {
    $.ajax({
        url: "../canteen/canteenRegistration.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading accounts section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Accounts section. Please try again.</p>');
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('ordersByCollegeChart').getContext('2d');

    const colleges = JSON.parse(document.getElementById('collegesData').textContent);
    const orderCounts = JSON.parse(document.getElementById('orderCountsData').textContent);

    const data = {
        labels: colleges, 
        datasets: [{
            label: 'Orders by College',
            data: orderCounts, 
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                '#8E44AD', '#1ABC9C', '#F39C12', '#2ECC71', '#E74C3C', '#3498DB',
                '#9B59B6', '#27AE60', '#E67E22', '#BDC3C7'
            ],
            borderWidth: 1
        }]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false 
                },
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            const dataset = tooltipItem.dataset; 
                            const value = dataset.data[tooltipItem.dataIndex];
                            const label = tooltipItem.chart.data.labels[tooltipItem.dataIndex];
                            return `${label}: ${value} orders`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Colleges'
                    },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 90, 
                        minRotation: 45, 
                        font: {
                            size: 10 
                        }
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Number of Orders'
                    },
                    beginAtZero: true,
                    max: 1000, 
                    ticks: {
                        stepSize: 100
                    }
                }
            }
        }
    };

    new Chart(ctx, config);
});
