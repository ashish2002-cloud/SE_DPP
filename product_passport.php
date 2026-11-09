<?php
require 'config/db.php';
session_start();

$product_id = $_GET['id'] ?? '';

if (!$product_id) {
    die("No Product ID provided");
}

$stmt = $conn->prepare("SELECT p.*, u.username AS manufacturer_name 
                        FROM products p 
                        LEFT JOIN users u ON p.manufacturer_id = u.id 
                        WHERE p.product_id = ?");
$stmt->bind_param("s", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("<h2>Product not found</h2>");
}

// Get materials
$mat = $conn->query("SELECT * FROM materials WHERE product_id = " . $product['id']);

// Get lifecycle events
$events = $conn->query("SELECT e.*, u.username 
                        FROM lifecycle_events e 
                        LEFT JOIN users u ON e.actor_id = u.id 
                        WHERE e.product_id = " . $product['id'] . " 
                        ORDER BY e.created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Digital Product Passport - <?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="card">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <p><strong>Product ID:</strong> <?= htmlspecialchars($product['product_id']) ?></p>
        <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
        <p><strong>Manufacturer:</strong> <?= htmlspecialchars($product['manufacturer_name']) ?></p>
        <p><strong>Manufacturing Date:</strong> <?= $product['manufacturing_date'] ?></p>
        <p><strong>Origin:</strong> <?= htmlspecialchars($product['origin']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($product['status']) ?></p>

        <?php if ($product['qr_code']): ?>
            <p><img src="<?= $product['qr_code'] ?>" alt="QR Code" width="180"></p>
        <?php endif; ?>

        <h3>Materials</h3>
        <ul>
            <?php while ($m = $mat->fetch_assoc()): ?>
                <li><?= htmlspecialchars($m['material_type']) ?> - <?= $m['percentage'] ?>% (Source: <?= htmlspecialchars($m['source']) ?>)</li>
            <?php endwhile; ?>
        </ul>

        <h3>Lifecycle Timeline</h3>
        <ul class="timeline">
            <?php while ($e = $events->fetch_assoc()): ?>
                <li>
                    <strong><?= $e['created_at'] ?></strong><br>
                    <?= $e['event_type'] ?> by <?= htmlspecialchars($e['username'] ?? 'System') ?><br>
                    <?= htmlspecialchars($e['event_data']) ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <p><a href="scan.php">← Scan another product</a> | 
           <?php if (isset($_SESSION['user_id'])): ?>
               <a href="dashboard.php">Dashboard</a>
           <?php else: ?>
               <a href="login.php">Login</a>
           <?php endif; ?>
        </p>
    </div>
</body>
</html>