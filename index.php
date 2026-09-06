<?php
// index.php - Landing Page with Hero & Featured Books Showcase
session_start();
require_once 'db.php';

// Fetch sample books for popular books grid
$stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 4");
$popular_books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header style="background: #ffffff; border-bottom: 1px solid var(--border); padding: 16px 40px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 1.3rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px;">
            <span>📚</span> LibManager
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'; ?>" class="btn btn-sm">My Dashboard</a>
                <a href="logout.php" class="btn btn-secondary btn-sm" style="margin-left: 8px;">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary btn-sm">Sign In</a>
                <a href="register.php" class="btn btn-sm" style="margin-left: 8px;">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff; padding: 70px 20px; text-align: center;">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 14px;">Smart Library Management System</h1>
        <p style="font-size: 1.1rem; color: #bfdbfe; max-width: 600px; margin: 0 auto 28px;">Search, explore, and request books online. Simple, fast, and easy for students and admins.</p>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user/books.php" class="btn" style="background: #ffffff; color: var(--primary);">Browse Catalog →</a>
            <?php else: ?>
                <a href="register.php" class="btn" style="background: #ffffff; color: var(--primary);">Get Started Now</a>
                <a href="login.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #fff; margin-left: 10px;">Login Account</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Container -->
    <div style="max-width: 1100px; margin: 40px auto; padding: 0 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.5rem; font-weight: 800;">Featured Library Books</h2>
            <a href="user/books.php" style="font-weight: 700;">View All Books →</a>
        </div>

        <!-- Book Cards Grid -->
        <div class="book-grid">
            <?php if (count($popular_books) > 0): ?>
                <?php foreach ($popular_books as $book): ?>
                    <div class="book-card">
                        <img src="<?php 
                            $img = 'uploads/' . $book['cover_image'];
                            echo file_exists($img) ? $img : 'uploads/default_cover.svg'; 
                        ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover" onerror="this.src='uploads/default_cover.svg'">
                        <div class="book-body">
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author">By <?php echo htmlspecialchars($book['author']); ?></div>
                            <div class="book-meta">
                                <span class="category-badge"><?php echo htmlspecialchars($book['category']); ?></span>
                                <span class="stock-pill <?php echo $book['available_qty'] > 0 ? '' : 'out'; ?>">
                                    <?php echo $book['available_qty'] > 0 ? $book['available_qty'] . ' Available' : 'Out of Stock'; ?>
                                </span>
                            </div>
                            <div style="margin-top: 14px;">
                                <a href="<?php echo isset($_SESSION['user_id']) ? 'user/book-details.php?id=' . $book['id'] : 'login.php'; ?>" class="btn btn-sm" style="width: 100%;">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff; border-radius: var(--radius); border: 1px solid var(--border);">
                    <p style="color: var(--text-muted); font-size: 1.05rem;">No books currently in the library catalog. Admin can log in to add new books!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer style="text-align: center; padding: 30px; color: var(--text-muted); border-top: 1px solid var(--border); margin-top: 60px; font-size: 0.9rem;">
        <p>Semester 5 Project — <strong>Library Management System</strong></p>
    </footer>

</body>
</html>
