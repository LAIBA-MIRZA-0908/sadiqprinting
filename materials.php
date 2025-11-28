<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
// materials.php
include 'header.php';
include 'menu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Materials Management</h1>
                <p class="text-gray-600">Add and manage printing materials</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Material Form (Left Side) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4" id="formTitle">Add New Material</h2>
                    
                    <form id="materialForm">
                        <input type="hidden" id="material_id" name="material_id" value="">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Material Name *</label>
                            <input type="text" id="material_name" name="material_name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter material name">
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="resetForm()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                                <i class="fas fa-save mr-2"></i> <span id="submitButtonText">Save Material</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Materials List (Right Side) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">Materials List</h2>
                        <div class="relative">
                            <input type="text" id="searchMaterials" placeholder="Search materials..."
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="materialsTableBody" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        <i class="fas fa-spinner fa-spin mr-2"></i> Loading materials...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-between items-center">
                        <div id="materialPaginationInfo" class="text-sm text-gray-700"></div>
                        <div class="space-x-2">
                            <button id="prevMaterialPage" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="nextMaterialPage" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Material</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to delete this material? This action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-center space-x-3 mt-4">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentMaterialPage = 1;
        const materialsPerPage = 100;
        let materialToDelete = null;

        $(document).ready(function() {
            // Load initial data
            loadMaterials();

            // Form submission
            $('#materialForm').submit(function(e) {
                e.preventDefault();
                submitMaterial();
            });

            // Search functionality
            $('#searchMaterials').on('input', function() {
                currentMaterialPage = 1;
                loadMaterials();
            });

            // Pagination
            $('#prevMaterialPage').click(function() {
                if (currentMaterialPage > 1) {
                    currentMaterialPage--;
                    loadMaterials();
                }
            });

            $('#nextMaterialPage').click(function() {
                currentMaterialPage++;
                loadMaterials();
            });
        });

        function loadMaterials() {
            const search = $('#searchMaterials').val();

            $.ajax({
                url: 'material_functions_new.php',
                type: 'POST',
                data: {
                    action: 'get_materials',
                    page: currentMaterialPage,
                    limit: materialsPerPage,
                    search: search
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        displayMaterials(data.materials);
                        updateMaterialPagination(data.total);
                    } else {
                        $('#materialsTableBody').html('<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error: ' + (data.message || 'Unknown error') + '</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading materials:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    console.log('Status Code:', xhr.status);
                    
                    // Try to parse the response to see what's actually being returned
                    try {
                        const responseText = xhr.responseText;
                        if (responseText.length === 0) {
                            $('#materialsTableBody').html('<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Server returned empty response. Check PHP error logs.</td></tr>');
                        } else if (responseText.includes('<br />') || responseText.includes('<b>')) {
                            $('#materialsTableBody').html('<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Server error: PHP error detected. Check console for details.</td></tr>');
                            console.error('PHP Error Response:', responseText);
                        } else {
                            $('#materialsTableBody').html('<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Network error loading materials</td></tr>');
                        }
                    } catch (e) {
                        $('#materialsTableBody').html('<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Network error loading materials</td></tr>');
                    }
                }
            });
        }

        function displayMaterials(materials) {
            const tbody = $('#materialsTableBody');
            
            if (materials.length === 0) {
                tbody.html('<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No materials found</td></tr>');
                return;
            }

            let html = '';
            materials.forEach(material => {
                const createdDate = new Date(material.created_at).toLocaleDateString();
                
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${material.id}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${material.name}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">${createdDate}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="editMaterial(${material.id}, '${material.name}')" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button onclick="deleteMaterial(${material.id})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tbody.html(html);
        }

        function updateMaterialPagination(total) {
            const totalPages = Math.ceil(total / materialsPerPage);
            $('#materialPaginationInfo').text(`Page ${currentMaterialPage} of ${totalPages} (${total} total materials)`);
            $('#prevMaterialPage').prop('disabled', currentMaterialPage === 1);
            $('#nextMaterialPage').prop('disabled', currentMaterialPage === totalPages);
        }

        function submitMaterial() {
            const materialId = $('#material_id').val();
            const materialName = $('#material_name').val();
            
            if (!materialName) {
                alert('Please enter a material name.');
                return;
            }

            const action = materialId ? 'update_material' : 'create_material';

            $.ajax({
                url: 'material_functions_new.php',
                type: 'POST',
                data: {
                    action: action,
                    material_id: materialId,
                    name: materialName
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert(materialId ? 'Material updated successfully!' : 'Material added successfully!');
                        resetForm();
                        loadMaterials();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error submitting material:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    alert('Network error occurred. Please try again.');
                }
            });
        }

        function editMaterial(id, name) {
            $('#material_id').val(id);
            $('#material_name').val(name);
            $('#formTitle').text('Edit Material');
            $('#submitButtonText').text('Update Material');
            
            // Scroll to form
            $('html, body').animate({
                scrollTop: $('#materialForm').offset().top - 100
            }, 500);
        }

        function deleteMaterial(id) {
            materialToDelete = id;
            $('#deleteModal').removeClass('hidden');
        }

        function confirmDelete() {
            if (!materialToDelete) return;

            $.ajax({
                url: 'material_functions_new.php', // Fixed: Using the new file
                type: 'POST',
                data: {
                    action: 'delete_material',
                    material_id: materialToDelete
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        alert('Material deleted successfully!');
                        closeDeleteModal();
                        loadMaterials();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error deleting material:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    alert('Network error occurred. Please try again.');
                }
            });
        }

        function closeDeleteModal() {
            $('#deleteModal').addClass('hidden');
            materialToDelete = null;
        }

        function resetForm() {
            document.getElementById('materialForm').reset();
            $('#material_id').val('');
            $('#formTitle').text('Add New Material');
            $('#submitButtonText').text('Save Material');
        }
    </script>
</body>
</html>