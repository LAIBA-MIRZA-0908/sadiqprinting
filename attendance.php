<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Employee Attendance</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    th.sticky-col { position: sticky; left: 0; background: #fff; z-index: 10; }
    td.sticky-col { position: sticky; left: 0; background: #f9fafb; }
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
            <h1 class="text-xl font-bold text-gray-800">Employee Attendance</h1>
            <div class="space-x-2 no-print">
                <select id="month" class="border border-gray-300 rounded px-2 py-1">
                    <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= $m==date('n')?'selected':'' ?>>
                            <?= date('F', mktime(0,0,0,$m,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select id="year" class="border border-gray-300 rounded px-2 py-1">
                    <?php $cy = date('Y'); for($y=$cy-2;$y<=$cy+1;$y++): ?>
                        <option value="<?= $y ?>" <?= $y==$cy?'selected':'' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button id="btnLoad" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Load</button>
                <button id="btnSave" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">Save</button>
            </div>
        </div>

        <div class="p-4 overflow-x-auto" id="printArea">
            <table class="min-w-full border border-gray-300">
                <thead class="bg-gray-800 text-white">
                    <tr id="attendanceHeader">
                        <th class="sticky-col px-2 py-1 text-center text-sm">Employee</th>
                    </tr>
                </thead>
                <tbody id="attendanceBody" class="bg-white divide-y divide-gray-200 text-sm text-center"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    loadAttendance();

    $('#btnLoad').click(loadAttendance);

    function loadAttendance(){
        const month = $('#month').val();
        const year = $('#year').val();

        $.post('hrfunctions.php', {action:'getAttendance', month, year}, function(res){
            $('#attendanceHeader').html('<th class="sticky-col px-2 py-1 text-center">Employee</th>');
            $('#attendanceBody').html('');

            const days = res.days;
            const employees = res.employees;
            const attendance = res.attendance;

            for(let d=1; d<=days; d++){
                $('#attendanceHeader').append(`<th class="px-2 py-1">${d}</th>`);
            }

            employees.forEach(emp => {
                let row = `<tr><td class="sticky-col px-2 py-1 text-left">${emp.full_name}</td>`;
                for(let d=1; d<=days; d++){
                    let key = emp.id + '-' + d;
                    let mark = attendance[key] ?? '';
                    row += `<td class="px-2 py-1">
                        <select class='border border-gray-300 rounded px-1 py-0.5 mark' data-id='${emp.id}' data-day='${d}'>
                            <option value=""></option>
                            <option value="Present" ${mark=='Present'?'selected':''}>Present</option>
                            <option value="Absent" ${mark=='Absent'?'selected':''}>Absent</option>
                            <option value="Leave" ${mark=='Leave'?'selected':''}>Leave</option>
                        </select>
                    </td>`;
                }
                row += '</tr>';
                $('#attendanceBody').append(row);
            });
        }, 'json');
    }

    $('#btnSave').click(function(){
        const month = $('#month').val();
        const year = $('#year').val();
        const data = [];

        $('.mark').each(function(){
            let emp = $(this).data('id');
            let day = $(this).data('day');
            let val = $(this).val();
            if(val) data.push({emp, day, val});
        });

        $.post('hrfunctions.php', {action:'saveAttendance', month, year, data: JSON.stringify(data)}, function(res){
            alert(res);
        });
    });
});
</script>
</body>
</html>
