<?php
// user/dashboard.php - Student User Dashboard
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// User Stats
$active_borrows = $pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = $user_id AND status = 'approved'")->fetchColumn();
$pending_req    = $pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = $user_id AND status = 'pending_issue'")->fetchColumn();
$returned_books = $pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = $user_id AND status = 'returned'")->fetchColumn();

// Currently Borrowed Active Books
$my_books = $pdo->query("SELECT t.*, b.title, b.author, b.category, b.cover_image 
                         FROM transactions t 
                         JOIN books b ON t.book_id = b.id 
                         WHERE t.user_id = $user_id AND t.status IN ('approved', 'pending_return') 
                         ORDER BY t.requested_at DESC")->fetchAll();

// Featured Books Preview for Dashboard
$popular_books = $pdo->query("SELECT * FROM books ORDER BY rating DESC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Library System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">📚 Student Portal</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link active">📊 Dashboard</a></li>
            <li><a href="books.php" class="sidebar-link">📖 Browse Books</a></li>
            <li><a href="my-books.php" class="sidebar-link">📑 My Issued Books</a></li>
            <li><a href="../logout.php" class="sidebar-link" style="margin-top: 40px; color: #ef4444;">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <!-- Welcome Header Banner -->
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff; padding: 24px 30px; border-radius: var(--radius); margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 4px;">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h1>
                <p style="color: #bfdbfe; font-size: 0.9rem;">Explore our digital collection, request textbooks, and manage your borrowed items.</p>
            </div>
            <a href="books.php" class="btn" style="background: #ffffff; color: var(--primary);">Explore Books →</a>
        </div>

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $active_borrows; ?></div>
                <div class="stat-label">Currently Borrowed</div>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-value"><?php echo $pending_req; ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="stat-value"><?php echo $returned_books; ?></div>
                <div class="stat-label">Total Returned</div>
            </div>
        </div>

        <!-- Active Borrowed Books Table -->
        <div class="card-table">
            <div class="table-header">
                <span>My Active Borrowed Books</span>
                <a href="my-books.php" class="btn btn-secondary btn-sm">View History</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Issued Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($my_books) > 0): ?>
                        <?php foreach ($my_books as $b): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($b['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($b['author']); ?></td>
                                <td><span class="category-badge"><?php echo htmlspecialchars($b['category']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($b['issue_date'])); ?></td>
                                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo $b['status'] === 'approved' ? 'Issued' : 'Return Pending'; ?></span></td>
                                <td>
                                    <?php if ($b['status'] === 'approved'): ?>
                                        <a href="my-books.php?action=request_return&id=<?php echo $b['id']; ?>" class="btn btn-secondary btn-sm">Return Book</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Pending Admin Action</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No active borrowed books right now. <a href="books.php">Browse Catalog to Request One!</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Rated Recommended Books Showcase -->
        <div style="margin-top: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-dark);">⭐ Top Rated Recommended Textbooks</h2>
                <a href="books.php" style="font-weight: 700; font-size: 0.9rem;">View All Catalog →</a>
            </div>
            <div class="book-grid">
                <?php if (count($popular_books) > 0): ?>
                    <?php foreach ($popular_books as $b): ?>
                        <div class="book-card">
                            <img src="<?php 
                                $cPath = '../uploads/' . $b['cover_image'];
                                echo file_exists($cPath) ? $cPath : '../uploads/default_cover.svg';
                            ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" class="book-cover" onerror="this.src='../uploads/default_cover.svg'">
                            <div class="book-body">
                                <div class="book-title"><?php echo htmlspecialchars($b['title']); ?></div>
                                <div class="book-author">By <?php echo htmlspecialchars($b['author']); ?></div>
                                <div class="book-meta">
                                    <span class="category-badge"><?php echo htmlspecialchars($b['category']); ?></span>
                                    <span class="stock-pill"><?php echo number_format($b['rating'], 1); ?> ⭐</span>
                                </div>
                                <div style="margin-top: 14px;">
                                    <a href="book-details.php?id=<?php echo $b['id']; ?>" class="btn btn-sm" style="width: 100%;">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 30px; background: #fff; border-radius: var(--radius); border: 1px solid var(--border);">
                        <p style="color: var(--text-muted);">No books available in the catalog yet. Admin can add books from Admin Panel.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

</body>
</html>
