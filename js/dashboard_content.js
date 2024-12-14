$(document).ready(function () {
    loadHomeSection();

    $('#homeLink').on('click', function (e) {
        e.preventDefault();
        loadHomeSection();
    });

    $('#cartLink').on('click', function (e) {
        e.preventDefault();
        loadCartSection();
    });

    $('#statusLink').on('click', function (e) {
        e.preventDefault();
        loadOrderStatusSection();
    });
});

function loadHomeSection() {
    $('#searchSection').show();
    $.get('search_results.php', function(data) {
        $('#contentArea').html(data);
        initializeSearchHandlers();
    });
}

function initializeSearchHandlers() {
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        performSearch($(this));
    });

    $('input[name="search_type"]').on('change', function() {
        performSearch($(this).closest('form'));
    });

    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch($(this).closest('form'));
        }, 500);
    });
}

function performSearch(form) {
    const formData = form.serialize();
    
    $.get('search_results.php?' + formData, function(data) {
        $('#contentArea').html(data);
    });
}

function loadCartSection() {
    $('#searchSection').hide();
    $.ajax({
        url: 'myCart.php',
        type: 'GET',
        dataType: 'html',
        success: function(response) {
            $('#contentArea').html(response);
            initializeCartHandlers();   
        },
        error: function(xhr, status, error) {
            console.error('Error loading cart:', error);
        }
    });
}

function loadCanteenDetails(canteenId) {
    $('#searchSection').hide();
    $.ajax({
        url: '../customers/viewCanteen.php',
        method: 'GET',
        data: { canteen_id: canteenId },
        success: function (response) {
            $('#contentArea').html(response);
        },
        error: function () {
            alert('Failed to load canteen details. Please try again.');
        }
    });
}

function loadOrderStatusSection() {
    $('#searchSection').hide();
    $.get('orderStatus.php', function(data) {
        $('#contentArea').html(data);
    });
}

function initializeCartHandlers() {
    $('.clear-all').off('click').on('click', function() {
        clearCart();
    });

    $('.checkout').off('click').on('click', function() {
        openCheckoutModal();
    });
}

window.clearCart = function() {
    let modalCartItems = '';
    let total = 0;

    $('.cart-item').each(function() {
        const name = $(this).find('.item-name').text();
        const quantity = $(this).find('.quantity').text().replace('Quantity: ', '');
        const price = $(this).find('.total').text();
        
        modalCartItems += `<div class="d-flex justify-content-between mb-2">
            <span>${name} x ${quantity}</span>
            <span>${price}</span>
        </div>`;
        
        total += parseFloat(price.replace('₱', '').replace(',', ''));
    });

    $('#modalClearCartItems').html(modalCartItems);
    $('#modalClearCartAmount').text('₱' + total.toFixed(2));

    const clearCartModal = new bootstrap.Modal(document.getElementById('clearCartModal'));
    clearCartModal.show();
};

window.openCheckoutModal = function() {
    let modalCartItems = '';
    let total = 0;

    $('.cart-item').each(function() {
        const name = $(this).find('.item-name').text();
        const quantity = $(this).find('.quantity').text().replace('Quantity: ', '');
        const price = $(this).find('.total').text();
        
        modalCartItems += `<div class="d-flex justify-content-between mb-2">
            <span>${name} x ${quantity}</span>
            <span>${price}</span>
        </div>`;
        
        total += parseFloat(price.replace('₱', '').replace(',', ''));
    });

    $('#modalCartItems').html(modalCartItems);
    $('#modalTotalAmount').text('₱' + total.toFixed(2));

    const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    checkoutModal.show();
};

$(document).ready(function() {
    initializeCartHandlers();
});
