let searchInput = document.getElementById("search");
let tableRows = document.querySelectorAll(".dataRow");

searchInput.addEventListener("input", function() {
    let searchValue = searchInput.value.toUpperCase();
    
    tableRows.forEach(function(row) {
        let rowText = row.innerText.toUpperCase();
        if (rowText.indexOf(searchValue) === -1) {
            row.style.display = "none";
        } else {
            row.style.display = "table-row";
        }
    });
});

function filter() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("filter");
    filter = input.value.toUpperCase();
    table = document.getElementById("table");
    tr = table.getElementsByTagName("tr");

    for (i = 1; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[6];  
        if (td) {
            txtValue = td.textContent || td.innerText;
            if (filter === "" || txtValue.toUpperCase() === filter) {
                tr[i].style.display = ""; 
            } else {
                tr[i].style.display = "none"; 
            }
        }
    }
}

function filterCategory() {
    const category = document.getElementById('category').value.toUpperCase();
    const tableRows = document.querySelectorAll("#table tbody tr");

    tableRows.forEach(row => {
        const roleCell = row.querySelector("td:nth-child(7)"); 
        if (roleCell) {
            const roleText = roleCell.textContent.toUpperCase();
            if (category === "" || roleText === category) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
}

function filterProductCategory() {
    const category = document.getElementById('category').value.toUpperCase();
    const tableRows = document.querySelectorAll("#table tbody tr");

    tableRows.forEach(row => {
        const categoryCell = row.querySelector("td:nth-child(5)"); 
        if (categoryCell) {
            const categoryText = categoryCell.textContent.toUpperCase();
            if (category === "" || categoryText === category) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
}

function filterOrderStatus() {
    const status = document.getElementById('status').value.toUpperCase();
    const tableRows = document.querySelectorAll("#orderTable tbody tr");

    tableRows.forEach(row => {
        const statusCell = row.querySelector("td:nth-child(8)"); 
        if (statusCell) {
            const statusText = statusCell.textContent.toUpperCase();
            if (status === "" || statusText === status) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
}



