<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php';

$accountID = intval($_POST['account_id']);

if ($accountID == 0) {
    echo "<p style='color:red;'>Invalid Account ID.</p>";
    exit;
}

// DEBUG
echo "<p style='color:blue;'>DEBUG: Fetching ledger for Account ID = $accountID</p>";

$query = "
    SELECT 
        je.entry_date,
        je.description,
        jd.debit,
        jd.credit
    FROM journal_details jd
    INNER JOIN journal_entries je ON jd.journal_id = je.id
    WHERE jd.account_id = $accountID
    ORDER BY je.entry_date ASC
";

$result = $conn->query($query);

if (!$result) {
    echo "<p style='color:red;'>QUERY ERROR: " . $conn->error . "</p>";
    exit;
}

echo "<table class='table table-bordered'>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>";

$totalDebit = 0;
$totalCredit = 0;

while ($row = $result->fetch_assoc()) {

    $totalDebit += $row['debit'];
    $totalCredit += $row['credit'];

    echo "<tr>
            <td>{$row['entry_date']}</td>
            <td>{$row['description']}</td>
            <td>{$row['debit']}</td>
            <td>{$row['credit']}</td>
          </tr>";
}

echo "</tbody>
      <tfoot>
        <tr style='font-weight:bold;'>
            <td colspan='2'>Total</td>
            <td>$totalDebit</td>
            <td>$totalCredit</td>
        </tr>
      </tfoot>
     </table>";
?>
