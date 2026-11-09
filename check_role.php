<?php
session_start();

echo "<h2>Current Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['role'])) {
    echo "<h3>Your Role is: <span style='color:blue;'>" . $_SESSION['role'] . "</span></h3>";
} else {
    echo "<h3 style='color:red;'>No role found in session. You are not logged in properly.</h3>";
}
?>