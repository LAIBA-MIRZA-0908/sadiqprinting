<?php
include 'header.php';
include 'menu.php';
include 'db_connection.php';
$currentMonth = date('n');
$currentYear = date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Trial Balance</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<style>
.table-row-hover:hover { background: #f8fafc; }
@media print {
    body {
        background: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .no-print { display: none !important; }
    .print-container {
        width: 100%;
        margin: 0 auto;
        padding: 20px;
        page-break-after: always;
    }
    table {
        border-collapse: collapse !important;
        width: 100%;
        font-size: 13px;
    }
    th, td {
        border: 1px solid #000 !important;
        padding: 6px;
    }
    th {
        background-color: #f2f2f2 !important;
    }
    h1, h2, h3 {
        margin: 0;
        text-align: center;
    }
}
</style>
</head>

<body class="bg-gray-100">

<div class="container mx-auto px-4 py-6 print-container">

    <!-- Header -->
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">TRIAL BALANCE</h1>
        <h2 class="text-lg text-gray-700">Al Sadiq Printing</h2>
        <p class="text-sm text-gray-600">
            As of <span id="displayDate"><?= date('F Y') ?></span> - All Accounts
        </p>
    </div>

    <!-- Month / Year Selection -->
    <div class="bg-white rounded-lg shadow p-5 mb-6 no-print">
        <div class="flex space-x-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select id="month" class="border rounded p-2">
                    <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= ($m==$currentMonth)?'selected':'' ?>>
                            <?= date('F', mktime(0,0,0,$m,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select id="year" class="border rounded p-2">
                    <?php for($y=$currentYear-5;$y<=$currentYear;$y++): ?>
                        <option value="<?= $y ?>" <?= ($y==$currentYear)?'selected':'' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <button id="btnLoad" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Load Trial Balance
                </button>
            </div>
            <div>
                <button id="btnPrint" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Print
                </button>
            </div>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="bg-white rounded-lg shadow p-5">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-400" id="trialTable">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-2 border text-left">Account</th>
                        <th class="p-2 border text-left">Account #</th>
                        <th class="p-2 border text-right">Debit Amount</th>
                        <th class="p-2 border text-right">Credit Amount</th>
                    </tr>
                </thead>
                <tbody id="trialBalanceBody" class="bg-white">
                    <tr><td colspan="4" class="p-4 text-center text-gray-500">Loading data...</td></tr>
                </tbody>
                <tfoot id="trialBalanceTotal" class="font-semibold bg-gray-50 text-gray-700"></tfoot>
            </table>
        </div>
    </div>
</div>

<script>
$(function() {
    function loadTrialBalance() {
        const month = $('#month').val();
        const year = $('#year').val();
        const monthName = $('#month option:selected').text();
        $('#displayDate').text(monthName + ' ' + year);
        $('#trialBalanceBody').html('<tr><td colspan="4" class="p-4 text-center text-gray-500">Loading...</td></tr>');

        $.post('trialbalancefunctions.php', { action: 'getTrialBalance', month, year }, function(res) {
            if (!Array.isArray(res) || res.length === 0) {
                $('#trialBalanceBody').html('<tr><td colspan="4" class="p-4 text-center text-red-500">No data found</td></tr>');
                $('#trialBalanceTotal').html('');
                return;
            }

            let rows = '';
            let totalDebit = 0, totalCredit = 0;

            res.forEach(a => {
                totalDebit += parseFloat(a.debit);
                totalCredit += parseFloat(a.credit);

                rows += `<tr class="table-row-hover">
                    <td class="p-2 border">${a.name}</td>
                    <td class="p-2 border">${a.code}</td>
                    <td class="p-2 border text-right">${parseFloat(a.debit).toFixed(2)}</td>
                    <td class="p-2 border text-right">${parseFloat(a.credit).toFixed(2)}</td>
                </tr>`;
            });

            $('#trialBalanceBody').html(rows);
            $('#trialBalanceTotal').html(`
                <tr>
                    <td colspan="2" class="p-2 border text-right">Totals</td>
                    <td class="p-2 border text-right">${totalDebit.toFixed(2)}</td>
                    <td class="p-2 border text-right">${totalCredit.toFixed(2)}</td>
                </tr>
            `);
        }, 'json');
    }

    // Auto-load on page open
    loadTrialBalance();

    // Reload button
    $('#btnLoad').click(loadTrialBalance);

    // Print button
    $('#btnPrint').click(function() {
        window.print();
    });
});

</script>
<style>
@media print {
    /* Hide header, menu, and buttons during print */
    header, 
    .sidebar, 
    .menu, 
    .header, 
    .navbar, 
    .bg-blue-900, 
    .logout-btn, 
    .btn, 
    nav,
    .no-print {
        display: none !important;
    }

    /* Expand main content to full width */
    .container, .content, body {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    /* Optional: remove background colors for clean printing */
    body {
        background: #fff !important;
    }
}
</style>

</body>
</html>
