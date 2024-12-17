(function() {
    'use strict';
    
    let currentOrderId = null;
    let refreshInterval = null; // For storing the interval reference

    // Make functions available globally
    window.reorderItems = function(orderId) {
        currentOrderId = orderId;
        
        // Find the order card
        const orderCard = document.querySelector(`.order-card button[onclick*="${orderId}"]`).closest('.order-card');
        
        // Find items and total
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
    };

    window.cancelOrder = function(orderId) {
        // Find the order card and check status
        const orderCard = document.querySelector(`.order-card button[onclick*="${orderId}"]`).closest('.order-card');
        const statusElement = orderCard.querySelector('.status-pending, .status-accepted, .status-preparing, .status-ready');
        
        if (statusElement && !statusElement.classList.contains('status-pending')) {
            showResponseModal('This order cannot be cancelled anymore', false);
            return;
        }

        currentOrderId = orderId;
        const cancelModal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
        cancelModal.show();
    };

    function showResponseModal(message, success) {
        const responseModal = document.getElementById('responseModal');
        const messageElement = document.getElementById('responseMessage');
        
        messageElement.textContent = message;
        messageElement.className = success ? 'text-success' : 'text-danger';
        
        const bsResponseModal = new bootstrap.Modal(responseModal);
        bsResponseModal.show();
    }

    function refreshOrdersContent() {
        $.ajax({
            url: window.location.href,
            type: 'GET',
            success: function(response) {
                // Create a temporary div to parse the HTML response
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = response;

                // Find the orders sections in both current and new content
                const currentActiveSection = document.querySelector('.active-section .orders-grid-container');
                const currentCompletedSection = document.querySelector('.completed-section .orders-grid-container');
                const newActiveSection = tempDiv.querySelector('.active-section .orders-grid-container');
                const newCompletedSection = tempDiv.querySelector('.completed-section .orders-grid-container');

                // Update the content if the sections exist
                if (currentActiveSection && newActiveSection) {
                    currentActiveSection.innerHTML = newActiveSection.innerHTML;
                }
                if (currentCompletedSection && newCompletedSection) {
                    currentCompletedSection.innerHTML = newCompletedSection.innerHTML;
                }

                // Re-initialize any necessary event listeners or components
                initializeComponents();
            },
            error: function() {
                console.error('Failed to refresh content');
            }
        });
    }

    function startAutoRefresh() {
        // Clear any existing interval
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        
        // Set new interval for 30 seconds
        refreshInterval = setInterval(refreshOrdersContent, 30000);
    }

    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Stop refresh when page is not visible
            stopAutoRefresh();
        } else {
            // Resume refresh when page becomes visible
            startAutoRefresh();
            // Immediate refresh when becoming visible
            refreshOrdersContent();
        }
    });

    function initializeComponents() {
        // Re-initialize any Bootstrap components or other UI elements if needed
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    }

    // Initialize event listeners when document is ready
    $(document).ready(function() {
        // Start auto-refresh when page loads
        startAutoRefresh();
        
        // Initialize components
        initializeComponents();

        // Add event listener for confirm reorder button
        $('#confirmReorder').on('click', function() {
            if (!currentOrderId) return;
            
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
                        setTimeout(refreshOrdersContent, 1500);
                    }
                },
                error: function() {
                    showResponseModal('Error occurred while reordering items', false);
                }
            });
        });

        // Add event listener for confirm cancel button
        $('#confirmCancel').on('click', function() {
            if (!currentOrderId) return;
            
            const cancelModal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderModal'));
            cancelModal.hide();
            
            $.ajax({
                url: '../ajax/cancelOrder.php',
                type: 'POST',
                data: { order_id: currentOrderId },
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        showResponseModal('Order cancelled successfully', true);
                        setTimeout(refreshOrdersContent, 1500);
                    } else {
                        const errorMsg = response ? response.message : 'Failed to cancel order';
                        showResponseModal(errorMsg, false);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Failed to cancel order';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch (e) {
                        console.error('Cancel order error:', xhr.responseText);
                    }
                    showResponseModal(errorMsg, false);
                }
            });
        });
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        stopAutoRefresh();
    });
})(); 