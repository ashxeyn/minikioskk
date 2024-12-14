function handleOrderAction(orderId, action) {
    if (!confirm('Are you sure you want to ' + action + ' this order?')) {
        return;
    }

    $.ajax({
        url: 'ajax/handleOrderAction.php',
        type: 'POST',
        data: {
            order_id: orderId,
            action: action
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
             
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while processing your request.');
        }
    });
} 