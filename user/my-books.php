<?php
// user/my-books.php - Student Issued & Requested Books History
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Return Book Request Action
if (isset($_GET['action']) && $_GET['action'] === 'request_return' && isset($_GET['id'])) {
    $tx_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE transactions SET status = 'pending_return' WHERE id = ? AND user_id = ? AND status = 'approved'");
    if ($stmt->execute([$tx_id, $user_id])) {
        $message = 'Return request submitted! Please present the book to admin for final confirmation.';
    } else {
        $error = 'Failed to submit return request.';
    }
}

// Fetch User's Transaction History
$my_transactions = $pdo->query("SELECT t.*, b.title, b.author, b.category, b.cover_image 
                                FROM transactions t 
                                JOIN books b ON t.book_id = b.id 
                                WHERE t.user_id = $user_id 
                                ORDER BY t.requested_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Issued Books - Student Portal</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">📚 Student Portal</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
            <li><a href="books.php" class="sidebar-link">📖 Browse Books</a></li>
            <li><a href="my-books.php" class="sidebar-link active">📑 My Issued Books</a></li>
            <li><a href="../logout.php" class="sidebar-link" style="margin-top: 40px; color: #ef4444;">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1 class="page-title">My Requested & Issued Books</h1>
            <a href="books.php" class="btn btn-sm">+ Request Another Book</a>
        </header>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="card-table">
            <div class="table-header">
                <span>My Borrowing & Request History (<?php echo count($my_transactions); ?> Records)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tx ID</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Issue Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($my_transactions) > 0): ?>
                        <?php foreach ($my_transactions as $t): ?>
                            <tr>
                                <td>#TX-<?php echo $t['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($t['author']); ?></td>
                                <td><span class="category-badge"><?php echo htmlspecialchars($t['category']); ?></span></td>
                                <td><?php echo $t['issue_date'] ? date('M d, Y', strtotime($t['issue_date'])) : '-'; ?></td>
                                <td><?php echo $t['return_date'] ? date('M d, Y', strtotime($t['return_date'])) : '-'; ?></td>
                                <td><span class="badge badge-<?php echo $t['status']; ?>"><?php echo str_replace('_', ' ', $t['status']); ?></span></td>
                                <td>
                                    <?php if ($t['status'] === 'approved'): ?>
                                        <a href="my-books.php?action=request_return&id=<?php echo $t['id']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Are you sure you want to return this book?');">Return Book</a>
                                    <?php elseif ($t['status'] === 'pending_return'): ?>
                                        <span style="color: #92400e; font-size: 0.82rem; font-weight: 700;">Return Pending Admin</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">You have no book requests yet. <a href="books.php">Browse Library Catalog</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
