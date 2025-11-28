<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header("Content-Type: application/json");

// DB connection
require_once "db_connection.php";

function send($success, $msg = "", $extra = [])
{
    echo json_encode(array_merge([
        "success" => $success,
        "message" => $msg
    ], $extra));

    exit;
}


// --------------------------------------------
// VALIDATE REQUEST
// --------------------------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send(false, "Invalid request method.");
}

if (!isset($_POST["JobOrderNo"])) {
    send(false, "Missing Job Order Number.");
}

$JobOrderNo = intval($_POST["JobOrderNo"]);


// --------------------------------------------
// COLLECT HEADER FIELDS
// --------------------------------------------
$OrderDate      = $_POST["OrderDate"] ?? "";
$DeliveryDate   = $_POST["DeliveryDate"] ?? "";
$CustomerName   = trim($_POST["CustomerName"] ?? "");
$CellNo         = trim($_POST["CellNo"] ?? "");
$DesignBy       = trim($_POST["DesignBy"] ?? "");
$JobFor         = trim($_POST["JobFor"] ?? "");
$Designer       = trim($_POST["Designer"] ?? "");
$AdvancePayment = floatval($_POST["AdvancePayment"] ?? 0);

// --------------------------------------------
// ORDER DETAIL ARRAYS
// --------------------------------------------
$Detail  = $_POST["Detail"] ?? [];
$Media   = $_POST["Media"] ?? [];
$Width   = $_POST["Width"] ?? [];
$Height  = $_POST["Height"] ?? [];
$Qty     = $_POST["Qty"] ?? [];
$Sqft    = $_POST["Sqft"] ?? [];
$Ring    = $_POST["Ring"] ?? [];
$Pocket  = $_POST["Pocket"] ?? [];

// BASIC VALIDATION
if (empty($Detail) || count($Detail) == 0) {
    send(false, "At least one detail row is required.");
}


// --------------------------------------------
// START TRANSACTION
// --------------------------------------------
$conn->begin_transaction();

try {

    // --------------------------------------------
    // 1) UPDATE HEADER
    // --------------------------------------------
    $updateHeader = $conn->prepare("
        UPDATE job_orders
        SET 
            OrderDate = ?, 
            DeliveryDate = ?, 
            DesignBy = ?, 
            JobFor = ?, 
            CustomerName = ?, 
            CellNo = ?, 
            Designer = ?, 
            AdvancePayment = ?
        WHERE JobOrderNo = ?
    ");

    if (!$updateHeader) {
        throw new Exception("Query error: " . $conn->error);
    }

    $updateHeader->bind_param(
        "sssssssdi",
        $OrderDate,
        $DeliveryDate,
        $DesignBy,
        $JobFor,
        $CustomerName,
        $CellNo,
        $Designer,
        $AdvancePayment,
        $JobOrderNo
    );

    if (!$updateHeader->execute()) {
        throw new Exception("Update failed: " . $updateHeader->error);
    }


    // --------------------------------------------
    // 2) DELETE ALL OLD DETAILS
    // --------------------------------------------
    $deleteDetails = $conn->prepare("DELETE FROM job_order_details WHERE JobOrderNo = ?");
    $deleteDetails->bind_param("i", $JobOrderNo);
    $deleteDetails->execute();


    // --------------------------------------------
    // 3) INSERT NEW DETAIL ROWS
    // --------------------------------------------
    $insert = $conn->prepare("
        INSERT INTO job_order_details 
        (JobOrderNo, SrNo, Detail, Media, Width, Height, Qty, Sqft, Ring, Pocket)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$insert) {
        throw new Exception("Insert prepare failed: " . $conn->error);
    }

    for ($i = 0; $i < count($Detail); $i++) {

        // Clean values
        $dDetail = trim($Detail[$i]);
        if ($dDetail == "") continue;  // skip empty rows

        $dMedia  = trim($Media[$i]);
        $dWidth  = floatval($Width[$i]);
        $dHeight = floatval($Height[$i]);
        $dQty    = intval($Qty[$i]);
        $dSqft   = floatval($Sqft[$i]);
        $dRing   = isset($Ring[$i]) ? 1 : 0;
        $dPocket = isset($Pocket[$i]) ? 1 : 0;

        $srNo = $i + 1;

        $insert->bind_param(
            "iisssddiii",
            $JobOrderNo,
            $srNo,
            $dDetail,
            $dMedia,
            $dWidth,
            $dHeight,
            $dQty,
            $dSqft,
            $dRing,
            $dPocket
        );

        if (!$insert->execute()) {
            throw new Exception("Insert error: " . $insert->error);
        }
    }


    // --------------------------------------------
    // COMMIT SUCCESS
    // --------------------------------------------
    $conn->commit();

    send(true, "Job order updated successfully.");

} catch (Exception $e) {

    // ROLLBACK ON FAILURE
    $conn->rollback();
    send(false, "Update failed: " . $e->getMessage());
}
?>
