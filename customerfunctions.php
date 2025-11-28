<?php
// customerfunctions.php
include 'db_connection.php';

$action = $_POST['action'] ?? '';

if ($action === 'getCustomers') {
    $q = $conn->real_escape_string($_POST['q'] ?? '');
    $sql = "SELECT CustomerID, CustomerName, account_id, Phone, Email, Address, CreatedAt FROM tblcustomers";
    if ($q !== '') {
        $safeq = $conn->real_escape_string($q);
        $sql .= " WHERE CustomerName LIKE '%$safeq%' OR Phone LIKE '%$safeq%' OR Email LIKE '%$safeq%'";
    }
    $sql .= " ORDER BY CustomerID DESC";

    $result = $conn->query($sql);
    $output = '';
    $count = 1;
    while ($row = $result->fetch_assoc()) {
        $addressEsc = htmlspecialchars($row['Address'] ?? '');
        $contactPerson = htmlspecialchars($row['ContactPerson'] ?? '');
        $output .= "
            <tr class='border-b hover:bg-gray-50'>
                <td class='p-2 border text-center'>{$count}</td>
                <td class='p-2 border'>
                    <div class='font-medium'>{$row['CustomerName']}</div>
                    <div class='text-xs text-gray-500'>{$contactPerson}</div>
                </td>
                <td class='p-2 border text-center'>{$row['Phone']}</td>
                <td class='p-2 border text-center'>{$row['Email']}</td>
                <td class='p-2 border text-center'>{$row['account_id']}</td>
                <td class='p-2 border text-center'>
                    <button class='editBtn bg-yellow-500 text-white px-2 py-1 rounded-md text-xs hover:bg-yellow-600'
                        data-id='{$row['CustomerID']}'
                        data-name=\"".htmlspecialchars($row['CustomerName'])."\"
                        data-contact=\"{$contactPerson}\"
                        data-phone=\"".htmlspecialchars($row['Phone'])."\"
                        data-email=\"".htmlspecialchars($row['Email'])."\"
                        data-address=\"{$addressEsc}\"
                    >Edit</button>
                    <button class='deleteBtn bg-red-600 text-white px-2 py-1 rounded-md text-xs hover:bg-red-700 ml-2' data-id='{$row['CustomerID']}'>Delete</button>
                </td>
            </tr>";
        $count++;
    }
    echo $output ?: "<tr><td colspan='6' class='text-center p-3 text-gray-500'>No customers found</td></tr>";
    exit;
}

if ($action === 'saveCustomer') {
    // Expecting customerData[...] in POST
    $cd = $_POST['customerData'] ?? [];
    // sanitize/normalize
    $customerData = [
        'CustomerName' => trim($cd['CustomerName'] ?? ''),
        'ContactPerson' => trim($cd['ContactPerson'] ?? ''),
        'Phone' => trim($cd['Phone'] ?? ''),
        'Email' => trim($cd['Email'] ?? ''),
        'Address' => trim($cd['Address'] ?? ''),
    ];

    // If CustomerID present -> update, else insert
    $customerID = intval($cd['CustomerID'] ?? 0);

    if ($customerID > 0) {
        // Update existing customer
        $stmt = $conn->prepare("UPDATE tblcustomers SET CustomerName=?, ContactPerson=?, Phone=?, Email=?, Address=? WHERE CustomerID=?");
        $stmt->bind_param(
            "sssssi",
            $customerData['CustomerName'],
            $customerData['ContactPerson'],
            $customerData['Phone'],
            $customerData['Email'],
            $customerData['Address'],
            $customerID
        );

        if ($stmt->execute()) {
            echo json_encode(['status'=>'success','message'=>'Customer updated successfully']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Update failed: '.$conn->error]);
        }
        exit;
    }

    // Insert new customer and create account as you specified
    $sql = "INSERT INTO tblcustomers 
            (CustomerName, ContactPerson, Phone, Email, Address, CreatedAt)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss",
        $customerData['CustomerName'],
        $customerData['ContactPerson'],
        $customerData['Phone'],
        $customerData['Email'],
        $customerData['Address']
    );

    if ($stmt->execute()) {
        $customer_id = $stmt->insert_id;

        // Step 2: Create customer account in chart of accounts
        $account_code = 'CUS-' . str_pad($customer_id, 4, '0', STR_PAD_LEFT);
        $account_name = 'Customer - ' . $customerData['CustomerName'];
        $parent_id = 3; // Accounts Receivable parent

        $sql_account = "INSERT INTO accounts (code, name, type, category, parent_id, opening_balance, balance, created_at, updated_at)
                        VALUES (?, ?, 'Asset', 'Accounts Receivable', ?, 0.00, 0.00, NOW(), NOW())";
        $stmt_acc = $conn->prepare($sql_account);
        $stmt_acc->bind_param("ssi", $account_code, $account_name, $parent_id);
        $stmt_acc->execute();

        $account_id = $stmt_acc->insert_id;

        // Step 3: Link customer with its account
        $update_customer = $conn->prepare("UPDATE tblcustomers SET account_id = ? WHERE CustomerID = ?");
        $update_customer->bind_param("ii", $account_id, $customer_id);
        $update_customer->execute();

        echo json_encode(['status' => 'success', 'message' => 'Customer saved successfully with account']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

if ($action === 'deleteCustomer') {
    $id = intval($_POST['CustomerID'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid ID']);
        exit;
    }

    // Optional: fetch account_id to consider deleting account record too (not done by default)
    $res = $conn->query("SELECT account_id FROM tblcustomers WHERE CustomerID = $id");
    $acc = $res->fetch_assoc();
    $account_id = $acc['account_id'] ?? null;

    if ($conn->query("DELETE FROM tblcustomers WHERE CustomerID=$id")) {
        // if you want to delete account too uncomment below (careful)
        // if ($account_id) $conn->query(\"DELETE FROM accounts WHERE id=\".intval($account_id));
        echo json_encode(['status'=>'success','message'=>'Customer deleted successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Delete failed: '.$conn->error]);
    }
    exit;
}

// default fallback
echo json_encode(['status'=>'error','message'=>'Invalid action']);
exit;
