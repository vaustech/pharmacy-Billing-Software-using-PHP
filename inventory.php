<?php
// inventory.php
include 'includes/header.php';
include 'includes/sidebar.php';
include 'config/db_connect.php';
?>

<div class="container mt-4">
    <h4 class="mb-4">ওষুধ ইনভেন্টরি</h4>

    <!-- Add Medicine Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
        নতুন ওষুধ যোগ করুন
    </button>

    <!-- Medicine Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="medicineTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>নাম</th>
                    <th>ব্যাচ</th>
                    <th>মেয়াদ</th>
                    <th>স্টক</th>
                    <th>MRP</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="medicineTableBody">
    <?php
    // কলামের নামগুলো নির্দিষ্ট করে দেওয়া ভালো অভ্যাস
    $sql = "SELECT id, name, batch_no, expiry_date, stock_quantity, mrp FROM medicines ORDER BY id DESC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // XSS অ্যাটাক প্রতিরোধের জন্য htmlspecialchars ব্যবহার করা
            $name = htmlspecialchars($row['name']);
            $batch_no = htmlspecialchars($row['batch_no']);
            
            // কম স্টক হাইলাইট করার জন্য
            $low_stock_class = $row['stock_quantity'] < 10 ? 'table-danger' : '';

            echo "<tr class='{$low_stock_class}'>
                    <td>{$row['id']}</td>
                    <td>{$name}</td>
                    <td>{$batch_no}</td>
                    <td>{$row['expiry_date']}</td>
                    <td>{$row['stock_quantity']}</td>
                    <td>{$row['mrp']}</td>
                    <td>
                        <button class='btn btn-sm btn-warning editBtn' data-id='{$row['id']}' data-bs-toggle='modal' data-bs-target='#editMedicineModal'>
                            <i class='bi bi-pencil-square'></i> Edit
                        </button>
                        <button class='btn btn-sm btn-danger deleteBtn' data-id='{$row['id']}'>
                            <i class='bi bi-trash-fill'></i> Delete
                        </button>
                    </td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center'>কোনো তথ্য পাওয়া যায়নি।</td></tr>";
    }
    ?>
</tbody>
        </table>
    </div>
</div>

<!-- =============================================== -->
<!-- বিদ্যমান "Add Medicine Modal" -->
<!-- =============================================== -->
<div class="modal fade" id="addMedicineModal" tabindex="-1" aria-labelledby="addMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMedicineModalLabel">নতুন ওষুধ যোগ করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMedicineForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="med_name" class="form-label">ওষুধের নাম <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="med_name" name="med_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="med_batch" class="form-label">ব্যাচ নং</label>
                            <input type="text" class="form-control" id="med_batch" name="med_batch">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="med_expiry" class="form-label">মেয়াদ শেষ হওয়ার তারিখ</label>
                            <input type="date" class="form-control" id="med_expiry" name="med_expiry">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="med_stock" class="form-label">স্টক (সংখ্যা) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="med_stock" name="med_stock" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="med_mrp" class="form-label">বিক্রয় মূল্য (MRP) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="med_mrp" name="med_mrp" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="med_cost" class="form-label">ক্রয় মূল্য (Cost Price)</label>
                        <input type="number" step="0.01" class="form-control" id="med_cost" name="med_cost">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                <button type="button" class="btn btn-success" id="saveMedicineBtn">সেভ করুন</button>
            </div>
        </div>
    </div>
</div>

<!-- =============================================== -->
<!-- নতুন "Edit Medicine Modal" -->
<!-- =============================================== -->
<div class="modal fade" id="editMedicineModal" tabindex="-1" aria-labelledby="editMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMedicineModalLabel">ওষুধের তথ্য সম্পাদনা করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMedicineForm">
                    <input type="hidden" id="edit_med_id" name="med_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_med_name" class="form-label">ওষুধের নাম <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_med_name" name="med_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_med_batch" class="form-label">ব্যাচ নং</label>
                            <input type="text" class="form-control" id="edit_med_batch" name="med_batch">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_med_expiry" class="form-label">মেয়াদ শেষ হওয়ার তারিখ</label>
                            <input type="date" class="form-control" id="edit_med_expiry" name="med_expiry">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_med_stock" class="form-label">স্টক (সংখ্যা) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_med_stock" name="med_stock" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_med_mrp" class="form-label">বিক্রয় মূল্য (MRP) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="edit_med_mrp" name="med_mrp" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_med_cost" class="form-label">ক্রয় মূল্য (Cost Price)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_med_cost" name="med_cost">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                <button type="button" class="btn btn-warning" id="updateMedicineBtn">আপডেট করুন</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
