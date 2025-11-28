<?php
require_once 'ledger_functions.php';

// Get account ID from query parameter
$account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : 0;

if (!$account_id) {
    die("Invalid account ID");
}

// Fetch ledger data
$ledger_data = getLedgerData($account_id);

if (!$ledger_data || !$ledger_data['success']) {
    die("Could not load ledger data");
}

$data = $ledger_data['data'];
$account = $data['account'];
$transactions = $data['transactions'];
$opening_balance = floatval($data['opening_balance']);

// Calculate totals
$total_debits = 0;
$total_credits = 0;
foreach ($transactions as $t) {
    $total_debits += floatval($t['debit']);
    $total_credits += floatval($t['credit']);
}

$closing_balance = count($transactions) > 0 
    ? floatval($transactions[count($transactions)-1]['running_balance']) 
    : $opening_balance;

// Format number function
function formatNumber($num) {
    return number_format(floatval($num), 2, '.', ',');
}

// Company information
$company_name = "Al-Sadiq Printing";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger Report - <?php echo htmlspecialchars($account['code'] . ' - ' . $account['name']); ?></title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000000;
            background: white;
            padding: 0;
            margin: 0;
        }

        /* Page setup */
        @page {
            size: A4;
            margin: 15mm;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .print-container {
                margin: 0;
                padding: 0;
            }
        }

        /* Container */
        .print-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        /* Header styles */
        .report-header {
            text-align: center;
            border-bottom: 3px double #000000;
            padding-bottom: 15px;
            margin-bottom: 20px;
            background: white;
            color: #000000;
            padding: 20px;
        }

        .company-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #000000;
            font-weight: bold;
            color: #000000;
            font-size: 14px;
            text-align: center;
            padding: 10px;
        }

        .company-details {
            text-align: center;
        }

        .company-name {
            font-size: 28pt;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 14pt;
            color: #000000;
            font-style: italic;
        }

        .report-title {
            font-size: 20pt;
            font-weight: bold;
            color: #000000;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Account info section */
        .account-info {
            background: #ffffff;
            border: 2px solid #000000;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000000;
            padding: 8px 0;
        }

        .info-label {
            font-weight: 700;
            color: #000000;
            font-size: 11pt;
        }

        .info-value {
            color: #000000;
            font-weight: 600;
        }

        /* Opening Balance */
        .opening-balance {
            background: #ffffff;
            color: #000000;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 5px solid #000000;
            border: 2px solid #000000;
            font-weight: bold;
            font-size: 12pt;
        }

        /* Table styles - Professional Black Theme */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
            border: 2px solid #000000;
        }

        .ledger-table th {
            background: #000000;
            color: white;
            font-weight: 700;
            text-align: left;
            padding: 12px 10px;
            border: 1px solid #000000;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 0.5px;
        }

        .ledger-table td {
            padding: 10px;
            border: 1px solid #000000;
            vertical-align: top;
            background: white;
            color: #000000;
        }

        .ledger-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        /* Alignment helpers */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        /* Amount formatting */
        .amount {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .debit {
            color: #000000;
            font-weight: 700;
        }

        .credit {
            color: #000000;
            font-weight: 700;
        }

        .balance {
            font-weight: 700;
            color: #000000;
        }

        /* Summary section */
        .summary-section {
            margin-top: 30px;
            border-top: 3px solid #000000;
            padding-top: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: 2px solid #000000;
            border-radius: 4px;
            padding: 15px;
            background: #ffffff;
        }

        .summary-title {
            font-size: 11pt;
            font-weight: 700;
            color: #000000;
            margin-bottom: 10px;
            border-bottom: 2px solid #000000;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            color: #000000;
        }

        .closing-balance {
            background: #000000;
            color: white;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
            border: 2px solid #000000;
        }

        .closing-balance .label {
            font-size: 14pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .closing-balance .value {
            font-size: 20pt;
            font-weight: bold;
            color: white;
        }

        /* Print-specific styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                font-size: 10pt;
                margin: 0;
                padding: 0;
                color: #000000;
                background: white;
            }
            
            .print-container {
                margin: 0;
                padding: 0;
            }
            
            .report-header {
                margin-bottom: 15px;
                padding: 15px;
                background: white;
                color: #000000;
            }
            
            .company-name {
                font-size: 24pt;
                color: #000000;
            }
            
            .report-title {
                font-size: 18pt;
                color: #000000;
            }
            
            .ledger-table {
                font-size: 9pt;
            }
            
            .ledger-table th,
            .ledger-table td {
                padding: 8px 6px;
            }
            
            .summary-value {
                font-size: 12pt;
            }
            
            .closing-balance .value {
                font-size: 18pt;
            }

            /* Ensure table breaks properly across pages */
            .ledger-table {
                page-break-inside: auto;
            }
            
            .ledger-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            .ledger-table thead {
                display: table-header-group;
            }
            
            .ledger-table tbody {
                display: table-row-group;
            }

            /* Colors for print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        /* Footer */
        .report-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000000;
            font-size: 9pt;
            color: #000000;
            text-align: center;
        }

        .print-date {
            margin-bottom: 8px;
            font-weight: 600;
        }

        .page-number:after {
            content: "Page " counter(page);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Report Header -->
        <div class="report-header">
            <div class="company-info">
                <div class="company-logo" style="display: none;">
                    
                </div>
                <div class="company-details">
                    <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
                </div>
            </div>
            <div class="report-title">General Ledger Report</div>
        </div>

        <!-- Account Information -->
        <div class="account-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Account Code:</span>
                    <span class="info-value"><?php echo htmlspecialchars($account['code']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($account['name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Type:</span>
                    <span class="info-value"><?php echo htmlspecialchars($account['type']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Category:</span>
                    <span class="info-value"><?php echo htmlspecialchars($account['category']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Report Period:</span>
                    <span class="info-value">Up to <?php echo date('F j, Y'); ?></span>
                </div>
            </div>
        </div>

        <!-- Opening Balance -->
        <div class="opening-balance">
            <strong>Opening Balance:</strong> Rs. <?php echo formatNumber($opening_balance); ?>
        </div>

        <!-- Ledger Table -->
        <table class="ledger-table">
            <thead>
                <tr>
                    <th width="12%">Date</th>
                    <th width="15%">Reference No.</th>
                    <th width="35%">Particulars</th>
                    <th width="12%" class="text-right">Debit (Rs.)</th>
                    <th width="12%" class="text-right">Credit (Rs.)</th>
                    <th width="14%" class="text-right">Balance (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['entry_date']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['reference_no'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                            <td class="text-right amount <?php echo floatval($transaction['debit']) > 0 ? 'debit' : ''; ?>">
                                <?php echo floatval($transaction['debit']) > 0 ? formatNumber($transaction['debit']) : ''; ?>
                            </td>
                            <td class="text-right amount <?php echo floatval($transaction['credit']) > 0 ? 'credit' : ''; ?>">
                                <?php echo floatval($transaction['credit']) > 0 ? formatNumber($transaction['credit']) : ''; ?>
                            </td>
                            <td class="text-right amount balance">
                                <?php echo formatNumber($transaction['running_balance']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 20px; color: #000000; font-style: italic;">
                            No transactions found for this account.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-title">Total Debits</div>
                    <div class="summary-value debit">Rs. <?php echo formatNumber($total_debits); ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-title">Total Credits</div>
                    <div class="summary-value credit">Rs. <?php echo formatNumber($total_credits); ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-title">Net Movement</div>
                    <div class="summary-value">
                        Rs. <?php echo formatNumber(abs($total_debits - $total_credits)); ?>
                        (<?php echo ($total_debits - $total_credits) >= 0 ? 'Debit' : 'Credit'; ?>)
                    </div>
                </div>
            </div>

            <div class="closing-balance">
                <div class="label">Closing Balance</div>
                <div class="value">Rs. <?php echo formatNumber($closing_balance); ?></div>
            </div>
        </div>

       

    <!-- Print automatically when page loads -->
    <script>
        window.onload = function() {
            window.print();
            
            // Close window after printing
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>