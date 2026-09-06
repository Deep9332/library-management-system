<?php
// admin/dashboard.php - Admin Dashboard Overview
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Key Stat Metrics
$total_books  = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$total_users  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$issued_books = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'approved'")->fetchColumn();
$avail_books  = $pdo->query("SELECT SUM(available_qty) FROM books")->fetchColumn() ?: 0;

// Recent Issue Requests
$requests = $pdo->query("SELECT t.*, u.name as user_name, u.email as user_email, b.title as book_title 
                         FROM transactions t 
                         JOIN users u ON t.user_id = u.id 
                         JOIN books b ON t.book_id = b.id 
                         ORDER BY t.requested_at DESC LIMIT 5")->fetchAll();

// Recent Students
$recent_students = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC LIMIT 4")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Library System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">📚 Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link active">📊 Dashboard</a></li>
            <li><a href="books.php" class="sidebar-link">📖 Manage Books</a></li>
            <li><a href="requests.php" class="sidebar-link">📋 Book Requests & Users</a></li>
            <li><a href="../logout.php" class="sidebar-link" style="margin-top: 40px; color: #ef4444;">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <!-- Admin Banner -->
        <div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #ffffff; padding: 24px 30px; border-radius: var(--radius); margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 4px;">System Administration Control Center</h1>
                <p style="color: #94a3b8; font-size: 0.9rem;">Manage textbook catalog, approve student issue requests, and monitor returns.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="books.php" class="btn" style="background: #2563eb; color: #fff;">+ Add New Book</a>
                <a href="requests.php" class="btn btn-secondary">Review Requests</a>
            </div>
        </div>

        <!-- 4 Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_books; ?></div>
                <div class="stat-label">Total Book Titles</div>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Registered Students</div>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-value"><?php echo $issued_books; ?></div>
                <div class="stat-label">Issued Books</div>
            </div>
            <div class="stat-card" style="border-left-color: #8b5cf6;">
                <div class="stat-value"><?php echo $avail_books; ?></div>
                <div class="stat-label">Available Copies</div>
            </div>
        </div>

        <!-- Recent Requests Table -->
        <div class="card-table">
            <div class="table-header">
                <span>Recent Book Issue Requests</span>
                <a href="requests.php" class="btn btn-secondary btn-sm">Process Requests →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Req ID</th>
                        <th>Student Name</th>
                        <th>Book Title</th>
                        <th>Requested Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td>#REQ-<?php echo $r['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($r['user_name']); ?></strong><br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($r['user_email']); ?></small></td>
                                <td><?php echo htmlspecialchars($r['book_title']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($r['requested_at'])); ?></td>
                                <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo str_replace('_', ' ', $r['status']); ?></span></td>
                                <td><a href="requests.php" class="btn btn-sm">Review</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Registered Students Overview -->
        <div class="card-table">
            <div class="table-header">
                <span>Recently Registered Students</span>
                <a href="requests.php" class="btn btn-secondary btn-sm">View All Students</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Student Name</th>
                        <th>Email Address</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_students as $u): ?>
                        <tr>
                            <td>#USR-<?php echo $u['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
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
