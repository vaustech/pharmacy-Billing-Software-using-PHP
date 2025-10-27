<?php
// ডাটাবেস কানেকশন (উপরে যেমন ছিল তেমনই থাকবে)
include '../config/db_connect.php';

$response = ['status' => 'error', 'message' => 'Unknown error occurred.'];

// চেক করা হচ্ছে এটি একটি POST রিকোয়েস্ট কিনা এবং 'action' সেট করা আছে কিনা
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    // ===============================================
    // "Add Medicine" অ্যাকশন (যেমন ছিল)
    // ===============================================
    if ($action == 'add_medicine') {

        $med_name   = $_POST['med_name'] ?? '';
        $med_batch  = $_POST['med_batch'] ?? '';
        $med_stock  = $_POST['med_stock'] ?? '';
        $med_mrp    = $_POST['med_mrp'] ?? '';
        $med_expiry = $_POST['med_expiry'] ?? '';
        $med_cost   = $_POST['med_cost'] ?? '';

        if (empty($med_name) || empty($med_stock) || empty($med_mrp)) {
            $response['message'] = 'অনুগ্রহ করে সব আবশ্যিক ফিল্ড পূরণ করুন।';
        } else {
            $sql = "INSERT INTO medicines (name, batch_no, expiry_date, stock_quantity, mrp, cost_price) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssidd", $med_name, $med_batch, $med_expiry, $med_stock, $med_mrp, $med_cost);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'ওষুধ সফলভাবে যোগ করা হয়েছে।';
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }

            $stmt->close();
        }
    }

    // ===============================================
    // "Delete Medicine" অ্যাকশন (যেমন ছিল)
    // ===============================================
    else if ($action == 'delete_medicine') {

        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);

            try {
                $sql = "DELETE FROM medicines WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'ওষুধটি সফলভাবে মুছে ফেলা হয়েছে।';
                } else {
                    $response['message'] = 'Database error: ' . $stmt->error;
                }

                $stmt->close();

            } catch (Exception $e) {
                $response['message'] = 'মুছতে সমস্যা হয়েছে। সম্ভবত এটি কোনো বিলে ব্যবহৃত হয়েছে। (' . $e->getMessage() . ')';
            }

        } else {
            $response['message'] = 'Medicine ID not provided.';
        }
    }

    // ===============================================
    // ধাপ ২: নির্দিষ্ট ওষুধের তথ্য আনুন (নতুন কোড)
    // ===============================================
    else if ($action == 'get_medicine_details') {
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);

            try {
                $sql = "SELECT id, name, batch_no, expiry_date, stock_quantity, mrp, cost_price 
                        FROM medicines WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $medicine_data = $result->fetch_assoc();
                    $response['status'] = 'success';
                    $response['data'] = $medicine_data;
                } else {
                    $response['message'] = 'Medicine not found.';
                }
                $stmt->close();

            } catch (Exception $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }

        } else {
            $response['message'] = 'Medicine ID not provided.';
        }
    }

    // ===============================================
    // ধাপ ৩: ওষুধের তথ্য আপডেট করুন (নতুন কোড)
    // ===============================================
    else if ($action == 'update_medicine') {

        $id     = $_POST['med_id'] ?? '';
        $name   = $_POST['med_name'] ?? '';
        $batch  = $_POST['med_batch'] ?? '';
        $expiry = $_POST['med_expiry'] ?? '';
        $stock  = $_POST['med_stock'] ?? '';
        $mrp    = $_POST['med_mrp'] ?? '';
        $cost   = $_POST['med_cost'] ?? '';

        // ভ্যালিডেশন
        if (empty($id) || empty($name) || empty($stock) || empty($mrp)) {
            $response['message'] = 'Please fill in all required fields (*)';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // NULL বা ডিফল্ট মান সেট করা
        $expiry_date = !empty($expiry) ? $expiry : NULL;
        $batch_no    = !empty($batch) ? $batch : NULL;
        $cost_price  = !empty($cost) ? $cost : 0.00;

        try {
            $sql = "UPDATE medicines 
                    SET name = ?, batch_no = ?, expiry_date = ?, stock_quantity = ?, mrp = ?, cost_price = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiddi", $name, $batch_no, $expiry_date, $stock, $mrp, $cost_price, $id);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Medicine updated successfully!';
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();

        } catch (Exception $e) {
            $response['message'] = 'Exception: ' . $e->getMessage();
        }
    }

    // ===============================================
    // ভবিষ্যতের অন্যান্য অ্যাকশন এখানে যোগ করা যাবে
    // ===============================================

    $conn->close();

} else {
    $response['message'] = 'Invalid request method or missing action.';
}

// চূড়ান্ত JSON রেসপন্স প্রিন্ট করা
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
