<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include 'header.php';
include 'menu.php';

$invoiceId = $_GET['id'] ?? 0;
if ($invoiceId <= 0) {
    die("Invalid Invoice ID");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .numeric-input { text-align: right; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .select2-container { width: 100% !important; }
    </style>
</head>

<body class="bg-gray-100">

<div class="container mx-auto px-4 py-6">

    <!-- HEADER -->
    <div class="bg-yellow-600 text-white rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Edit Invoice</h1>
            <a href="invoices_list.php"
                class="bg-white text-yellow-700 px-4 py-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-list mr-2"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Invoice Form -->
    <form id="invoiceForm">

        <input type="hidden" id="InvoiceID" value="<?= $invoiceId ?>">

        <!-- Invoice Header Block -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <div>
                    <label class="text-sm font-semibold">Invoice No</label>
                    <input type="text" id="InvoiceNo" class="w-full border p-2 rounded bg-gray-200" readonly>
                </div>

                <div>
                    <label class="text-sm font-semibold">Date</label>
                    <input type="date" id="InvoiceDate" class="w-full border p-2 rounded">
                </div>

                <div style="display:none;">
                    <label class="text-sm font-semibold">Customer</label>
                    <select id="CustomerID" class="w-full border p-2 rounded"></select>
                </div>

                <div>
                    <label class="text-sm font-semibold">Subject</label>
                    <input type="text" id="InvoiceSubject" class="w-full border p-2 rounded">
                </div>

                <div>
    <label class="text-sm font-semibold">PO Number</label>
    <input type="text" id="PONumber" class="w-full border p-2 rounded">
</div>

<div>
    <label class="text-sm font-semibold">Quotation No</label>
    <input type="text" id="QuotationNo" class="w-full border p-2 rounded">
</div>


            </div>
        </div>

        <!-- ITEMS -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">

            <div class="flex justify-between mb-3">
                <h2 class="text-lg font-semibold">Invoice Items</h2>
                <button type="button" onclick="addInvoiceRow()"
                        class="bg-green-600 text-white px-3 py-1 rounded">
                    <i class="fas fa-plus"></i> Add Row
                </button>
            </div>

            <div class="table-responsive">
                <table class="min-w-full border" id="invoiceTable">
                    <thead class="bg-gray-200 text-xs uppercase">
                        <tr>
                            <th class="px-2 py-1">Job#</th>
                            <th class="px-2 py-1">Detail</th>
                            <th class="px-2 py-1">Media</th>
                            <th class="px-2 py-1">W</th>
                            <th class="px-2 py-1">H</th>
                            <th class="px-2 py-1">Qty</th>
                            <th class="px-2 py-1">Sqft</th>
                            <th class="px-2 py-1">Rate</th>
                            <th class="px-2 py-1">Total</th>
                            <th class="px-2 py-1">Action</th>
                        </tr>
                    </thead>

                    <tbody id="invoiceTableBody"></tbody>

                </table>
            </div>

        </div>

        <!-- SUMMARY -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h2 class="text-lg font-semibold mb-3">Summary</h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                <div>
                    <label class="text-sm font-semibold">Sub Total</label>
                    <input type="number" id="SubTotal" readonly class="w-full border p-2 rounded bg-gray-200 text-right">
                </div>

                <div>
                    <label class="text-sm font-semibold">Advance</label>
                    <input type="number" id="Advance" class="w-full border p-2 rounded text-right">
                </div>

                <div>
                    <label class="text-sm font-semibold">GST %</label>
                    <input type="number" id="GSTRate" class="w-full border p-2 rounded text-right">
                </div>

                <div>
                    <label class="text-sm font-semibold">GST Total</label>
                    <input type="number" id="TotalGST" readonly class="w-full border p-2 rounded bg-gray-200 text-right">
                </div>

                <div>
                    <label class="text-sm font-semibold">NTN %</label>
                    <input type="number" id="NTRRate" class="w-full border p-2 rounded text-right">
                </div>

                <div>
                    <label class="text-sm font-semibold">NTN Total</label>
                    <input type="number" id="TotalNTR" readonly class="w-full border p-2 rounded bg-gray-200 text-right">
                </div>

                <div>
                    <label class="text-sm font-semibold font-bold">Grand Total</label>
                    <input type="number" id="GrandTotal" readonly class="w-full border p-2 rounded bg-gray-200 text-right font-bold">
                </div>

            </div>
        </div>

        <!-- BUTTONS -->
        <div class="flex justify-end gap-3">
            <a href="invoices_list.php"
               class="bg-gray-300 px-4 py-2 rounded-lg">Cancel</a>

            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-save mr-2"></i> Update Invoice
            </button>
        </div>

    </form>

</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>

let rowCounter = 0;
let materials = [];
let customers = [];

// INIT
$(document).ready(function () {
     loadCustomers();

    // First load materials
    loadMaterials(function() {
        // After materials loaded, load invoice
        loadInvoice();
    });

    $('#invoiceForm').submit(function(e){
        e.preventDefault();
        updateInvoice();
    });

    $('#GSTRate,#NTRRate,#Advance').on('input', computeTotals);

      
});


// LOAD INVOICE DATA
function loadInvoice() {
    $.post("invoice_functions.php",
        { action: "get_invoice_details", invoiceId: $("#InvoiceID").val() },
        function(resp) {

            if (!resp.success) return alert("Error loading invoice");

            const inv = resp.invoice;
            const items = resp.items;

            $("#InvoiceNo").val(inv.InvoiceNo);
            $("#InvoiceDate").val(inv.InvoiceDate);
            $("#InvoiceSubject").val(inv.InvoiceSubject);
            $("#PONumber").val(inv.PONumber);
            $("#QuotationNo").val(inv.QuotationNo);

            $("#Advance").val(inv.Advance);
            $("#GSTRate").val(inv.GSTRate);
            $("#TotalGST").val(inv.TotalGST);
            $("#NTRRate").val(inv.NTRRate);
            $("#TotalNTR").val(inv.TotalNTR);
            $("#GrandTotal").val(inv.GrandTotal);

            // After customers loaded, set dropdown value
            setTimeout(() => {
                $("#CustomerID").val(inv.CustomerID).trigger("change");
            }, 400);

            $("#invoiceTableBody").empty();

            items.forEach(row => {
                addInvoiceRow(row);
            });

            computeTotals();
        },
        "json"
    );
}



// LOAD CUSTOMERS
function loadCustomers() {
    $.post("invoice_functions.php", { action: "get_customers" }, function (r) {
        if (r.success) {
            customers = r.customers;
            $("#CustomerID").html('<option value="">Select Customer</option>');
            customers.forEach(c => {
                $("#CustomerID").append(`<option value="${c.CustomerID}">${c.CustomerName}</option>`);
            });
              $("#CustomerID").select2({
                placeholder: "Select Customer",
                allowClear: true,
                width: "100%"
            });
        }
    }, "json");
}

// LOAD MATERIALS
function loadMaterials(callback) {
    $.post("invoice_functions.php", { action: "get_materials" }, function (r) {
        if (r.success) {
            materials = r.materials;
        }
        if (callback) callback();
    }, "json");
}



// ADD ITEM ROW
function addInvoiceRow(data = null) {
    rowCounter++;

    let detail = data ? data.Detail : "";
    let job = data ? data.JobNo : "";
    let media = data ? data.Media : "";
    let width = data ? data.Width : "";
    let height = data ? data.Height : "";
    let qty = data ? data.Qty : "";
    let sqft = data ? data.Sqft : "";
    let rate = data ? data.Rate : "";
    let total = data ? data.Total : "";

    let row = `
<tr id="row-${rowCounter}">
    <td><input class="job-no w-16 border p-1" value="${job}"></td>
    <td><input class="detail-input w-full border p-1" value="${detail}"></td>
 <td>
    <select class="media-select w-full border p-1">
        <option value="">Select</option>
        ${materials.map(m => `
            <option value="${m.name}" ${media == m.name ? "selected" : ""}>
                ${m.name}
            </option>
        `).join('')}
    </select>
</td>

    <td><input class="width-input w-20 border p-1 text-right" value="${width}" oninput="computeRow(${rowCounter})"></td>
    <td><input class="height-input w-20 border p-1 text-right" value="${height}" oninput="computeRow(${rowCounter})"></td>
    <td><input class="qty-input w-16 border p-1 text-right" value="${qty}" oninput="computeRow(${rowCounter})"></td>
    <td><input class="sqft-input w-20 border p-1 text-right bg-gray-100" value="${sqft}" readonly></td>
    <td><input class="rate-input w-20 border p-1 text-right" value="${rate}" oninput="computeRow(${rowCounter})"></td>
    <td><input class="total-input w-24 border p-1 text-right bg-gray-100" value="${total}" readonly></td>

    <td>
        <button onclick="removeRow(${rowCounter})"
                class="text-red-600 hover:text-red-800">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>`;

    $("#invoiceTableBody").append(row);
}



// REMOVE ROW
function removeRow(r) {
    $("#row-" + r).remove();
    computeTotals();
}


// COMPUTE SINGLE ROW
function computeRow(r) {
    let row = $("#row-" + r);

    let width = parseFloat(row.find(".width-input").val()) || 0;
    let height = parseFloat(row.find(".height-input").val()) || 0;
    let qty = parseFloat(row.find(".qty-input").val()) || 0;
    let rate = parseFloat(row.find(".rate-input").val()) || 0;

    let sqft = width * height * qty;
    let total = sqft * rate;

    row.find(".sqft-input").val(sqft.toFixed(2));
    row.find(".total-input").val(total.toFixed(2));

    computeTotals();
}


// COMPUTE TOTALS
function computeTotals() {
    let subtotal = 0;

    $(".total-input").each(function () {
        subtotal += parseFloat($(this).val()) || 0;
    });

    let advance = parseFloat($("#Advance").val()) || 0;
    let gstRate = parseFloat($("#GSTRate").val()) || 0;
    let ntrRate = parseFloat($("#NTRRate").val()) || 0;

    let gstAmt = subtotal * (gstRate / 100);
    let ntrAmt = subtotal * (ntrRate / 100);

    let grand = subtotal + gstAmt + ntrAmt - advance;

    $("#SubTotal").val(subtotal.toFixed(2));
    $("#TotalGST").val(gstAmt.toFixed(2));
    $("#TotalNTR").val(ntrAmt.toFixed(2));
    $("#GrandTotal").val(grand.toFixed(2));
}


// SAVE UPDATED INVOICE
function updateInvoice() {

    let items = [];

    $("#invoiceTableBody tr").each(function () {
        let row = $(this);

        let detail = row.find(".detail-input").val();
        let job = row.find(".job-no").val();
        let total = parseFloat(row.find(".total-input").val()) || 0;

        if (detail || total > 0) {
            items.push({
                JobNo: job,
                Detail: detail,
                Media: row.find(".media-select").val(),
                Width: row.find(".width-input").val(),
                Height: row.find(".height-input").val(),
                Qty: row.find(".qty-input").val(),
                Sqft: row.find(".sqft-input").val(),
                Rate: row.find(".rate-input").val(),
                Total: row.find(".total-input").val()
            });
        }
    });

    $.post("invoice_functions.php", {
        action: "update_invoice",
        InvoiceID: $("#InvoiceID").val(),
        InvoiceDate: $("#InvoiceDate").val(),
        CustomerID: $("#CustomerID").val(),
        CustomerName: $("#CustomerID option:selected").text(),
        InvoiceSubject: $("#InvoiceSubject").val(),
        PONumber: $("#PONumber").val(),
        QuotationNo: $("#QuotationNo").val(),

        SubTotal: $("#SubTotal").val(),
        Advance: $("#Advance").val(),
        GSTRate: $("#GSTRate").val(),
        TotalGST: $("#TotalGST").val(),
        NTRRate: $("#NTRRate").val(),
        TotalNTR: $("#TotalNTR").val(),
        GrandTotal: $("#GrandTotal").val(),
        items: items
    },
    function (resp) {
        if (resp.success) {
            alert("Invoice Updated Successfully!");
            window.location.href = "invoices_list.php";
        } else {
            alert(resp.message);
        }
    }, "json");

}
// CUSTOM TAB BEHAVIOR — Move only Rates vertically
// CUSTOM TAB BEHAVIOR — Move only Rates vertically + Auto-Select Value
$(document).on("keydown", ".rate-input", function(e) {

    if (e.key === "Tab") {
        e.preventDefault(); // Stop normal tab

        let currentRow = $(this).closest("tr");
        let nextRow = currentRow.next("tr");

        if (nextRow.length) {
            let nextRate = nextRow.find(".rate-input");
            nextRate.focus();
            setTimeout(() => nextRate.select(), 10); // 🔥 auto-select
        } else {
            // If last row → add new row automatically
            addInvoiceRow();
            setTimeout(() => {
                let newRate = $("#row-" + rowCounter).find(".rate-input");
                newRate.focus();
                newRate.select(); // 🔥 auto-select
            }, 50);
        }
    }
});

</script>

</body>
</html>
