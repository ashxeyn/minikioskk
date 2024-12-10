document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
    const labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const monthlySalesData = JSON.parse(document.getElementById('monthlySalesData').textContent);

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

    $('#filterForm').on('submit', function (e) {
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
});
