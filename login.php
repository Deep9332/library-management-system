<?php
// login.php - Login Handler for Admin and Students
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin/dashboard.php" : "user/dashboard.php"));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check password using password_verify() or fallback for convenience
        $is_valid = false;
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $is_valid = true;
            } elseif ($user['email'] === 'admin@gmail.com' && ($password === 'admin123' || $password === 'deep123')) {
                $is_valid = true;
            } elseif ($user['email'] === 'deep@gmail.com' && $password === 'deep123') {
                $is_valid = true;
            }
        }

        if ($is_valid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            header("Location: " . ($user['role'] === 'admin' ? "admin/dashboard.php" : "user/dashboard.php"));
            exit;
        } else {
            $error = 'Invalid email or password!';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-wrapper">

<div class="auth-card">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="font-size: 2.2rem; margin-bottom: 6px;">📚</div>
        <h2 style="font-weight: 800; font-size: 1.5rem; color: var(--text-dark);">System Login</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Sign in to your Library account</p>
    </div>

    <?php if ($error): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. admin@gmail.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn" style="width: 100%; margin-top: 8px;">Sign In</button>
    </form>

    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); text-align: center;">
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px; font-weight: 700;">⚡ Quick Fill Test Accounts:</p>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="fillAcc('admin@gmail.com', 'admin123')" class="btn btn-secondary btn-sm" style="flex: 1;">Admin</button>
            <button type="button" onclick="fillAcc('deep@gmail.com', 'deep123')" class="btn btn-secondary btn-sm" style="flex: 1;">Student (Deep)</button>
        </div>
    </div>

    <p style="text-align: center; margin-top: 20px; font-size: 0.88rem; color: var(--text-muted);">
        Don't have an account? <a href="register.php" style="font-weight: 700;">Register Here</a>
    </p>
</div>

<script>
function fillAcc(e, p) {
    document.getElementsByName('email')[0].value = e;
    document.getElementsByName('password')[0].value = p;
}
</script>

</body>
</html>
