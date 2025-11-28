<?php
include 'db_connection.php';
$action = $_POST['action'] ?? '';

if ($action === 'getSuppliers') {
    $q = $conn->real_escape_string($_POST['q'] ?? '');
    $sql = "SELECT id, name, email, phone, supplier_type, payment_terms, account_id, bank_name, bank_account_no, bank_iban, created_at FROM suppliers";
    if ($q !== '') {
        $sql .= " WHERE name LIKE '%$q%' OR phone LIKE '%$q%' OR email LIKE '%$q%'";
    }
    $sql .= " ORDER BY id DESC";
    $result = $conn->query($sql);

    $output = '';
    $count = 1;
    while ($row = $result->fetch_assoc()) {
        $output .= "
            <tr class='border-b hover:bg-gray-50'>
                <td class='p-2 border text-center'>{$count}</td>
                <td class='p-2 border'>{$row['name']}</td>
                <td class='p-2 border text-center'>{$row['supplier_type']}</td>
                <td class='p-2 border text-center'>{$row['phone']}</td>
                <td class='p-2 border text-center'>{$row['email']}</td>
                <td class='p-2 border text-center'>{$row['account_id']}</td>
                <td class='p-2 border text-center'>
                    <button class='editBtn bg-yellow-500 text-white px-2 py-1 rounded-md text-xs hover:bg-yellow-600'
                        data-id='{$row['id']}'
                        data-name=\"".htmlspecialchars($row['name'])."\"
                        data-email=\"".htmlspecialchars($row['email'])."\"
                        data-phone=\"".htmlspecialchars($row['phone'])."\"
                        data-type=\"".htmlspecialchars($row['supplier_type'])."\"
                        data-terms=\"".htmlspecialchars($row['payment_terms'])."\"
                        data-bank=\"".htmlspecialchars($row['bank_name'])."\"
                        data-account=\"".htmlspecialchars($row['bank_account_no'])."\"
                        data-iban=\"".htmlspecialchars($row['bank_iban'])."\"
                    >Edit</button>
                    <button class='deleteBtn bg-red-600 text-white px-2 py-1 rounded-md text-xs hover:bg-red-700 ml-2' data-id='{$row['id']}'>Delete</button>
                </td>
            </tr>";
        $count++;
    }
    echo $output ?: "<tr><td colspan='7' class='text-center p-3 text-gray-500'>No suppliers found</td></tr>";
    exit;
}

if ($action === 'saveSupplier') {
    $sd = $_POST['supplierData'] ?? [];
    $id = intval($sd['id'] ?? 0);
    $supplierData = [
        'name' => trim($sd['name'] ?? ''),
        'email' => trim($sd['email'] ?? ''),
        'phone' => trim($sd['phone'] ?? ''),
        'supplier_type' => trim($sd['supplier_type'] ?? ''),
        'payment_terms' => trim($sd['payment_terms'] ?? ''),
        'bank_name' => trim($sd['bank_name'] ?? ''),
        'bank_account_no' => trim($sd['bank_account_no'] ?? ''),
        'bank_iban' => trim($sd['bank_iban'] ?? '')
    ];

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE suppliers SET name=?, email=?, phone=?, supplier_type=?, payment_terms=?, bank_name=?, bank_account_no=?, bank_iban=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("ssssssssi", 
            $supplierData['name'], $supplierData['email'], $supplierData['phone'], 
            $supplierData['supplier_type'], $supplierData['payment_terms'], 
            $supplierData['bank_name'], $supplierData['bank_account_no'], $supplierData['bank_iban'], $id
        );
        $stmt->execute();
        echo json_encode(['status'=>'success','message'=>'Supplier updated successfully']);
        exit;
    }

    // Insert new supplier + create account
    $stmt = $conn->prepare("INSERT INTO suppliers (name,email,phone,supplier_type,payment_terms,bank_name,bank_account_no,bank_iban,created_at,updated_at)
                            VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())");
    $stmt->bind_param("ssssssss", 
        $supplierData['name'], $supplierData['email'], $supplierData['phone'],
        $supplierData['supplier_type'], $supplierData['payment_terms'],
        $supplierData['bank_name'], $supplierData['bank_account_no'], $supplierData['bank_iban']
    );

    if ($stmt->execute()) {
        $supplier_id = $stmt->insert_id;

        // Step 2: Create supplier account in chart of accounts
        $account_code = 'SUP-' . str_pad($supplier_id, 4, '0', STR_PAD_LEFT);
        $account_name = 'Supplier - ' . $supplierData['name'];
        $parent_id = 4; // Accounts Payable parent

        $sql_account = "INSERT INTO accounts (code, name, type, category, parent_id, opening_balance, balance, created_at, updated_at)
                        VALUES (?, ?, 'Liability', 'Accounts Payable', ?, 0.00, 0.00, NOW(), NOW())";
        $stmt_acc = $conn->prepare($sql_account);
        $stmt_acc->bind_param("ssi", $account_code, $account_name, $parent_id);
        $stmt_acc->execute();
        $account_id = $stmt_acc->insert_id;

        // Step 3: Link supplier with its account
        $update = $conn->prepare("UPDATE suppliers SET account_id=? WHERE id=?");
        $update->bind_param("ii", $account_id, $supplier_id);
        $update->execute();

        echo json_encode(['status'=>'success','message'=>'Supplier saved successfully with account']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Database error: '.$conn->error]);
    }
    exit;
}

if ($action === 'deleteSupplier') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }

    $conn->query("DELETE FROM suppliers WHERE id=$id");
    echo json_encode(['status'=>'success','message'=>'Supplier deleted successfully']);
    exit;
}

echo json_encode(['status'=>'error','message'=>'Invalid action']);
exit;
