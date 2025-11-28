<?php
require_once 'db_connection.php';

/**
 * Fetches all accounts for the dropdown selector.
 *
 * @return array The list of accounts.
 */
function getAccountsForDropdown(): array
{
    global $conn;
    $query = "SELECT id, code, name FROM accounts ORDER BY code";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Fetches the details for a single account.
 *
 * @param int $accountId The ID of the account.
 * @return array|null The account details or null if not found.
 */
function getAccountDetails(int $accountId): ?array
{
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM accounts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $accountId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Fetches all transactions for a given account and calculates the running balance.
 *
 * @param int $accountId The ID of the account.
 * @return array An array containing the opening balance and an array of transactions with running balances.
 */
function getAccountLedger(int $accountId): array
{
    global $conn;
    
    // 1. Get account details to find the opening balance
    $account = getAccountDetails($accountId);
    if (!$account) {
        return ['opening_balance' => 0, 'transactions' => []];
    }
    $opening_balance = (float) $account['opening_balance'];

    // 2. Get all transactions for this account, ordered by date
    $stmt = mysqli_prepare($conn, "
      SELECT 
    je.id, 
    je.entry_date, 
    je.reference_no AS reference_no,  -- ✅ Added
    je.description, 
    jd.debit, 
    jd.credit
        FROM journal_details jd
        JOIN journal_entries je ON jd.journal_id = je.id
        WHERE jd.account_id = ?
        ORDER BY je.entry_date ASC, je.id ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $accountId);
    mysqli_stmt_execute($stmt);
    $transactions_result = mysqli_stmt_get_result($stmt);
    $transactions = mysqli_fetch_all($transactions_result, MYSQLI_ASSOC);

    // 3. Calculate the running balance for each transaction
    $running_balance = $opening_balance;
    $ledger_with_balance = [];
    foreach ($transactions as $transaction) {
        $debit = (float) $transaction['debit'];
        $credit = (float) $transaction['credit'];

        // The logic for running balance depends on the account type
        // Assets & Expenses: Debit increases, Credit decreases
        // Liabilities, Equity, & Income: Credit increases, Debit decreases
        if (in_array($account['type'], ['Asset', 'Expense'])) {
            $running_balance += $debit - $credit;
        } else { // Liability, Equity, Income
            $running_balance += $credit - $debit;
        }

        $transaction['running_balance'] = $running_balance;
        $ledger_with_balance[] = $transaction;
    }

    return [
        'account' => $account,
        'opening_balance' => $opening_balance,
        'transactions' => $ledger_with_balance
    ];
}

/**
 * Fetches ledger data for a given account (wrapper function for AJAX and print)
 *
 * @param int $accountId The ID of the account.
 * @return array An array with success status and ledger data.
 */
function getLedgerData(int $accountId): array
{
    try {
        $ledgerData = getAccountLedger($accountId);
        
        if (empty($ledgerData['account'])) {
            return [
                'success' => false,
                'message' => 'Account not found'
            ];
        }
        
        return [
            'success' => true,
            'data' => $ledgerData
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error fetching ledger data: ' . $e->getMessage()
        ];
    }
}

/**
 * Alternative function to get ledger data for direct usage (without success wrapper)
 *
 * @param int $accountId The ID of the account.
 * @return array The ledger data directly.
 */
function getLedgerDataDirect(int $accountId): array
{
    return getAccountLedger($accountId);
}