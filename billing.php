<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// নেভিগেশন বারে এই পেজটিকে 'active' দেখানোর জন্য
// (এই কোডটি header.php-তে যুক্ত করলে ভালো হয়, আপাতত এখানে রাখছি)
echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        // আগের 'active' ক্লাস মুছে ফেলা
        document.querySelector('.navbar-nav .nav-link.active').classList.remove('active');
        // 'নতুন বিল' লিঙ্কটিকে active করা
        document.querySelector('a[href=\"billing.php\"]').classList.add('active');
    });
</script>";
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-calculator-fill"></i> নতুন বিল (Point of Sale)</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="medicine_search" class="form-label">ওষুধ খুঁজুন:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="medicine_search" class="form-control form-control-lg" 
                               placeholder="ওষুধের নাম টাইপ করুন...">
                    </div>
                </div>

                <div class="table-responsive" style="min-height: 300px;">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 40%;">ওষুধের নাম</th>
                                <th scope="col" style="width: 15%;">মূল্য</th>
                                <th scope="col" style="width: 15%;">পরিমাণ</th>
                                <th scope="col" style="width: 20%;">মোট</th>
                                <th scope="col" style="width: 10%;">মুছুন</th>
                            </tr>
                        </thead>
                        <tbody id="billing-cart-body">
                            <tr>
                                <td colspan="5" class="text-center text-muted">কার্ট খালি আছে</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm sticky-top" style="top: 80px;"> <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-receipt"></i> বিলের বিবরণ</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="customer_name" class="form-label">গ্রাহকের নাম</label>
                    <input type="text" id="customer_name" class="form-control" value="Walking Customer">
                </div>

                <div class="mb-3">
                    <h5 class="mb-3">হিসাব</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            মোট মূল্য (Subtotal)
                            <span class="fw-bold" id="bill-subtotal">৳ 0.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ডিসকাউন্ট (৳)
                            <input type="number" class="form-control w-50" id="bill-discount" value="0.00" step="0.01">
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center fs-4">
                            <strong>সর্বমোট (Grand Total)</strong>
                            <strong class="text-success" id="bill-grandtotal">৳ 0.00</strong>
                        </li>
                    </ul>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-lg" id="generateBillBtn">
                        <i class="bi bi-check-circle-fill"></i> বিল জেনারেট করুন
                    </button>
                    <button type="button" class="btn btn-danger" id="cancelBillBtn">
                        <i class="bi bi-x-circle-fill"></i> বাতিল করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// ফুটার ফাইল যুক্ত করা
require_once 'includes/footer.php';
?>