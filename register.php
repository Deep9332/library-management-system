<?php
// register.php - Student Registration Page
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin/dashboard.php" : "user/dashboard.php"));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists!';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($ins->execute([$name, $email, $hashed])) {
                header("Location: login.php");
                exit;
            } else {
                $error = 'Failed to register account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-wrapper">

<div class="auth-card">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="font-size: 2.2rem; margin-bottom: 6px;">📝</div>
        <h2 style="font-weight: 800; font-size: 1.5rem; color: var(--text-dark);">Student Registration</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Create an account to request library books</p>
    </div>

    <?php if ($error): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. John Student" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. john@student.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn" style="width: 100%; margin-top: 8px;">Create Account</button>
    </form>

    <p style="text-align: center; margin-top: 20px; font-size: 0.88rem; color: var(--text-muted);">
        Already registered? <a href="login.php" style="font-weight: 700;">Sign In Here</a>
    </p>
</div>

</body>
</html>
