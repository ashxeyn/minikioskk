<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Manage Co-Employees</h2>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bi bi-plus-circle"></i> Add Co-Employee
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body" id="employeeTableContainer">
        </div>
    </div>
</div>

<?php include 'addEmployeeModal.html'; ?>
<?php include 'editEmployeeModal.html'; ?> 