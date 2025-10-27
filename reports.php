<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// নেভিগেশন বারে এই পেজটিকে 'active' দেখানোর জন্য
echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.navbar-nav .nav-link.active').classList.remove('active');
        document.querySelector('a[href=\"reports.php\"]').classList.add('active');
    });
</script>";

// --- PHP লজিক: তারিখ ফিল্টার করা ---

// ভেরিয়েবলগুলো প্রথমে সেট করে নেওয়া
$start_date = '';
$end_date = '';
$sql_where = ''; // SQL ক্যোয়ারীর WHERE অংশ
$params = []; // Prepared Statement-এর জন্য
$types = ''; // Prepared Statement-এর জন্য

// যদি ব্যবহারকারী তারিখ দিয়ে ফিল্টার করে (فرم সাবমিট করে)
if (isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    
    // শেষ তারিখের পুরো দিনটি অন্তর্ভুক্ত করার জন্য (23:59:59)
    $end_date_full = $end_date . ' 23:59:59';
    
    $sql_where = " WHERE sale_date BETWEEN ? AND ?";
    
    $params[] = &$start_date;
    $params[] = &$end_date_full;
    $types = "ss"; // দুটিই স্ট্রিং
}

// --- মোট হিসাব (Summary) এবং টেবিল ডেটা আনার জন্য ক্যোয়ারী ---

// ১. মোট হিসাবের ক্যোয়ারী (ফিল্টার সহ)
$sql_summary = "SELECT 
                    SUM(sub_total) AS total_sub, 
                    SUM(discount) AS total_disc, 
                    SUM(grand_total) AS total_grand 
                FROM sales" . $sql_where;

$stmt_summary = $conn->prepare($sql_summary);
if (!empty($types)) {
    $stmt_summary->bind_param($types, ...$params);
}
$stmt_summary->execute();
$summary = $stmt_summary->get_result()->fetch_assoc();

// ২. টেবিলের বিস্তারিত ডেটা আনার ক্যোয়ারী (ফিল্টার সহ)
$sql_details = "SELECT * FROM sales" . $sql_where . " ORDER BY sale_id DESC";

$stmt_details = $conn->prepare($sql_details);
if (!empty($types)) {
    $stmt_details->bind_param($types, ...$params);
}
$stmt_details->execute();
$result_details = $stmt_details->get_result();

?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-bar-chart-fill"></i> বিক্রয় রিপোর্ট (Sales Report)</h4>
            </div>
            <div class="card-body">
                
                <form method="GET" action="reports.php" class="row g-3 mb-4 p-3 border rounded bg-light">
                    <div class="col-md-5">
                        <label for="start_date" class="form-label">শুরুর তারিখ (Start Date)</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo htmlspecialchars($start_date); ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label for="end_date" class="form-label">শেষ তারিখ (End Date)</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo htmlspecialchars($end_date); ?>" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel-fill"></i> ফিল্টার
                        </button>
                        <a href="reports.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-repeat"></i>
                        </a>
                    </div>
                </form>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center">
                                <h5 class="card-title">মোট মূল্য (Subtotal)</h5>
                                <h3>৳ <?php echo number_format($summary['total_sub'] ?? 0, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <h5 class="card-title">মোট ডিসকাউন্ট</h5>
                                <h3>৳ <?php echo number_format($summary['total_disc'] ?? 0, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <h5 class="card-title">সর্বমোট বিক্রয় (Grand Total)</h5>
                                <h3>৳ <?php echo number_format($summary['total_grand'] ?? 0, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <h5 class="mt-4">বিস্তারিত রিপোর্ট</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">বিল নং</th>
                                <th scope="col">গ্রাহকের নাম</th>
                                <th scope="col">তারিখ ও সময়</th>
                                <th scope="col">মোট মূল্য</th>
                                <th scope="col">ডিসকাউন্ট</th>
                                <th scope="col">সর্বমোট</th>
                                <th scope="col">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result_details->num_rows > 0) {
                                while ($row = $result_details->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>#" . $row['sale_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                                    echo "<td>" . date("d/m/Y h:i A", strtotime($row['sale_date'])) . "</td>";
                                    echo "<td>" . number_format($row['sub_total'], 2) . "</td>";
                                    echo "<td>" . number_format($row['discount'], 2) . "</td>";
                                    echo "<td class='fw-bold'>" . number_format($row['grand_total'], 2) . "</td>";
                                    echo "<td>
                                            <a href='receipt.php?sale_id=" . $row['sale_id'] . "' class='btn btn-sm btn-info' target='_blank'>
                                                <i class='bi bi-receipt'></i> রসিদ দেখুন
                                            </a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>এই তারিখের মধ্যে কোনো বিক্রয় পাওয়া যায়নি।</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// কানেকশন বন্ধ করা
$stmt_summary->close();
$stmt_details->close();
$conn->close();

// ফুটার ফাইল যুক্ত করা
require_once 'includes/footer.php';
?>