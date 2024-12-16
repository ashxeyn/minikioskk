// Use module pattern to avoid global variables
const OrderManager = (function($) {
    let currentOrderId = null;

    function reorderItems(orderId) {
        currentOrderId = orderId;
        
        // Find the order card
        const orderCard = document.querySelector(`.order-card button[onclick*="${orderId}"]`).closest('.order-card');
        
        // Find items and total using more compatible selectors
        let items = '';
        let total = '';
        
        // Loop through order rows to find items and total
        orderCard.querySelectorAll('.order-row').forEach(row => {
            const label = row.querySelector('strong');
            if (label) {
                if (label.textContent === 'Items:') {
                    items = row.querySelector('p:last-child').textContent;
                }
                if (label.textContent === 'Total Amount:') {
                    total = row.querySelector('p:last-child').textContent;
                }
            }
        });
        
        // Populate modal
        document.getElementById('reorderItems').innerHTML = `
            <div class="mt-3">
                <strong>Items:</strong>
                <p class="mb-2">${items}</p>
            </div>
        `;
        document.getElementById('reorderTotal').textContent = total;
        
        // Show modal
        const reorderModal = new bootstrap.Modal(document.getElementById('reorderModal'));
        reorderModal.show();
    }

    function showResponseModal(message, success) {
        const responseModal = document.getElementById('responseModal');
        const messageElement = document.getElementById('responseMessage');
        
        messageElement.textContent = message;
        messageElement.className = success ? 'text-success' : 'text-danger';
        
        // Show response modal
        const bsResponseModal = new bootstrap.Modal(responseModal);
        bsResponseModal.show();
    }

    function updateCartCount() {
        $.ajax({
            url: '../ajax/getCartCount.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const cartCountElement = document.getElementById('cartCount');
                if (cartCountElement && response.count !== undefined) {
                    cartCountElement.textContent = response.count;
                }
            }
        });
    }

    // Initialize event listeners
    function init() {
        // Add event listener for confirm button
        $('#confirmReorder').on('click', function() {
            if (!currentOrderId) return;
            
            // Hide reorder modal
            const reorderModal = bootstrap.Modal.getInstance(document.getElementById('reorderModal'));
            reorderModal.hide();
            
            $.ajax({
                url: '../ajax/reorderItems.php',
                type: 'POST',
                data: { order_id: currentOrderId },
                dataType: 'json',
                success: function(response) {
                    showResponseModal(response.message, response.success);
                    if (response.success) {
                        // Reload the page after a short delay to show the new order
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function() {
                    showResponseModal('Error occurred while reordering items', false);
                }
            });
        });
    }

    // Public methods
    return {
        init: init,
        reorderItems: reorderItems
    };
})(jQuery);

// Initialize when document is ready
$(document).ready(function() {
    OrderManager.init();
    // Make reorderItems available globally
    window.reorderItems = OrderManager.reorderItems;
}); 