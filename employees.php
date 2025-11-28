<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Employee Management</h1>
            <button id="btnAdd" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg no-print">
                + Add Employee
            </button>
        </div>

        <div class="bg-white shadow-md rounded-lg p-4">
            <div class="overflow-x-auto" id="printArea">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium">#</th>
                            <th class="px-4 py-2 text-left text-sm font-medium">Full Name</th>
                            <th class="px-4 py-2 text-left text-sm font-medium">CNIC</th>
                            <th class="px-4 py-2 text-left text-sm font-medium">Contact</th>
                            <th class="px-4 py-2 text-left text-sm font-medium">Designation</th>
                            <th class="px-4 py-2 text-left text-sm font-medium">Monthly Salary</th>
                            <th class="px-4 py-2 text-left text-sm font-medium no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTable" class="bg-white divide-y divide-gray-200">
                        <!-- Employee rows will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="employeeModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2">
            <div class="flex justify-between items-center border-b px-5 py-3">
                <h3 class="text-lg font-semibold modal-title">Add Employee</h3>
                <button id="closeModal" class="text-gray-600 hover:text-gray-800">&times;</button>
            </div>
            <div class="p-5">
                <form id="employeeForm">
                    <input type="hidden" name="id" id="employee_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="w-full px-3 py-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">CNIC</label>
                        <input type="text" id="cnic" name="cnic" class="w-full px-3 py-2 border border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="w-full px-3 py-2 border border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Designation</label>
                        <input type="text" id="designation" name="designation" class="w-full px-3 py-2 border border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Monthly Salary</label>
                        <input type="number" step="0.01" id="monthly_salary" name="monthly_salary" class="w-full px-3 py-2 border border-gray-300 rounded">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
$(document).ready(function(){
    loadEmployees();

    function loadEmployees(){
        $.post('hrfunctions.php', {action: 'getEmployees'}, function(data){
            $('#employeeTable').html(data);
        });
    }

    $('#btnAdd').click(function(){
        $('#employeeForm')[0].reset();
        $('#employee_id').val('');
        $('.modal-title').text('Add Employee');
        $('#employeeModal').removeClass('hidden');
    });

    $('#closeModal').click(function(){
        $('#employeeModal').addClass('hidden');
    });

    $('#employeeForm').submit(function(e){
        e.preventDefault();
        $.post('hrfunctions.php', $(this).serialize() + '&action=saveEmployee', function(res){
            alert(res);
            $('#employeeModal').addClass('hidden');
            loadEmployees();
        });
    });

    $(document).on('click', '.btnEdit', function(){
        let id = $(this).data('id');
        $.post('hrfunctions.php', {action:'getEmployeeById', id:id}, function(res){
            let emp = JSON.parse(res);
            $('#employee_id').val(emp.id);
            $('#full_name').val(emp.full_name);
            $('#cnic').val(emp.cnic);
            $('#contact_number').val(emp.contact_number);
            $('#designation').val(emp.designation);
            $('#monthly_salary').val(emp.monthly_salary);
            $('.modal-title').text('Edit Employee');
            $('#employeeModal').removeClass('hidden');
        });
    });

    $(document).on('click', '.btnDelete', function(){
        if(confirm('Delete this employee?')){
            let id = $(this).data('id');
            $.post('hrfunctions.php', {action:'deleteEmployee', id:id}, function(res){
                alert(res);
                loadEmployees();
            });
        }
    });
});
</script>
</body>
</html>
