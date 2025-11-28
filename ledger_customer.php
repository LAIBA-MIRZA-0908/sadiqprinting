<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'header.php';
include 'menu.php';
 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Ledger Report</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            #printBtn { display: none; }
            .no-print { display: none; }
            body { background: white !important; }

            /* Hide header/menu for printing */
            header, nav, .menu, #topbar { 
                display: none !important;
            }

            /* Make content full width */
            .max-w-7xl { max-width: 100% !important; margin: 0; }
            .shadow, .rounded { box-shadow: none !important; border-radius: 0 !important; }
            .bg-white { background: white !important; }
        }
    </style>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 bg-white p-6 shadow rounded">

    <h1 class="text-2xl font-bold mb-4">📘 Customer Ledger Report</h1>

    <button id="printBtn" onclick="window.print()" 
        class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 no-print">
        🖨 Print Report
    </button>

    <?php
    // Fetch all customers
    $customers = $conn->query("SELECT * FROM tblcustomers ORDER BY CustomerName ASC");

    while ($c = $customers->fetch_assoc()) {

        $account_id = $c['account_id'];

        echo "<div class='mt-10'>";
        echo "<h2 class='text-xl font-bold border-b pb-2 text-blue-700'>{$c['CustomerName']} (Account: {$account_id})</h2>";
        echo "<p class='text-sm text-gray-600 mb-2'>Phone: {$c['Phone']} | Email: {$c['Email']}</p>";

        // Fetch ledger records linked with this customer
        $sql = "
            SELECT 
                je.entry_date,
                je.description,
                jd.debit,
                jd.credit
            FROM journal_details jd
            INNER JOIN journal_entries je ON jd.journal_id = je.id
            WHERE jd.account_id = $account_id
            ORDER BY je.entry_date ASC, jd.id ASC
        ";

        $ledger = $conn->query($sql);

        echo "
        <table class='w-full text-sm border mt-3'>
            <thead class='bg-gray-200'>
                <tr>
                    <th class='border p-2'>Date</th>
                    <th class='border p-2 text-left'>Description</th>
                    <th class='border p-2 text-right'>Debit</th>
                    <th class='border p-2 text-right'>Credit</th>
                    <th class='border p-2 text-right'>Balance</th>
                </tr>
            </thead>
            <tbody>
        ";

        $balance = 0;
        while ($row = $ledger->fetch_assoc()) {
            $balance += $row['debit'] - $row['credit'];

            echo "
                <tr>
                    <td class='border p-2'>" . $row['entry_date'] . "</td>
                    <td class='border p-2'>" . $row['description'] . "</td>
                    <td class='border p-2 text-right'>" . number_format($row['debit'], 2) . "</td>
                    <td class='border p-2 text-right'>" . number_format($row['credit'], 2) . "</td>
                    <td class='border p-2 text-right font-semibold'>" . number_format($balance, 2) . "</td>
                </tr>
            ";
        }

        echo "</tbody></table>";

        echo "
            <p class='text-right mt-2 font-bold text-lg'>
                Final Balance: " . number_format($balance, 2) . "
            </p>
        ";

        echo "</div>";
    }

    ?>

</div>

</body>
</html>
