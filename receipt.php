<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// URL থেকে sale_id নেওয়া
if (!isset($_GET['sale_id'])) {
    echo "<div class='alert alert-danger'>Error: No Sale ID provided.</div>";
    require_once 'includes/footer.php';
    exit;
}

$sale_id = (int)$_GET['sale_id'];

// --- ধাপ ১: বিলের মূল তথ্য আনা ---
$sql_sale = "SELECT * FROM sales WHERE sale_id = ?";
$stmt_sale = $conn->prepare($sql_sale);
$stmt_sale->bind_param("i", $sale_id);
$stmt_sale->execute();
$result_sale = $stmt_sale->get_result();

if ($result_sale->num_rows == 0) {
    echo "<div class='alert alert-danger'>Error: Bill not found.</div>";
    require_once 'includes/footer.php';
    exit;
}
$sale = $result_sale->fetch_assoc();

// --- ধাপ ২: বিলের আইটেমগুলো আনা ---
$sql_items = "SELECT m.name, si.quantity, si.price_per_item 
              FROM sale_items si 
              JOIN medicines m ON si.medicine_id = m.id 
              WHERE si.sale_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $sale_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<div class="container" id="receipt-area">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="mb-1"><i class="bi bi-capsule-pill"></i> PharmaLink POS</h2>
                        <p class="mb-0">নিউ টাউন,মুসলিমনগর,মাতুয়াইল,ঢাকা</p>
                         <p class="mb-0">ফোন:০১৭৭১৮০২৭৫৬</p>
                        <h4 class="mt-3">টাকার রসিদ / Bill Receipt</h4>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>বিল নং:</strong> #<?php echo htmlspecialchars($sale['sale_id']); ?>
                        </div>
                        <div class="col-6 text-end">
                            <strong>তারিখ:</strong> <?php echo date("d/m/Y h:i A", strtotime($sale['sale_date'])); ?>
                        </div>
                        <div class="col-12 mt-2">
                            <strong>গ্রাহকের নাম:</strong> <?php echo htmlspecialchars($sale['customer_name']); ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">ওষুধের নাম</th>
                                    <th scope="col">পরিমাণ</th>
                                    <th scope="col">মূল্য</th>
                                    <th scope="col" class="text-end">মোট</th>
                                T</tr>
                            </thead>
                            <tbody>
                                <?php
                                $serial = 1;
                                $item_subtotal = 0;
                                while ($item = $result_items->fetch_assoc()) {
                                    $row_total = $item['quantity'] * $item['price_per_item'];
                                    $item_subtotal += $row_total;
                                    echo "<tr>";
                                    echo "<td>" . $serial++ . "</td>";
                                    echo "<td>" . htmlspecialchars($item['name']) . "</td>";
                                    echo "<td>" . $item['quantity'] . "</td>";
                                    echo "<td>" . number_format($item['price_per_item'], 2) . "</td>";
                                    echo "<td class='text-end'>" . number_format($row_total, 2) . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">মোট মূল্য (Subtotal)</td>
                                    <td class="text-end">৳ <?php echo number_format($sale['sub_total'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end">ডিসকাউন্ট</td>
                                    <td class="text-end">৳ <?php echo number_format($sale['discount'], 2); ?></td>
                                </tr>
                                <tr class="fs-5 table-success">
                                    <td colspan="4" class="text-end">সর্বমোট (Grand Total)</td>
                                    <td class="text-end">৳ <?php echo number_format($sale['grand_total'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="text-center text-muted mt-4">ধন্যবাদ! আবার আসবেন।বিক্রিত মাল ফেরত নিয়া হয় না।</p>

                </div>
            </div>

            <div class="text-center my-4">
                <a href="billing.php" class="btn btn-primary">
                    <i class="bi bi-calculator-fill"></i> নতুন বিল করুন
                </a>
                <button class="btn btn-success" onclick="window.print();">
                    <i class="bi bi-printer-fill"></i> রসিদ প্রিন্ট করুন
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    @media print {
        body {
            background-color: #fff;
        }
        /* প্রিন্টের সময় হেডার, ফুটার এবং বাটন লুকিয়ে ফেলা */
        nav, footer, .btn {
            display: none !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        .col-lg-8 {
            width: 100% !important;
            offset: 0 !important;
        }
    }
</style>

<?php
// কানেকশন বন্ধ করা
$stmt_sale->close();
$stmt_items->close();
$conn->close();

require_once 'includes/footer.php';
?>