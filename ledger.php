<?php
require_once 'ledger_functions.php';
include 'header.php';
include 'menu.php';

$accounts = getAccountsForDropdown();
?>

<!-- ✅ Add Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Print Styles - Professional Layout */
    @media print {
        /* Hide everything first */
        body * {
            visibility: hidden;
        }
        
        /* Show only ledger content */
        .ledger-display, .ledger-display * {
            visibility: visible;
        }
        
        /* Position ledger at top */
        .ledger-display {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 15mm;
        }
        
        /* Hide non-printable elements */
        .no-print {
            display: none !important;
        }
        
        /* Page setup */
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: white;
        }
        
        /* Table styling for print */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            page-break-inside: auto;
        }
        
        thead {
            display: table-header-group;
            background: #f3f4f6 !important;
        }
        
        tbody {
            display: table-row-group;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        th, td {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        
        th {
            font-weight: 600;
            background: #f3f4f6 !important;
            font-size: 9pt;
            text-transform: uppercase;
            color: #374151;
        }
        
        td {
            font-size: 10pt;
            color: #1f2937;
        }
        
        /* Right align numeric columns */
        td:nth-child(4),
        td:nth-child(5),
        td:nth-child(6) {
            text-align: right;
        }
        
        /* Header section */
        .ledger-display > div > div:first-child {
            padding: 12px 16px;
            border: 2px solid #374151;
            margin-bottom: 10px;
            background: #f9fafb !important;
        }
        
        .ledger-display h3 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            color: #111827;
        }
        
        .ledger-display > div > div:first-child p {
            font-size: 9pt;
            margin: 0;
            color: #4b5563;
        }
        
        /* Opening balance section */
        .ledger-display > div > div:nth-child(2) {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            margin-bottom: 10px;
            background: #eff6ff !important;
        }
        
        /* Footer totals section */
        .ledger-display > div > div:last-child {
            padding: 10px 16px;
            border: 2px solid #374151;
            margin-top: 10px;
            background: #f9fafb !important;
        }
        
        /* Remove shadows and rounded corners for print */
        .rounded-lg {
            border-radius: 0 !important;
        }
        
        .shadow {
            box-shadow: none !important;
        }
        
        /* Ensure proper spacing */
        .overflow-x-auto {
            overflow: visible !important;
        }
        
        /* Make fonts crisp for print */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        /* Footer totals styling */
        .ledger-display > div > div:last-child > div {
            padding: 4px 0;
        }
        
        .ledger-display > div > div:last-child span {
            font-size: 10pt;
        }
        
        .ledger-display > div > div:last-child strong {
            font-weight: 700;
        }
        
        /* Closing balance highlight */
        .ledger-display > div > div:last-child > div:last-child {
            border-top: 2px solid #374151;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .ledger-display > div > div:last-child > div:last-child span:last-child {
            font-size: 12pt;
            font-weight: 700;
            color: #111827;
        }
    }
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Header with Controls -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 no-print">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">General Ledger</h2>
            <p class="text-gray-600">View detailed transactions for any account</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <!-- Professional Print Button -->
            <button id="printProfessional" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center no-print">
                <i class="fas fa-file-pdf mr-2"></i> Professional Print
            </button>
        </div>
    </div>

    <!-- Account Selector -->
    <div class="bg-white rounded-lg shadow p-6 mb-8 no-print">
        <label for="accountSelect" class="block text-lg font-semibold text-gray-800 mb-2">Select Account</label>
        <select id="accountSelect" class="select2 w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">-- Choose an Account to View Ledger --</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?php echo $account['id']; ?>">
                    <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Ledger Display Area -->
    <div id="ledgerContent" class="ledger-display">
        <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
            <i class="fas fa-book-open text-6xl mb-4"></i>
            <p class="text-xl">Please select an account from the dropdown above to view its ledger.</p>
        </div>
    </div>
</div>

<!-- ✅ Include jQuery and Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    // Initialize Select2
    $('#accountSelect').select2({
        placeholder: "Select an account",
        allowClear: true,
        width: '100%'
    });

    const ledgerContent = $('#ledgerContent');
    let currentAccountId = null;

    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // ✅ Only ONE change event — this one!
    $('#accountSelect').on('change', function() {
        const accountId = $(this).val();
        currentAccountId = accountId;

        if (!accountId) {
            ledgerContent.html(`
                <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
                    <i class="fas fa-book-open text-6xl mb-4"></i>
                    <p class="text-xl">Please select an account from the dropdown above to view its ledger.</p>
                </div>`);
            return;
        }

        ledgerContent.html(`
            <div class="bg-white rounded-lg shadow p-12 text-center text-gray-600">
                <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                <p class="text-xl">Loading Ledger...</p>
            </div>`);

        fetch(`ledger_ajax_handler.php?account_id=${accountId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    renderLedger(result.data);
                } else {
                    ledgerContent.html(`<div class="bg-white rounded-lg shadow p-6 text-center text-red-600"><p>Error: ${result.message}</p></div>`);
                }
            })
            .catch(error => {
                console.error('Error fetching ledger:', error);
                ledgerContent.html(`<div class="bg-white rounded-lg shadow p-6 text-center text-red-600"><p>Could not load ledger data.</p></div>`);
            });
    });

    // Professional Print Functionality
    $('#printProfessional').on('click', function() {
        if (!currentAccountId) {
            alert('Please select an account first to print the professional report.');
            return;
        }
        
        // Open professional print page in new window
        const printWindow = window.open(`ledger_print.php?account_id=${currentAccountId}`, '_blank');
        
        // Focus on the new window
        if (printWindow) {
            printWindow.focus();
        }
    });

    function renderLedger(data) {
        const account = data.account;
        const transactions = data.transactions;
        const openingBalance = parseFloat(data.opening_balance);
        const totalDebits = transactions.reduce((sum, t) => sum + parseFloat(t.debit), 0);
        const totalCredits = transactions.reduce((sum, t) => sum + parseFloat(t.credit), 0);
        const closingBalance = transactions.length > 0
            ? transactions[transactions.length - 1].running_balance
            : openingBalance;

        let transactionsHtml = '';
        transactions.forEach(t => {
           transactionsHtml += `
    <tr>
        <td class="px-4 py-2 text-sm">${t.entry_date}</td>
        <td class="px-4 py-2 text-sm">${t.reference_no || ''}</td>
        <td class="px-4 py-2 text-sm">${t.description}</td>
        <td class="px-4 py-2 text-sm text-right">${parseFloat(t.debit) > 0 ? formatNumber(t.debit) : ''}</td>
        <td class="px-4 py-2 text-sm text-right">${parseFloat(t.credit) > 0 ? formatNumber(t.credit) : ''}</td>
        <td class="px-4 py-2 text-sm font-medium text-right">${formatNumber(t.running_balance)}</td>
    </tr>`;

        });

        ledgerContent.html(`
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-xl font-bold text-gray-800">Ledger for: ${account.code} - ${account.name}</h3>
                    <p class="text-sm text-gray-600">Account Type: ${account.type} | Category: ${account.category}</p>
                </div>
                <div class="px-6 py-3 border-b border-gray-200 bg-blue-50">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-700">Opening Balance:</span>
                        <span class="font-bold">Rs. ${formatNumber(openingBalance)}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                  <thead class="bg-gray-100">
    <tr>
        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ref No.</th>
        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Particulars</th>
        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
    </tr>
</thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            ${transactionsHtml || '<tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">No transactions found for this account.</td></tr>'}
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-gray-700">Totals:</span>
                        <div class="space-x-6">
                            <span class="font-medium">Total Debit: <strong>Rs. ${formatNumber(totalDebits)}</strong></span>
                            <span class="font-medium">Total Credit: <strong>Rs. ${formatNumber(totalCredits)}</strong></span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center border-t pt-2">
                        <span class="font-bold text-gray-800">Closing Balance:</span>
                        <span class="font-bold text-lg">Rs. ${formatNumber(closingBalance)}</span>
                    </div>
                </div>
            </div>`);
    }
});


</script>

<?php include 'footer.php'; ?>