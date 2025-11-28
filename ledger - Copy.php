<?php
require_once 'ledger_functions.php';
include 'header.php';
include 'menu.php';

$accounts = getAccountsForDropdown();
?>

<!-- ✅ Add Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        .ledger-display, .ledger-display * {
            visibility: visible;
        }
        .ledger-display {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
        }
        .no-print {
            display: none !important;
        }
        tr {
            page-break-inside: avoid;
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
            <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-print mr-2"></i> Print
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
    // ✅ Initialize Select2 for single select with search
    $('#accountSelect').select2({
        placeholder: "Select an account",
        allowClear: true,
        width: '100%'
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accountSelect = document.getElementById('accountSelect');
    const ledgerContent = document.getElementById('ledgerContent');

    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    accountSelect.addEventListener('change', function() {
        const accountId = this.value;
        if (!accountId) {
            ledgerContent.innerHTML = `
                <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
                    <i class="fas fa-book-open text-6xl mb-4"></i>
                    <p class="text-xl">Please select an account from the dropdown above to view its ledger.</p>
                </div>`;
            return;
        }

        ledgerContent.innerHTML = `
            <div class="bg-white rounded-lg shadow p-12 text-center text-gray-600">
                <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                <p class="text-xl">Loading Ledger...</p>
            </div>`;

        fetch(`ledger_ajax_handler.php?account_id=${accountId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    renderLedger(result.data);
                } else {
                    ledgerContent.innerHTML = `<div class="bg-white rounded-lg shadow p-6 text-center text-red-600"><p>Error: ${result.message}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching ledger:', error);
                ledgerContent.innerHTML = `<div class="bg-white rounded-lg shadow p-6 text-center text-red-600"><p>Could not load ledger data.</p></div>`;
            });
    });

    function renderLedger(data) {
        const account = data.account;
        const transactions = data.transactions;
        const openingBalance = parseFloat(data.opening_balance);
        const totalDebits = transactions.reduce((sum, t) => sum + parseFloat(t.debit), 0);
        const totalCredits = transactions.reduce((sum, t) => sum + parseFloat(t.credit), 0);
        const closingBalance = transactions.length > 0 ? transactions[transactions.length - 1].running_balance : openingBalance;

        let transactionsHtml = '';
        transactions.forEach(t => {
            transactionsHtml += `
                <tr>
                    <td class="px-4 py-2 text-sm">${t.entry_date}</td>
                    <td class="px-4 py-2 text-sm">${t.description}</td>
                    <td class="px-4 py-2 text-sm text-right">${parseFloat(t.debit) > 0 ? formatNumber(t.debit) : ''}</td>
                    <td class="px-4 py-2 text-sm text-right">${parseFloat(t.credit) > 0 ? formatNumber(t.credit) : ''}</td>
                    <td class="px-4 py-2 text-sm font-medium text-right">${formatNumber(t.running_balance)}</td>
                </tr>`;
        });

        ledgerContent.innerHTML = `
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
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Particulars</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${transactionsHtml || '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No transactions found for this account.</td></tr>'}
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
            </div>`;
    }
});
</script>

<?php include 'footer.php'; ?>
