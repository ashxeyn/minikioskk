document.addEventListener("DOMContentLoaded", function () {
    // Only initialize chart if the element exists
    const chartElement = document.getElementById('monthlySalesChart');
    if (chartElement) {
        const ctx = chartElement.getContext('2d');
        const labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const monthlySalesDataElement = document.getElementById('monthlySalesData');
        
        if (monthlySalesDataElement) {
            try {
                const monthlySalesData = JSON.parse(monthlySalesDataElement.textContent);
                // Check if there's an existing chart instance
                if (window.salesChart instanceof Chart) {
                    window.salesChart.destroy();
                }
                
                window.salesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Monthly Sales',
                            data: monthlySalesData,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            fill: true,
                            tension: 0.4
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
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return '₱' + tooltipItem.value.toLocaleString();
                                }
                            }
                        }
                    }
                });

                // Add filter form handler
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    filterForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const startDate = document.getElementById('startDate').value;
                        const endDate = document.getElementById('endDate').value;

                        if (!startDate || !endDate) {
                            alert('Please select both start and end dates.');
                            return;
                        }

                        $.ajax({
                            url: '../manager/filterManagerAnalytics.php',
                            type: 'POST',
                            data: { start_date: startDate, end_date: endDate },
                            success: function(response) {
                                try {
                                    const data = JSON.parse(response);
                                    
                                    // Update statistics
                                    $('#customerCount').text(data.customer_count.toLocaleString());
                                    $('#completedOrders').text(data.completed_orders.toLocaleString());
                                    $('#totalSales').text(`₱${parseFloat(data.total_sales).toLocaleString()}`);
                                    
                                    // Update top selling products table
                                    let productRows = '';
                                    data.top_selling_products.forEach(product => {
                                        productRows += `<tr>
                                            <td>${product.product_name}</td>
                                            <td class="text-end">${parseInt(product.total_sold).toLocaleString()}</td>
                                        </tr>`;
                                    });
                                    $('#topSellingProducts').html(productRows);

                                    // Update chart
                                    window.salesChart.data.datasets[0].data = data.monthly_sales;
                                    window.salesChart.update();
                                } catch (error) {
                                    console.error('Error parsing response:', error);
                                    alert('Error processing the filtered data.');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Ajax error:', error);
                                alert('An error occurred while fetching the filtered data.');
                            }
                        });
                    });
                }
            } catch (error) {
                console.error('Error initializing chart:', error);
            }
        }
    }

    // Check URL parameters for section loading
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('section') === 'employees') {
        loadEmployeesSection();
    }
});

function loadEmployeesSection() {
    $.ajax({
        url: '../canteen/view_employees.php',
        method: 'GET',
        success: function(response) {
            $('#contentArea').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading employee section:', error);
            $('#contentArea').html('<div class="alert alert-danger">Error loading employee section</div>');
        }
    });
}

function loadProductsSection() {
    $.ajax({
        url: '../product/view_products.php',
        method: 'GET',
        success: function(response) {
            $('#contentArea').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading product section:', error);
            $('#contentArea').html('<div class="alert alert-danger">Error loading product section</div>');
        }
    });
}

function loadOrdersSection() {
    $.ajax({
        url: '../orders/view_orders.php',
        method: 'GET',
        success: function(response) {
            $('#contentArea').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading order section:', error);
            $('#contentArea').html('<div class="alert alert-danger">Error loading order section</div>');
        }
    });
}

function loadAnalyticsSection() {
    $.ajax({
        url: 'view_manager_analytics.php',
        method: 'GET',
        success: function(response) {
            $('#contentArea').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading analytics section:', error);
            $('#contentArea').html('<div class="alert alert-danger">Error loading analytics section</div>');
        }
    });
}

$(document).ready(function() {
    // Add any initialization code here
});

function submitAddEmployee() {
    const form = document.getElementById('addEmployeeForm');
    const formData = new FormData(form);

    fetch('../ajax/addEmployee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#addEmployeeModal').modal('hide');
            location.reload(); 
        } else {
            alert(data.message || 'Error adding employee');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding employee');
    });
}

function editEmployee(userId) {
    fetch(`../ajax/getEmployee.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editUserId').value = data.user_id;
            document.getElementById('editLastName').value = data.last_name;
            document.getElementById('editGivenName').value = data.given_name;
            document.getElementById('editMiddleName').value = data.middle_name || '';
            document.getElementById('editEmail').value = data.email;
            document.getElementById('editUsername').value = data.username;
            $('#editEmployeeModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading employee details');
        });
}

function submitEditEmployee() {
    const form = document.getElementById('editEmployeeForm');
    const formData = new FormData(form);

    fetch('../ajax/updateEmployee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#editEmployeeModal').modal('hide');
            location.reload(); 
        } else {
            alert(data.message || 'Error updating employee');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating employee');
    });
}

function deleteEmployee(userId) {
    if (confirm('Are you sure you want to delete this employee?')) {
        fetch('../ajax/deleteEmployee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); 
            } else {
                alert(data.message || 'Error deleting employee');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting employee');
        });
    }
}
