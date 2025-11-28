<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Monthly Payroll</title>
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
    <div class="bg-white shadow-md rounded-lg">
        <div class="flex justify-between items-center border-b px-5 py-3">
            <h1 class="text-xl font-bold text-gray-800">Employee Payroll</h1>
            <div class="flex space-x-2 no-print">
                <select id="month" class="border border-gray-300 rounded px-2 py-1">
                    <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= $m==date('n')?'selected':'' ?>>
                            <?= date('F', mktime(0,0,0,$m,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select id="year" class="border border-gray-300 rounded px-2 py-1">
                    <?php $cy=date('Y'); for($y=$cy-2;$y<=$cy+1;$y++): ?>
                        <option value="<?= $y ?>" <?= $y==$cy?'selected':'' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button id="btnLoad" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Load</button>
                <button id="btnSave" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">Save</button>
                <button id="btnPrint" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded">Print</button>
            </div>
        </div>

        <div class="p-4" id="printArea">
            <div class="text-center mb-4">
                <h5 class="font-bold text-lg">Company Payroll Sheet</h5>
                <p id="reportTitle" class="text-gray-500"></p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-center text-sm">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-2 py-1 border">#</th>
                            <th class="px-2 py-1 border">Employee Name</th>
                            <th class="px-2 py-1 border">Designation</th>
                            <th class="px-2 py-1 border">Monthly Salary (Rs)</th>
                            <th class="px-2 py-1 border">Days Present</th>
                            <th class="px-2 py-1 border">Total Days</th>
                            <th class="px-2 py-1 border">Calculated Salary</th>
                            <th class="px-2 py-1 border no-print">Adjust / Edit</th>
                        </tr>
                    </thead>
                    <tbody id="payrollBody">
                        <tr>
                            <td colspan="8" class="p-3 text-gray-500 text-center">Select month and year, then click Load</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    $('#btnLoad').click(loadPayroll);
    $('#btnPrint').click(() => window.print());

    function loadPayroll(){
        const month = $('#month').val();
        const year = $('#year').val();

        $('#reportTitle').text(`Payroll for ${$('#month option:selected').text()} ${year}`);
        $('#payrollBody').html('<tr><td colspan="8" class="text-center text-gray-500 p-3">Loading...</td></tr>');

        $.post('hrfunctions.php', {action:'getPayroll', month, year}, function(res){
            if(!Array.isArray(res)) {
                $('#payrollBody').html('<tr><td colspan="8" class="text-center text-red-600">Error loading data</td></tr>');
                return;
            }
            if(res.length == 0){
                $('#payrollBody').html('<tr><td colspan="8" class="text-center text-gray-500 p-3">No data found</td></tr>');
                return;
            }

            let rows = '';
            res.forEach((e,i)=>{
                rows += `<tr class="border-b">
                    <td class="px-2 py-1 border">${i+1}</td>
                    <td class="px-2 py-1 border text-left">${e.full_name}</td>
                    <td class="px-2 py-1 border">${e.designation||'-'}</td>
                    <td class="px-2 py-1 border">${e.salary_amount}</td>
                    <td class="px-2 py-1 border">${e.days_present}</td>
                    <td class="px-2 py-1 border">${e.total_days}</td>
                    <td class="px-2 py-1 border"><input type="number" class="border border-gray-300 rounded px-1 py-0.5 w-20 salary" data-id="${e.id}" value="${e.calculated_salary}"></td>
                    <td class="px-2 py-1 border no-print"><small>Edit if needed</small></td>
                </tr>`;
            });
            $('#payrollBody').html(rows);
        }, 'json');
    }

    $('#btnSave').click(function(){
        const month = $('#month').val();
        const year = $('#year').val();
        const data = [];

        $('.salary').each(function(){
            data.push({id: $(this).data('id'), salary: $(this).val()});
        });

        $.post('hrfunctions.php', {action:'savePayroll', month, year, data: JSON.stringify(data)}, function(res){
            alert(res);
        });
    });
});
</script>
</body>
</html>
