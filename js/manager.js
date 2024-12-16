document.addEventListener("DOMContentLoaded", function () {
    // Only initialize chart if the element exists
    const chartElement = document.getElementById('monthlySalesChart');
    if (chartElement) {
        const ctx = chartElement.getContext('2d');
        const labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const monthlySalesDataElement = document.getElementById('monthlySalesData');
        
        if (monthlySalesDataElement) {
            const monthlySalesData = JSON.parse(monthlySalesDataElement.textContent);

            const monthlySalesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly Sales',
                        data: monthlySalesData,
                        borderColor: '#FFA500',
                        backgroundColor: 'rgba(255, 165, 0, 0.5)',
                        borderWidth: 2
                    }]
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

            // Only attach filter form handler if the form exists
            const filterForm = $('#filterForm');
            if (filterForm.length) {
                filterForm.on('submit', function (e) {
                    e.preventDefault();
                    const startDate = $('#startDate').val();
                    const endDate = $('#endDate').val();

                    if (!startDate || !endDate) {
                        alert('Please select both start and end dates.');
                        return;
                    }

                    $.ajax({
                        url: '../manager/filterManagerAnalytics.php',
                        type: 'POST',
                        data: { start_date: startDate, end_date: endDate },
                        success: function (response) {
                            const data = JSON.parse(response);
                            $('#customerCount').text(data.customer_count);
                            $('#completedOrders').text(data.completed_orders);
                            $('#totalSales').text(`₱${parseFloat(data.total_sales).toLocaleString()}`);
                            
                            let productRows = '';
                            data.top_selling_products.forEach(product => {
                                productRows += `<tr>
                                    <td>${product.product_name}</td>
                                    <td>${product.total_sold}</td>
                                </tr>`;
                            });
                            $('#topSellingProducts').html(productRows);

                            monthlySalesChart.data.datasets[0].data = data.monthly_sales;
                            monthlySalesChart.update();
                        },
                        error: function () {
                            alert('An error occurred while fetching the filtered data.');
                        }
                    });
                });
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
