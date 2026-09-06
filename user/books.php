<?php
// user/books.php - Browse & Search Books Catalog
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// Search and Category Filters
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$query = "SELECT * FROM books WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term, $term];
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

// Distinct categories for quick filter chips
$categories = $pdo->query("SELECT DISTINCT category FROM books ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books - Student Portal</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">📚 Student Portal</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
            <li><a href="books.php" class="sidebar-link active">📖 Browse Books</a></li>
            <li><a href="my-books.php" class="sidebar-link">📑 My Issued Books</a></li>
            <li><a href="../logout.php" class="sidebar-link" style="margin-top: 40px; color: #ef4444;">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1 class="page-title">Library Book Catalog</h1>
            <a href="my-books.php" class="btn btn-secondary btn-sm">My Requests</a>
        </header>

        <!-- Search Bar -->
        <div style="margin-bottom: 24px;">
            <form method="GET" action="books.php" style="display: flex; gap: 12px;">
                <input type="text" name="search" class="form-control" placeholder="Search by title, author, category, or ISBN..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn" style="width: auto;">Search</button>
                <?php if ($search || $category): ?>
                    <a href="books.php" class="btn btn-secondary" style="width: auto;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Category Filter Pills -->
        <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
            <a href="books.php" class="btn btn-sm <?php echo empty($category) ? 'btn-primary' : 'btn-secondary'; ?>">All Categories</a>
            <?php foreach ($categories as $cat): ?>
                <a href="books.php?category=<?php echo urlencode($cat); ?>" class="btn btn-sm <?php echo $category === $cat ? 'btn-primary' : 'btn-secondary'; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Book Cards Grid -->
        <div class="book-grid">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $b): ?>
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
                                <span class="stock-pill <?php echo $b['available_qty'] > 0 ? '' : 'out'; ?>">
                                    <?php echo $b['available_qty'] > 0 ? $b['available_qty'] . ' Available' : 'Out of Stock'; ?>
                                </span>
                            </div>
                            <div style="margin-top: 14px;">
                                <a href="book-details.php?id=<?php echo $b['id']; ?>" class="btn btn-sm" style="width: 100%;">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff; border-radius: var(--radius); border: 1px solid var(--border);">
                    <p style="color: var(--text-muted);">No books found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
