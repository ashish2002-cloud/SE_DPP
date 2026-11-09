<?php
require 'includes/auth.php';
require 'config/db.php';

// Get products created by this manufacturer
$my_products = null;
if ($_SESSION['role'] == 'MANUFACTURER') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE manufacturer_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $my_products = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manufacturer Dashboard - DPP Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .btn-primary {
            background: #1a73e8;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover {
            background: #1557b0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #e8f0fe;
        }
        .action-links a {
            margin-right: 10px;
            text-decoration: none;
            color: #1a73e8;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <div class="nav" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <strong>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></strong>
            <span style="background:#1a73e8; color:white; padding:3px 10px; border-radius:20px; font-size:13px; margin-left:8px;">
                MANUFACTURER
            </span>
        </div>
        <div>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card">
        <h2>Manufacturer Dashboard</h2>

        <!-- Create Product Button -->
        <p style="margin: 25px 0;">
            <a href="create_product.php" class="btn-primary">
                + Create New Product & Generate QR Code
            </a>
        </p>

        <!-- My Products Section -->
        <h3>My Created Products</h3>

        <?php if ($my_products && $my_products->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $my_products->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['product_id']) ?></strong></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['status']) ?></td>
                            <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                            <td class="action-links">
                                <a href="product_passport.php?id=<?= urlencode($p['product_id']) ?>">View Passport</a>
                                <a href="edit_product.php?id=<?= $p['id'] ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#666; margin-top:15px;">
                You have not created any products yet.<br>
                Click the button above to create your first product.
            </p>
        <?php endif; ?>
    </div>

    <!-- Optional: Quick Access -->
    <div class="card" style="margin-top:20px;">
        <h3>Quick Access</h3>
        <p>
            <a href="scan.php">Scan / Search any Product Passport</a>
        </p>
    </div>

</body>
</html>