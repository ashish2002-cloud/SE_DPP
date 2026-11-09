<?php
require 'config/db.php';
session_start();

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);

    if ($stmt->execute()) {
        $message = "Registration successful! You can now <a href='login.php'>login</a>.";
    } else {
        $error = "Username already exists or error occurred.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - DPP Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="card">
        <h2>Register</h2>
        <?php if ($message) echo "<p class='success'>$message</p>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Role</label>
            <select name="role" required>
                <option value="CONSUMER">Consumer / Owner</option>
                <option value="MANUFACTURER">Manufacturer</option>
                <option value="REPAIRER">Repairer</option>
                <option value="RESELLER">Reseller</option>
                <option value="RECYCLER">Recycler</option>
                <option value="ADMIN">Admin</option>
            </select>

            <button type="submit">Register</button>
        </form>
        <p>Already have account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>