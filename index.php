<?php
// হেডার এবং ডাটাবেস কানেকশন যুক্ত করা
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// নেভিগেশন বারে এই পেজটিকে 'active' দেখানোর জন্য
echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        // অন্য কোনো পেজ 'active' থাকলে তা রিমুভ করা
        var currentActive = document.querySelector('.navbar-nav .nav-link.active');
        if (currentActive) {
            currentActive.classList.remove('active');
        }
        // ড্যাশবোর্ড লিঙ্কটিকে 'active' করা
        document.querySelector('a[href=\"index.php\"]').classList.add('active');
    });
</script>";


// --- PHP লজিক: ড্যাশবোর্ডের তথ্য সংগ্রহ ---

// ১. আজকের বিক্রয়ের হিসাব
// CURDATE() ফাংশন আজকের তারিখ নিয়ে আসে
$sql_today = "SELECT 
                SUM(grand_total) as total_sales, 
                COUNT(sale_id) as total_bills 
              FROM sales 
              WHERE DATE(sale_date) = CURDATE()";
$result_today = $conn->query($sql_today);
$today_summary = $result_today->fetch_assoc();

// যদি কোনো বিক্রয় না হয়, তাহলে মান NULL হতে পারে, তাই '?? 0' ব্যবহার করা
$total_sales_today = $today_summary['total_sales'] ?? 0;
$total_bills_today = $today_summary['total_bills'] ?? 0;

// ২. মোট ওষুধের ধরণ (কত প্রকার ওষুধ আছে)
$sql_med_count = "SELECT COUNT(id) as total_medicines FROM medicines";
$result_med_count = $conn->query($sql_med_count);
$med_summary = $result_med_count->fetch_assoc();
$total_medicine_types = $med_summary['total_medicines'] ?? 0;

// ৩. কম স্টক থাকা ওষুধ (যেগুলোর স্টক ১০ বা তার কম)
$low_stock_threshold = 10;
$sql_low_stock = "SELECT name, stock_quantity 
                  FROM medicines 
                  WHERE stock_quantity <= ? 
                  ORDER BY stock_quantity ASC";
$stmt_low_stock = $conn->prepare($sql_low_stock);
$stmt_low_stock->bind_param("i", $low_stock_threshold);
$stmt_low_stock->execute();
$result_low_stock = $stmt_low_stock->get_result();

?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success shadow h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="mb-2">
                    <i class="bi bi-cash-coin fs-1"></i>
                </div>
                <h5 class="card-title">আজকের মোট বিক্রয়</h5>
                <h2 class="display-6 fw-bold">৳ <?php echo number_format($total_sales_today, 2); ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info shadow h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="mb-2">
                    <i class="bi bi-receipt fs-1"></i>
                </div>
                <h5 class="card-title">আজকের মোট বিল</h5>
                <h2 class="display-6 fw-bold"><?php echo $total_bills_today; ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary shadow h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="mb-2">
                    <i class="bi bi-archive-fill fs-1"></i>
                </div>
                <h5 class="card-title">মোট ওষুধের ধরণ</h5>
                <h2 class="display-6 fw-bold"><?php echo $total_medicine_types; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle-fill"></i> কম স্টক সতর্কতা (স্টক <?php echo $low_stock_threshold; ?> পিস বা কম)
                </h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if ($result_low_stock->num_rows > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php while ($row = $result_low_stock->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-capsule-pill text-danger me-2"></i>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </div>
                                <span class="badge bg-danger rounded-pill fs-6">
                                    <?php echo $row['stock_quantity']; ?> পিস
                                </span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill"></i> স্টক সন্তোষজনক। কোনো ওষুধের স্টক কম নেই।
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// কানেকশন এবং স্টেটমেন্ট বন্ধ করা
$stmt_low_stock->close();
$conn->close();

// ফুটার ফাইল যুক্ত করা
require_once 'includes/footer.php';
?>