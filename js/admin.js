function loadOrderSection() {
    $.ajax({
        url: "../admin/view_orders.php",
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
function loadDashboardSection() {
    $.ajax({
        url: "../admin/view_analytics.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
            initializeDashboardChart();
        },
        error: function (xhr, status, error) {
            console.error('Error loading dashboard section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Dashboard section. Please try again.</p>');
        }
    });
}
function loadProductsSection() {
    $.ajax({
        url: "../admin/view_products.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading products section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Products section. Please try again.</p>');
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

function loadProductsSection() {
    $.ajax({
        url: "../admin/view_products.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading products section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Products section. Please try again.</p>');
        }
    });
}

function loadCanteenSection() {
    $.ajax({
        url: "../canteen/canteenSection.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
            // DataTable is initialized in canteenSection.php
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
        success: function(response) {
            $('#contentArea').html(response);
            // Let program.js handle the initialization
        },
        error: function(xhr, status, error) {
            console.error('Error loading program section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Program section. Please try again.</p>');
        }
    });
}

function loadOrderSection() {
    $.ajax({
        url: "../admin/view_orders.php",
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

function initializeDashboardChart() {
    const chartCanvas = document.getElementById('ordersByCollegeChart');
    if (!chartCanvas) {
        console.warn('Chart canvas not found');
        return;
    }

    const ctx = chartCanvas.getContext('2d');
    const collegesElement = document.getElementById('collegesData');
    const orderCountsElement = document.getElementById('orderCountsData');

    if (!collegesElement || !orderCountsElement) {
        console.warn('Chart data elements not found');
        return;
    }

    const colleges = JSON.parse(collegesElement.textContent);
    const orderCounts = JSON.parse(orderCountsElement.textContent);

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
                        label: function(tooltipItem) {
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
}

function reloadCanteenTable() {
    if (window.canteenTable) {
        window.canteenTable.ajax.reload(null, false);
    }
}

$(document).ready(function() {
    // Handle all section loading through data attributes
    $(document).on('click', '[data-section]', function(e) {
        e.preventDefault();
        const section = $(this).data('section');
        
        // Remove active class from all nav links
        $('.nav-link').removeClass('active');
        // Add active class to clicked link
        $(this).addClass('active');
        
        switch(section) {
            case 'dashboard':
                loadDashboardSection();
                break;
            case 'program':
                loadProgramSection();
                break;
            
            case 'orders':
                loadOrderSection();
                break;
            case 'canteen':
                loadCanteenSection();
                break;
            case 'accounts':
                loadAccountsSection();
                break;
            case 'products':
                loadProductsSection();
                break;
            case 'registration':
                loadRegistrationSection();
                break;
        }
    });
});
