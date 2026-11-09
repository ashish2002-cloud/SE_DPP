<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Scan / Search Product</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="card">
        <h2>View Product Passport</h2>
        <p>Enter Product ID (or scan QR code which opens the passport page directly)</p>

        <form action="product_passport.php" method="GET">
            <label>Product ID</label>
            <input type="text" name="id" placeholder="e.g. DPP-2026-00123" required>
            <button type="submit">View Passport</button>
        </form>

        <p style="margin-top:20px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">← Back to Dashboard</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>