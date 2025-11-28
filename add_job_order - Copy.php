<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// add_job_order.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .numeric-input {
            text-align: right;
        }
        .checkbox-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .form-control-sm {
            font-size: 0.875rem;
        }
        .btn-sm {
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1">Add New Job Order</h2>
                <p class="text-muted mb-0">Create job order with multiple details</p>
            </div>
            <div>
                <a href="job_orders_list.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i> View Job Orders
                </a>
            </div>
        </div>

        <!-- Job Order Form -->
        <form id="jobOrderForm" class="needs-validation" novalidate>
            <!-- Job Order Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Job Order Header</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Job Order No</label>
                            <input type="text" id="JobOrderNo" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Order Date *</label>
                            <input type="date" id="OrderDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Delivery Date *</label>
                            <input type="date" id="DeliveryDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Design By *</label>
                            <div class="form-control-sm">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="DesignBy" id="DesignByClient" value="Client" required>
                                    <label class="form-check-label" for="DesignByClient">Client</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="DesignBy" id="DesignByFresh" value="Fresh" checked required>
                                    <label class="form-check-label" for="DesignByFresh">Fresh</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Job For *</label>
                            <select id="JobFor" class="form-select form-select-sm" required>
                                <option value="New Client">New Client</option>
                                <option value="Existing Client">Existing Client</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Customer Name *</label>
                            <input type="text" id="CustomerName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Cell No *</label>
                            <input type="text" id="CellNo" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Designer</label>
                            <input type="text" id="Designer" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm">Advance Payment</label>
                            <input type="number" id="AdvancePayment" class="form-control form-control-sm numeric-input" 
                                   step="0.01" value="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Order Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Job Order Details</h5>
                    <button type="button" class="btn btn-light btn-sm" onclick="addDetailRow()">
                        <i class="fas fa-plus me-1"></i> Add Row
                    </button>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="detailsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">Sr#</th>
                                    <th>Detail</th>
                                    <th>Media</th>
                                    <th width="80">Width</th>
                                    <th width="80">Height</th>
                                    <th width="60">Qty</th>
                                    <th width="80">Sqft</th>
                                    <th width="60">Ring</th>
                                    <th width="60">Pocket</th>
                                    <th width="60">Action</th>
                                </tr>
                            </thead>
                            <tbody id="detailsTableBody">
                                <!-- Dynamic rows will be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetForm()">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save me-1"></i> Save Job Order
                </button>
            </div>
        </form>
    </div>

    <!-- Success Modal -->
    <!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Job Order saved successfully!</p>
                <p class="mb-0" id="jobOrderNumber"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="resetForm()">Add New Order</button>
                <a href="job_orders_list.php" class="btn btn-outline-primary">View Orders</a>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var detailRowCount = 0;
        var materials = [];

        $(document).ready(function() {
            // Set today's date
            $('#OrderDate').val(new Date().toISOString().split('T')[0]);
            
            // Generate Job Order No
            generateJobOrderNo();
            
            // Load materials
            loadMaterials();
            
            // Add first detail row
            addDetailRow();
            
            // Form submission
            $('#jobOrderForm').submit(function(e) {
                e.preventDefault();
                submitJobOrder();
            });
// alert(detailRowCount);
  if (detailRowCount === 1) {
   
            for (var i = 1; i <= 7; i++) {
                addDetailRow();
            }
        }




        });

        function generateJobOrderNo() {
            var timestamp = new Date().getTime();
            var random = Math.floor(Math.random() * 1000);
            $('#JobOrderNo').val('JO-' + timestamp + '-' + random);
        }

        function loadMaterials() {
            $.ajax({
                url: 'job_order_functions.php',
                type: 'POST',
                data: { action: 'get_materials' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        materials = data.materials;
                        updateMaterialDropdowns();
                    }
                },
                error: function() {
                    console.error('Failed to load materials');
                }
            });
        }

        function updateMaterialDropdowns() {
            $('.media-select').each(function() {
                var currentValue = $(this).val();
                $(this).empty().append('<option value="">Select Media</option>');
                
                for (var i = 0; i < materials.length; i++) {
                    $(this).append('<option value="' + materials[i].name + '">' + materials[i].name + '</option>');
                }
                
                $(this).val(currentValue);
            });
        }

        function addDetailRow() {
            detailRowCount++;
            var row = '<tr id="detailRow-' + detailRowCount + '">' +
                '<td><input type="text" class="form-control form-control-sm text-center" value="' + detailRowCount + '" readonly></td>' +
                '<td><input type="text" name="Detail[]" class="form-control form-control-sm" placeholder="Enter detail"  ></td>' +
                '<td><select name="Media[]" class="form-select form-select-sm media-select"  ><option value="">Select Media</option></select></td>' +
                '<td><input type="number" name="Width[]" class="form-control form-control-sm numeric-input" step="1" value="0" onchange="calculateSqft(' + detailRowCount + ')"></td>' +
                '<td><input type="number" name="Height[]" class="form-control form-control-sm numeric-input" step="1" value="0" onchange="calculateSqft(' + detailRowCount + ')"></td>' +
                '<td><input type="number" name="Qty[]" class="form-control form-control-sm numeric-input text-center" value="1" onchange="calculateSqft(' + detailRowCount + ')"></td>' +
                '<td><input type="number" name="Sqft[]" class="form-control form-control-sm numeric-input" step="1" value="0" readonly></td>' +
                '<td><div class="checkbox-wrapper"><input type="checkbox" name="Ring[]" value="1" class="form-check-input"></div></td>' +
                '<td><div class="checkbox-wrapper"><input type="checkbox" name="Pocket[]" value="1" class="form-check-input"></div></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeDetailRow(' + detailRowCount + ')"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';
            
            $('#detailsTableBody').append(row);
            
            // Update material dropdown for new row
            updateMaterialDropdowns();
        }

        function removeDetailRow(rowId) {
            $('#detailRow-' + rowId).remove();
            updateSerialNumbers();
        }

        function updateSerialNumbers() {
            $('#detailsTableBody tr').each(function(index) {
                $(this).find('td:first input').val(index + 1);
            });
        }

        function calculateSqft(rowId) {
            var width = parseFloat($('input[name="Width[]"]').eq(rowId - 1).val()) || 0;
            var height = parseFloat($('input[name="Height[]"]').eq(rowId - 1).val()) || 0;
            var qty = parseFloat($('input[name="Qty[]"]').eq(rowId - 1).val()) || 0;
            var sqft = width * height * qty;
            
            $('input[name="Sqft[]"]').eq(rowId - 1).val(sqft.toFixed(2));
        }

 function submitJobOrder() {
    // Validate form
    if (!$('#jobOrderForm')[0].checkValidity()) {
        $('#jobOrderForm')[0].reportValidity();
        return;
    }

    // Check if at least one detail row exists
    if ($('#detailsTableBody tr').length === 0) {
        alert('Please add at least one detail row.');
        return;
    }

    // Collect form data - REMOVED JobOrderNo since it's auto-generated
    var formData = {
        action: 'create_job_order',
        OrderDate: $('#OrderDate').val(),
        DeliveryDate: $('#DeliveryDate').val(),
        DesignBy: $('input[name="DesignBy"]:checked').val(),
        JobFor: $('#JobFor').val(),
        CustomerName: $('#CustomerName').val(),
        CellNo: $('#CellNo').val(),
        Designer: $('#Designer').val(),
        AdvancePayment: $('#AdvancePayment').val(),
        details: []
    };

    // Collect ONLY VALID detail rows
    var hasValidRow = false;
    $('#detailsTableBody tr').each(function(index) {
        var row = $(this);
        var Detail = row.find('input[name="Detail[]"]').val().trim();
        var Media = row.find('select[name="Media[]"]').val();
        var Width = parseFloat(row.find('input[name="Width[]"]').val()) || 0;
        var Height = parseFloat(row.find('input[name="Height[]"]').val()) || 0;
        var Qty = parseFloat(row.find('input[name="Qty[]"]').val()) || 0;
        var Sqft = parseFloat(row.find('input[name="Sqft[]"]').val()) || 0;
        
        // Log each row for debugging
        console.log('Checking Row ' + (index + 1) + ':', {
            Detail: Detail,
            Media: Media,
            Width: Width,
            Height: Height,
            Qty: Qty,
            Sqft: Sqft,
            isValid: (Detail && Media && Width > 0 && Height > 0 && Qty > 0)
        });
        
        // ONLY include rows where ALL required fields are filled AND have valid values
        if (Detail && Media && Width > 0 && Height > 0 && Qty > 0) {
            hasValidRow = true;
            formData.details.push({
                SrNo: index + 1,
                Detail: Detail,
                Media: Media,
                Width: Width,
                Height: Height,
                Qty: Qty,
                Sqft: Sqft,
                Ring: row.find('input[name="Ring[]"]').is(':checked') ? 1 : 0,
                Pocket: row.find('input[name="Pocket[]"]').is(':checked') ? 1 : 0
            });
            console.log('Added valid row to formData:', formData.details[formData.details.length - 1]);
        }
    });

    console.log('Final formData being sent:', formData);
    console.log('Total valid rows:', formData.details.length);

    if (!hasValidRow) {
        alert('Please add at least one complete detail row with all required fields (Detail, Media, Width, Height, and Qty).');
        return;
    }

    // Submit to server
    $.ajax({
        url: 'job_order_functions.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(data) {
            console.log('Server response:', data);
            if (data.success) {
                $('#jobOrderNumber').text('Job Order No: ' + data.JobOrderNo + 
                                         (data.detailsSaved ? ' (' + data.detailsSaved + ' details saved)' : ''));
                var modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.log('Response Text:', xhr.responseText);
            console.log('Status Code:', xhr.status);
            
            // Try to parse the response to see what's actually being returned
            try {
                const responseText = xhr.responseText;
                if (responseText.length === 0) {
                    alert('Server returned empty response. Check PHP error logs.');
                } else if (responseText.includes('<br />') || responseText.includes('<b>')) {
                    alert('Server error: PHP error detected. Check console for details.');
                    console.error('PHP Error Response:', responseText);
                } else {
                    alert('Network error occurred. Please try again.');
                }
            } catch (e) {
                alert('Network error occurred. Please try again.');
            }
        }
    });
}

        function resetForm() {
            $('#jobOrderForm')[0].reset();
            $('#OrderDate').val(new Date().toISOString().split('T')[0]);
            $('#DesignByFresh').prop('checked', true);
            $('#AdvancePayment').val('0.00');
            $('#detailsTableBody').empty();
            detailRowCount = 0;
            generateJobOrderNo();
            addDetailRow();
            
            // Hide modal if shown
            var modal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
            if (modal) {
                modal.hide();
            }
        }
    </script>
</body>
</html>