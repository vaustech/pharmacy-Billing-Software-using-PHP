<?php
header('Content-Type: application/json');
require_once '../config/db_connect.php';

$response = array();

// শুধুমাত্র POST রিকোয়েস্ট এবং 'action' থাকলে কাজ করবে
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /**
     * ===============================================
     * অ্যাকশন: ওষুধ খোঁজা (Autocomplete-এর জন্য)
     * ===============================================
     */
    if ($action == 'search_medicine' && isset($_POST['term'])) {
        $term = $_POST['term'] . '%'; // নামের শুরু দিয়ে খোঁজা
        $medicines = array();

        try {
            // Prepared Statement ব্যবহার করা
            $sql = "SELECT id, name, mrp, stock_quantity 
                    FROM medicines 
                    WHERE (name LIKE ? OR batch_no LIKE ?) 
                    AND stock_quantity > 0 
                    LIMIT 10";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $term, $term);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $med_item = array(
                        'id' => $row['id'],
                        'label' => $row['name'] . " (স্টক: " . $row['stock_quantity'] . ")",
                        'value' => $row['name'],
                        'mrp' => $row['mrp'],
                        'stock' => $row['stock_quantity']
                    );
                    $medicines[] = $med_item;
                }
            }

            $stmt->close();
        } catch (Exception $e) {
            // error হলে খালি অ্যারে ফেরত দেবে
        }

        echo json_encode($medicines);
        exit;
    }

    /**
     * ===============================================
     * অ্যাকশন: বিল জেনারেট করা (নতুন কোড)
     * ===============================================
     */
    else if ($action == 'generate_bill') {
        
        // ফ্রন্টএন্ড থেকে পাঠানো ডেটা গ্রহণ করা
        $customer_name = $_POST['customer_name'];
        $sub_total = (float)$_POST['sub_total'];
        $discount = (float)$_POST['discount'];
        $grand_total = (float)$_POST['grand_total'];
        
        // কার্ট আইটেমগুলো JSON স্ট্রিং হিসাবে আসছে, তাই ডিকোড করতে হবে
        $cart_items = json_decode($_POST['cart_items'], true);

        // কার্ট খালি কিনা যাচাই
        if (empty($cart_items)) {
            $response['status'] = 'error';
            $response['message'] = 'Cart is empty. Please add medicines first.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // --- ডাটাবেস ট্রানজ্যাকশন শুরু ---
        $conn->begin_transaction();

        try {
            // ধাপ ১: sales টেবিলে মূল বিল ইনসার্ট
            $sql_sale = "INSERT INTO sales (customer_name, sub_total, discount, grand_total, sale_date) 
                         VALUES (?, ?, ?, ?, NOW())";
            $stmt_sale = $conn->prepare($sql_sale);
            $stmt_sale->bind_param("sddd", $customer_name, $sub_total, $discount, $grand_total);

            if (!$stmt_sale->execute()) {
                throw new Exception("Error saving sale: " . $stmt_sale->error);
            }

            // সদ্য তৈরি বিলের ID (sale_id)
            $sale_id = $conn->insert_id;

            // ধাপ ২: প্রতিটি আইটেমের জন্য sale_items এ ইনসার্ট ও স্টক আপডেট
            foreach ($cart_items as $item) {
                $medicine_id = (int)$item['id'];
                $quantity = (int)$item['quantity'];
                $price_per_item = (float)$item['mrp'];

                // ২ক: sale_items এ ইনসার্ট
                $sql_item = "INSERT INTO sale_items (sale_id, medicine_id, quantity, price_per_item) 
                             VALUES (?, ?, ?, ?)";
                $stmt_item = $conn->prepare($sql_item);
                $stmt_item->bind_param("iiid", $sale_id, $medicine_id, $quantity, $price_per_item);

                if (!$stmt_item->execute()) {
                    throw new Exception("Error saving sale item: " . $stmt_item->error);
                }

                // ২খ: medicines টেবিলে স্টক কমানো
                $sql_stock = "UPDATE medicines SET stock_quantity = stock_quantity - ? 
                              WHERE id = ? AND stock_quantity >= ?";
                $stmt_stock = $conn->prepare($sql_stock);
                $stmt_stock->bind_param("iii", $quantity, $medicine_id, $quantity);

                if (!$stmt_stock->execute()) {
                    throw new Exception("Error updating stock: " . $stmt_stock->error);
                }

                // যদি স্টক না কমে (মানে স্টক নেই)
                if ($stmt_stock->affected_rows == 0) {
                    throw new Exception("Insufficient stock for medicine ID: " . $medicine_id . ". Bill cancelled.");
                }
            }

            // ধাপ ৩: সব ঠিক থাকলে Commit
            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = 'Bill generated successfully!';
            $response['sale_id'] = $sale_id;

        } catch (Exception $e) {
            // কোনো সমস্যা হলে Rollback
            $conn->rollback();
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ভবিষ্যতের অন্যান্য অ্যাকশন এখানে যোগ করা যেতে পারে...
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request'], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
