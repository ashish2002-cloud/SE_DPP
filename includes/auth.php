<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

function require_role($roles) {
    // Convert single role (string) into array
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array($_SESSION['role'], $roles)) {
        echo "<h2 style='color:red; text-align:center; margin-top:80px;'>Access Denied</h2>";
        echo "<p style='text-align:center;'>You do not have permission to access this page.</p>";
        echo "<p style='text-align:center;'><a href='dashboard.php'>← Go back to Dashboard</a></p>";
        exit;
    }
}
?>