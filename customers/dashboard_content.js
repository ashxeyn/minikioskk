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
    $.ajax({
        url: "../customers/home.php",
        method: 'GET',
        success: function (response) {
            console.log('Home content successfully loaded.');
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading home section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load the Home section. Please try again.</p>');
        }
    });
}

// Function to load the Cart section into the main content area
function loadCartSection() {
    console.log('Loading cart section...');
    $.ajax({
        url: "../customers/myCart.php",
        method: 'GET',
        success: function (response) {
            console.log('Cart content successfully loaded.');
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading cart section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load the Cart section. Please try again.</p>');
        }
    });
}

// Function to dynamically load canteen details into the main content area
function loadCanteenDetails(canteenId) {
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
    console.log('Loading order status section...');
    $.ajax({
        url: "../customers/orderStatus.php",
        method: 'GET',
        success: function (response) {
            console.log('Order status content successfully loaded.');
            $('#contentArea').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error loading order status section:', error);
            $('#contentArea').html('<p class="text-danger">Failed to load the Order Status section. Please try again.</p>');
        }
    });
}
