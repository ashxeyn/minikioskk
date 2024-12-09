function deleteProduct(productId) {
    document.getElementById('product_id_to_remove').value = productId;
}

function editProduct(productId, quantity) {
    console.log('Product ID:', productId);
    document.getElementById('product_id').value = productId;
    document.getElementById('new_quantity').value = quantity;
}

function loadDashboardSection() {
    $.ajax({
        url: "../customers/customerDashboard.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading dashboard section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Dashboard section. Please try again.</p>');
        }
    });
}

function loadCartSection() {
    $.ajax({
        url: "../customers/cart.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading cart section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Cart section. Please try again.</p>');
        }
    });
}

function loadOrderStatusSection() {
    $.ajax({
        url: "../customers/order_status.php",
        method: 'GET',
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading order status section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load Order Status section. Please try again.</p>');
        }
    });
}


// document.addEventListener("DOMContentLoaded", function() {
//     const modal = new bootstrap.Modal(document.getElementById("orderModal"));
//     const orderItemsList = document.getElementById("orderItemsList");

//     document.querySelectorAll(".order-card.view-items").forEach(card => {
//         card.addEventListener("click", function() {
//             const orderId = card.closest(".order-card").getAttribute("data-order-id");

//             fetch('order_status.php', {
//                 method: 'POST',
//                 body: new URLSearchParams({ 'order_id': orderId })
//             })
//             .then(response => response.text())
//             .then(data => {
//                 orderItemsList.innerHTML = data;
//                 modal.show();
//             })
//             .catch(error => console.error("Error fetching order items:", error));
//         });
//     });
// });