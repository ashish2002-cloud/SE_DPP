<?php
require 'includes/auth.php';
require_role('MANUFACTURER');
require 'config/db.php';

// =============================================
// Function to generate Unique Product ID
// =============================================
function generateUniqueProductId($conn) {
    do {
        // Format: DPP-2026-00487
        $product_id = "DPP-" . date("Y") . "-" . str_pad(rand(1, 99999), 5, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare("SELECT id FROM products WHERE product_id = ?");
        $stmt->bind_param("s", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);  // Keep trying until unique

    return $product_id;
}

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $category = trim($_POST['category']);
    $brand    = trim($_POST['brand']);
    $mfg_date = $_POST['mfg_date'];
    $origin   = trim($_POST['origin']);

    // Generate unique Product ID
    $product_id = generateUniqueProductId($conn);

    // Create the full passport URL
    $passport_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/product_passport.php?id=" . urlencode($product_id);

    // Generate QR Code using free API
    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($passport_url);

    // Insert product into database
    $stmt = $conn->prepare("INSERT INTO products 
        (product_id, name, category, brand, manufacturer_id, manufacturing_date, origin, qr_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssisss", 
        $product_id, 
        $name, 
        $category, 
        $brand, 
        $_SESSION['user_id'], 
        $mfg_date, 
        $origin, 
        $qr_code_url
    );

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;

        // Insert material (if provided)
        if (!empty($_POST['material_type'])) {
            $mat_stmt = $conn->prepare("INSERT INTO materials (product_id, material_type, percentage, source) VALUES (?, ?, ?, ?)");
            $mat_stmt->bind_param("isds", $new_id, $_POST['material_type'], $_POST['percentage'], $_POST['source']);
            $mat_stmt->execute();
        }

        // Add MANUFACTURED lifecycle event
        $event_stmt = $conn->prepare("INSERT INTO lifecycle_events (product_id, event_type, actor_id, event_data) VALUES (?, 'MANUFACTURED', ?, ?)");
        $event_data = "Product created by manufacturer";
        $event_stmt->bind_param("iis", $new_id, $_SESSION['user_id'], $event_data);
        $event_stmt->execute();

        $message = "
            <strong>Product created successfully!</strong><br><br>
            <b>Product ID:</b> $product_id <br>
            <b>QR Code:</b><br>
            <img src='$qr_code_url' width='200'><br><br>
            <a href='product_passport.php?id=$product_id'>View Full Passport</a>
        ";
    } else {
        $error = "Error creating product. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Product - DPP Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="nav">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <div class="card">
        <h2>Create New Product</h2>

        <?php if ($message) echo "<div class='success'>$message</div>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <label>Product Name *</label>
            <input type="text" name="name" required>

            <label>Category *</label>
            <input type="text" name="category" required>

            <label>Brand / Manufacturer Name *</label>
            <input type="text" name="brand" required>

            <label>Manufacturing Date *</label>
            <input type="date" name="mfg_date" required>

            <label>Origin / Country *</label>
            <input type="text" name="origin" required>

            <hr>
            <h3>Material Information (Optional)</h3>

            <label>Material Type</label>
            <input type="text" name="material_type" placeholder="e.g. Cotton, Plastic, Steel">

            <label>Percentage (%)</label>
            <input type="number" step="0.01" name="percentage" placeholder="e.g. 80">

            <label>Source</label>
            <input type="text" name="source" placeholder="e.g. India, Recycled">

            <button type="submit">Create Product & Generate QR Code</button>
        </form>
    </div>
</body>
</html>