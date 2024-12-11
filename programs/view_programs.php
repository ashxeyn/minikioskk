<?php
require_once '../classes/programClass.php';
$programObj = new Program();
?>

<head>
    <link rel="stylesheet" href="../css/table.css">
</head>

<div class='center'>
    <div class='table'>
        <form autocomplete='off'>
            <input type="search" id="searchProgram" placeholder="Search programs..." 
                   onkeyup="searchPrograms(this.value)">
        </form>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" onclick="openAddProgramModal()">Add Program</button>
        </div>
        <table class="table table-bordered" id="programTable">
            <thead>
                <tr>
                    <th>Program ID</th>
                    <th>Program Name</th>
                    <th>Department</th>
                    <th>College</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="programTableBody">
            </tbody>
        </table>
    </div>
</div>

<script>
function searchPrograms(keyword) {
    fetch(`../ajax/search_programs.php?keyword=${encodeURIComponent(keyword)}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('programTableBody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">No programs found</td>
                    </tr>`;
                return;
            }
            
            data.forEach(program => {
                tbody.innerHTML += `
                    <tr>
                        <td>${escapeHtml(program.program_id)}</td>
                        <td>${escapeHtml(program.program_name)}</td>
                        <td>${escapeHtml(program.department)}</td>
                        <td>${escapeHtml(program.college)}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(${program.program_id})">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteModal(${program.program_id})">Delete</button>
                        </td>
                    </tr>`;
            });
        })
        .catch(error => console.error('Error:', error));
}

function escapeHtml(unsafe) {
    return unsafe
        ? unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
        : '';
}

// Initial load
searchPrograms('');
</script>

