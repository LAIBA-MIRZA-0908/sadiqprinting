<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
 
include 'db_connection.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($_POST['action'] == 'getTrialBalance') {
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];

    $sql = "
    SELECT 
        a.id,
        a.code,
        a.name,
        a.opening_balance,
        IFNULL(SUM(jd.debit),0) AS debit,
        IFNULL(SUM(jd.credit),0) AS credit
    FROM accounts a
    LEFT JOIN journal_details jd
        ON a.id = jd.account_id
    LEFT JOIN journal_entries je
        ON jd.journal_id = je.id
        AND MONTH(je.entry_date) = ?
        AND YEAR(je.entry_date) = ?
    GROUP BY a.id, a.code, a.name, a.opening_balance
    ORDER BY a.code ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}
echo json_encode([]);
?>
