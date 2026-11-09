<?php
require 'includes/auth.php';
require_role('MANUFACTURER');
require 'config/db.php';

// Get product ID from URL
$product_db_id = $_GET['id'] ?? 0;

// Fetch product (only if it belongs to this manufacturer)
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND manufacturer_id = ?");
$stmt->bind_param("ii", $product_db_id, $_SESSION['user_id']);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("<h2>Product not found or you don't have permission to edit it.</h2>
         <a href='dashboard.php'>Back to Dashboard</a>");
}

// Fetch materials
$mat_stmt = $conn->prepare("SELECT * FROM materials WHERE product_id = ?");
$mat_stmt->bind_param("i", $product_db_id);
$mat_stmt->execute();
$material = $mat_stmt->get_result()->fetch_assoc();

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $category = trim($_POST['category']);
    $brand    = trim($_POST['brand']);
    $mfg_date = $_POST['mfg_date'];
    $origin   = trim($_POST['origin']);
    $status   = $_POST['status'];

    // Update product
    $update = $conn->prepare("UPDATE products SET 
        name = ?, category = ?, brand = ?, manufacturing_date = ?, origin = ?, status = ?
        WHERE id = ? AND manufacturer_id = ?");
    $update->bind_param("ssssssii", $name, $category, $brand, $mfg_date, $origin, $status, $product_db_id, $_SESSION['user_id']);

    if ($update->execute()) {
        // Update or insert material
        if ($material) {
            $mat_update = $conn->prepare("UPDATE materials SET material_type = ?, percentage = ?, source = ? WHERE product_id = ?");
            $mat_update->bind_param("sdsi", $_POST['material_type'], $_POST['percentage'], $_POST['source'], $product_db_id);
            $mat_update->execute();
        } else {
            if (!empty($_POST['material_type'])) {
                $mat_insert = $conn->prepare("INSERT INTO materials (product_id, material_type, percentage, source) VALUES (?, ?, ?, ?)");
                $mat_insert->bind_param("isds", $product_db_id, $_POST['material_type'], $_POST['percentage'], $_POST['source']);
                $mat_insert->execute();
            }
        }

        $message = "Product updated successfully! 
                    <a href='product_passport.php?id=" . urlencode($product['product_id']) . "'>View Passport</a>";
        
        // Refresh product data
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Failed to update product.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <div class="card">
        <h2>Edit Product</h2>
        <p><strong>Product ID:</strong> <?= htmlspecialchars($product['product_id']) ?> (cannot be changed)</p>

        <?php if ($message) echo "<p class='success'>$message</p>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <label>Product Name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label>Category *</label>
            <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

            <label>Brand *</label>
            <input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>

            <label>Manufacturing Date *</label>
            <input type="date" name="mfg_date" value="<?= $product['manufacturing_date'] ?>" required>

            <label>Origin / Country *</label>
            <input type="text" name="origin" value="<?= htmlspecialchars($product['origin']) ?>" required>

            <label>Status</label>
            <select name="status">
                <option value="MANUFACTURED" <?= $product['status']=='MANUFACTURED'?'selected':'' ?>>MANUFACTURED</option>
                <option value="SOLD" <?= $product['status']=='SOLD'?'selected':'' ?>>SOLD</option>
                <option value="REPAIRED" <?= $product['status']=='REPAIRED'?'selected':'' ?>>REPAIRED</option>
                <option value="RESOLD" <?= $product['status']=='RESOLD'?'selected':'' ?>>RESOLD</option>
                <option value="RECYCLED" <?= $product['status']=='RECYCLED'?'selected':'' ?>>RECYCLED</option>
            </select>

            <hr>
            <h3>Material Information</h3>

            <label>Material Type</label>
            <input type="text" name="material_type" value="<?= htmlspecialchars($material['material_type'] ?? '') ?>">

            <label>Percentage (%)</label>
            <input type="number" step="0.01" name="percentage" value="<?= $material['percentage'] ?? '' ?>">

            <label>Source</label>
            <input type="text" name="source" value="<?= htmlspecialchars($material['source'] ?? '') ?>">

            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>