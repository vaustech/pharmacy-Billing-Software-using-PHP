<?php
// ডাটাবেস কনফিগারেশন
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // XAMPP-এ সাধারণত কোনো পাসওয়ার্ড থাকে না
define('DB_NAME', 'pharmacy_db'); // যে নামে ডাটাবেস তৈরি করেছেন

// অবজেক্ট-ওরিয়েন্টেড (Object-Oriented) পদ্ধতিতে MySQLi কানেকশন
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// কানেকশন চেক করা
if ($conn->connect_error) {
    // die() ফাংশন স্ক্রিপ্ট বন্ধ করে দেয় এবং একটি বার্তা দেখায়
    die("Connection failed: " . $conn->connect_error);
}

// বাংলা অক্ষরের জন্য UTF-8 ক্যারেক্টার সেট ঠিক করা
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// আমরা এই ফাইলটি অন্যান্য ফাইলে 'require' করব, 
// তাই এখানে কোনো HTML বা আউটপুট থাকা চলবে না।
?>