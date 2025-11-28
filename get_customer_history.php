<?php
include 'db_connection.php';

if (isset($_POST['CustomerID'])) {
    $CustomerID = intval($_POST['CustomerID']);

    // Get customer's account_id
    $stmt = $conn->prepare("SELECT account_id FROM tblcustomers WHERE CustomerID=?");
    $stmt->bind_param("i", $CustomerID);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $AccountID = $result['account_id'];

    // Calculate balance
    $balance_sql = "SELECT 
                        SUM(debit) AS total_debit,
                        SUM(credit) AS total_credit
                    FROM journal_details
                    WHERE account_id = ?";
    $stmt = $conn->prepare($balance_sql);
    $stmt->bind_param("i", $AccountID);
    $stmt->execute();
    $bal = $stmt->get_result()->fetch_assoc();

    $total_debit = $bal['total_debit'] ?? 0;
    $total_credit = $bal['total_credit'] ?? 0;
    $closing_balance = $total_debit - $total_credit;
    $balance_type = $closing_balance >= 0 ? "Debit" : "Credit";

    // Fetch journal history
    $history_sql = "
        SELECT je.entry_date, je.description, jd.debit, jd.credit
        FROM journal_details jd
        JOIN journal_entries je ON jd.journal_id = je.id
        WHERE jd.account_id = ?
        ORDER BY je.entry_date DESC, je.id DESC
        LIMIT 20";
    $stmt = $conn->prepare($history_sql);
    $stmt->bind_param("i", $AccountID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare output
    $rows = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows .= "<tr>
                        <td class='border p-2'>{$row['entry_date']}</td>
                        <td class='border p-2'>{$row['description']}</td>
                        <td class='border p-2 text-right'>" . number_format($row['debit'], 2) . "</td>
                        <td class='border p-2 text-right'>" . number_format($row['credit'], 2) . "</td>
                      </tr>";
        }
    } else {
        $rows = "<tr><td colspan='4' class='text-center p-3 text-gray-500'>No transactions found.</td></tr>";
    }

    // Return both balance and table as JSON
    echo json_encode([
        'balance' => number_format(abs($closing_balance), 2) . ' ' . $balance_type,
        'rows' => $rows
    ]);
}
?>
