<?php
// এখানে আমরা ভবিষ্যতে সেশন (Session) স্টার্ট করতে পারি ইউজার লগইনের জন্য
// session_start(); 
?>
<!DOCTYPE html>
<html lang="bn"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaLink POS - ফার্মেসি ম্যানেজমেন্ট</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-capsule-pill"></i> PharmaLink POS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="billing.php"><i class="bi bi-calculator-fill"></i> নতুন বিল</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="inventory.php"><i class="bi bi-archive-fill"></i> ইনভেন্টরি</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart-fill"></i> রিপোর্ট</a>
                    </li>
                    </ul>
            </div>
        </div>
    </nav>

    <main class="container-fluid mt-4">