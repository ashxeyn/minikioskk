$(document).ready(function () {
    console.log("Document ready, setting up event listeners...");

    // Load Home by default on page load
    loadHomeSection();

    // Set event listeners for navigation buttons
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

// Function to load the home section into the main content area
function loadHomeSection() {
    console.log('Loading home section...');
    $('#searchSection').show();
    $.get('search_results.php', function(data) {
        $('#contentArea').html(data);
        initializeSearchHandlers();
    });
}

function initializeSearchHandlers() {
    // Handle form submission
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        performSearch($(this));
    });

    // Handle radio button changes
    $('input[name="search_type"]').on('change', function() {
        performSearch($(this).closest('form'));
    });

    // Handle real-time search
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

// Function to load the Cart section into the main content area
function loadCartSection() {
    $('#searchSection').hide();
    $.ajax({
        url: 'myCart.php',
        type: 'GET',
        dataType: 'html',
        success: function(response) {
            console.log('Cart content successfully loaded.');
            $('#contentArea').html(response);
            
            // Reinitialize event handlers after loading content
            initializeCartHandlers();   
        },
        error: function(xhr, status, error) {
            console.error('Error loading cart:', error);
        }
    });
}

// Function to dynamically load canteen details into the main content area
function loadCanteenDetails(canteenId) {
    $('#searchSection').hide();
    console.log('AJAX request to fetch canteen details for ID:', canteenId);
    $.ajax({
        url: '../customers/viewCanteen.php',
        method: 'GET',
        data: { canteen_id: canteenId },
        success: function (response) {
            console.log('Canteen details loaded successfully.');
            $('#contentArea').html(response);
        },
        error: function () {
            alert('Failed to load canteen details. Please try again.');
        }
    });
}

// Function to load the Order Status section into the main content area
function loadOrderStatusSection() {
    $('#searchSection').hide();
    $.get('orderStatus.php', function(data) {
        $('#contentArea').html(data);
    });
}

// Add this function to initialize all cart-related event handlers
function initializeCartHandlers() {
    // Clear cart button handler
    $('.clear-all').off('click').on('click', function() {
        clearCart();
    });

    // Checkout button handler
    $('.checkout').off('click').on('click', function() {
        openCheckoutModal();
    });

    // Other event handlers as needed...
}

// Make sure these functions are globally available
window.clearCart = function() {
    // Your existing clearCart code
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

// Initialize handlers when document is ready
$(document).ready(function() {
    initializeCartHandlers();
});
