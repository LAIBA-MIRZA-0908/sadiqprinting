<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
 $csrf_token = $_SESSION['csrf_token'];

require_once 'journal_functions.php';
include 'header.php';
include 'menu.php';

 $accounts = getAccountsForDropdown();
 $entries = getAllJournalEntries();
?>

<!-- Select2 CSS + jQuery added -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* small styling to match Tailwind look */
.select2-container .select2-selection--single {
    height: 40px !important;
    padding: 6px 10px !important;
    border-radius: 0.375rem !important; /* rounded-md */
    border: 1px solid #d1d5db !important;
    background: #fff;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
}
.select2-selection__arrow {
    height: 40px !important;
    right: 8px;
}
.select2-container--open { z-index: 9999; }
</style>

<style>
    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Header with Print Button -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 no-print">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Journal Entries</h2>
            <p class="text-gray-600">Record financial transactions</p>
        </div>
        <button onclick="window.print()" class="mt-4 md:mt-0 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-print mr-2"></i> Print
        </button>
    </div>

    <!-- Journal Entry Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-8 no-print">
       <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Create New Entry</h3>
        <h3 class="text-green-600 font-semibold ml-4">
            Golden Rule: “What comes in = Debit, What goes out = Credit”
        </h3>
    </div>
        <form id="journalForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="entry_date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="entry_date" name="entry_date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="reference_no" class="block text-sm font-medium text-gray-700">Reference No.</label>
                    <input type="text" id="reference_no" name="reference_no" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" id="description" name="description" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Dynamic Journal Rows -->
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Debit</th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Credit</th>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody id="journalRows">
                        <!-- Initial row will be added by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-4">
                <button type="button" id="addRowBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-plus mr-2"></i> Add Row
                </button>
                <div class="text-sm text-gray-600">
                    <span>Total Debit: <strong id="totalDebit">00</strong></span> | 
                    <span>Total Credit: <strong id="totalCredit">00</strong></span>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Save Entry
                </button>
            </div>
        </form>
    </div>

    <!-- Saved Journal Entries Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden print-area">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Saved Entries</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Debit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Credit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider no-print">Actions</th>
                    </tr>
                </thead>
                <tbody id="entriesTableBody" class="bg-white divide-y divide-gray-200">
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No journal entries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entries as $entry): ?>
                        <tr data-entry-id="<?php echo $entry['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($entry['entry_date']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($entry['reference_no']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($entry['description']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rs. <?php echo number_format($entry['total_debit'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rs. <?php echo number_format($entry['total_credit'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium no-print">
                                <button class="delete-entry-btn text-red-600 hover:text-red-900" data-id="<?php echo $entry['id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- jQuery (required by Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const journalRows = document.getElementById('journalRows');
    const addRowBtn = document.getElementById('addRowBtn');
    const journalForm = document.getElementById('journalForm');
    const entriesTableBody = document.getElementById('entriesTableBody');
    
    const accounts = <?php echo json_encode($accounts); ?>;
    
    function createAccountOptions(selectedId = null) {
        return accounts.map(acc => 
            `<option value="${acc.id}" ${acc.id == selectedId ? 'selected' : ''}>${acc.code} - ${acc.name}</option>`
        ).join('');
    }

    function addJournalRow() {
        const rowCount = journalRows.children.length;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="p-2">
                <select name="details[${rowCount}][account_id]" required class="w-full px-2 py-1 border rounded account-select">
                    <option value="">Select Account</option>
                    ${createAccountOptions()}
                </select>
            </td>
            <td class="p-2"><input type="number" step="0.01" name="details[${rowCount}][debit]" class="debit-input w-full px-2 py-1 border rounded" value="0.00"></td>
            <td class="p-2"><input type="number" step="0.01" name="details[${rowCount}][credit]" class="credit-input w-full px-2 py-1 border rounded" value="0.00"></td>
            <td class="p-2 text-center"><button type="button" class="remove-row-btn text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button></td>
        `;
        journalRows.appendChild(row);

        // Initialize Select2 on the newly added select
        $(row).find('select.account-select').select2({
            placeholder: 'Select Account',
            allowClear: true,
            width: '100%'
        });

        attachRowListeners(row);
    }

    function removeJournalRow(button) {
        // ensure we destroy select2 instance before removing row to avoid orphaned elements
        const tr = button.closest('tr');
        if (!tr) return;
        const selectEl = tr.querySelector('select.account-select');
        if (selectEl && $(selectEl).hasClass('select2-hidden-accessible')) {
            try { $(selectEl).select2('destroy'); } catch (e) { /* ignore */ }
        }

        if (journalRows.children.length > 1) {
            tr.remove();
            updateTotals();
        } else {
            alert('You must have at least one row.');
        }
    }
    
    function attachRowListeners(row) {
        // Using closest selectors because button icon might be clicked
        row.querySelector('.remove-row-btn').addEventListener('click', function(e) {
            removeJournalRow(this);
        });
        row.querySelector('.debit-input').addEventListener('input', updateTotals);
        row.querySelector('.credit-input').addEventListener('input', updateTotals);
    }

    function updateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;
        document.querySelectorAll('.debit-input').forEach(el => totalDebit += parseFloat(el.value) || 0);
        document.querySelectorAll('.credit-input').forEach(el => totalCredit += parseFloat(el.value) || 0);
        document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
        document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
    }

    // Form submission
    journalForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const totalDebit = parseFloat(document.getElementById('totalDebit').textContent);
        const totalCredit = parseFloat(document.getElementById('totalCredit').textContent);

        if (totalDebit !== totalCredit) {
            alert('Total Debit must equal Total Credit.');
            return;
        }
        
        const formData = new FormData(journalForm);
        const data = Object.fromEntries(formData.entries());
        
        // Re-structure details for the backend
        const details = [];
        document.querySelectorAll('#journalRows tr').forEach((row, index) => {
            const accountId = row.querySelector('select').value;
            const debit = row.querySelector('.debit-input').value;
            const credit = row.querySelector('.credit-input').value;
            if (accountId && (parseFloat(debit) > 0 || parseFloat(credit) > 0)) {
                 details.push({ account_id: accountId, debit, credit });
            }
        });
        data.details = JSON.stringify(details);
        data.action = 'create';

        fetch('journal_ajax_handler.php', {
            method: 'POST',
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                journalForm.reset();

                // destroy any select2 instances in rows before clearing
                document.querySelectorAll('#journalRows select.account-select').forEach(s => {
                    if ($(s).hasClass('select2-hidden-accessible')) {
                        try { $(s).select2('destroy'); } catch (e) { /* ignore */ }
                    }
                });

                journalRows.innerHTML = ''; // Clear existing rows
                addJournalRow(); // Add one fresh row
                updateTotals();
                location.reload(); // Simple way to refresh the table
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Delete entry (using event delegation)
    entriesTableBody.addEventListener('click', function(e) {
        if (e.target.closest('.delete-entry-btn')) {
            const btn = e.target.closest('.delete-entry-btn');
            const entryId = btn.dataset.id;
            if (confirm('Are you sure you want to delete this journal entry? This action cannot be undone.')) {
                fetch('journal_ajax_handler.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'delete',
                        journal_id: entryId,
                        csrf_token: '<?php echo $csrf_token; ?>'
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        btn.closest('tr').remove();
                    } else {
                        alert('Error: ' + result.message);
                    }
                });
            }
        }
    });

    // Initial setup
    addJournalRow();
    addRowBtn.addEventListener('click', addJournalRow);
    // Set today's date as default
    document.getElementById('entry_date').valueAsDate = new Date();
});
</script>

<?php include 'footer.php'; ?>
