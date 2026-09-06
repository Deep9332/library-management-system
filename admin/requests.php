<?php
// admin/requests.php - Manage Requests & Users
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

// Handle Actions: Approve, Reject, Confirm Return
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $action = $_GET['action'];

    $stmt = $pdo->prepare("SELECT t.*, b.available_qty FROM transactions t JOIN books b ON t.book_id = b.id WHERE t.id = ?");
    $stmt->execute([$id]);
    $tx = $stmt->fetch();

    if ($tx) {
        if ($action === 'approve') {
            if ($tx['available_qty'] > 0) {
                $pdo->query("UPDATE transactions SET status = 'approved', issue_date = CURDATE() WHERE id = $id");
                $pdo->query("UPDATE books SET available_qty = available_qty - 1 WHERE id = {$tx['book_id']}");
                $message = 'Request approved! Book issued.';
            } else {
                $error = 'Book is out of stock!';
            }
        } elseif ($action === 'reject') {
            $pdo->query("UPDATE transactions SET status = 'rejected' WHERE id = $id");
            $message = 'Request rejected.';
        } elseif ($action === 'confirm_return') {
            $pdo->query("UPDATE transactions SET status = 'returned', return_date = CURDATE() WHERE id = $id");
            $pdo->query("UPDATE books SET available_qty = available_qty + 1 WHERE id = {$tx['book_id']}");
            $message = 'Book return confirmed! Stock restored.';
        }
    }
}

// Fetch All Requests
$requests = $pdo->query("SELECT t.*, u.name as user_name, u.email as user_email, b.title as book_title 
                         FROM transactions t 
                         JOIN users u ON t.user_id = u.id 
                         JOIN books b ON t.book_id = b.id 
                         ORDER BY t.requested_at DESC")->fetchAll();

// Fetch All Registered Users
$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requests & Users - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">📚 Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
            <li><a href="books.php" class="sidebar-link">📖 Manage Books</a></li>
            <li><a href="requests.php" class="sidebar-link active">📋 Book Requests & Users</a></li>
            <li><a href="../logout.php" class="sidebar-link" style="margin-top: 40px; color: #ef4444;">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1 class="page-title">Manage Issue Requests & Registered Users</h1>
        </header>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <!-- Requests Table -->
        <div class="card-table">
            <div class="table-header">
                <span>Book Issue & Return Requests (<?php echo count($requests); ?>)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tx ID</th>
                        <th>Student Info</th>
                        <th>Book Title</th>
                        <th>Issue Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td>#TX-<?php echo $r['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($r['user_name']); ?></strong><br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($r['user_email']); ?></small></td>
                                <td><?php echo htmlspecialchars($r['book_title']); ?></td>
                                <td><?php echo $r['issue_date'] ? date('M d, Y', strtotime($r['issue_date'])) : '-'; ?></td>
                                <td><?php echo $r['return_date'] ? date('M d, Y', strtotime($r['return_date'])) : '-'; ?></td>
                                <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo str_replace('_', ' ', $r['status']); ?></span></td>
                                <td>
                                    <?php if ($r['status'] === 'pending_issue'): ?>
                                        <a href="requests.php?action=approve&id=<?php echo $r['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                        <a href="requests.php?action=reject&id=<?php echo $r['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                                    <?php elseif ($r['status'] === 'pending_return' || $r['status'] === 'approved'): ?>
                                        <a href="requests.php?action=confirm_return&id=<?php echo $r['id']; ?>" class="btn btn-primary btn-sm">Confirm Return</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Users Table -->
        <div class="card-table">
            <div class="table-header">
                <span>Registered Users List</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#USR-<?php echo $u['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><span class="badge badge-<?php echo $u['role'] === 'admin' ? 'approved' : 'returned'; ?>"><?php echo strtoupper($u['role']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
