<?php
// user/book-details.php - Amazon-Style Book Details Product Page
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$message = '';
$error = '';

// Fetch Book Details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: books.php");
    exit;
}

// Handle Issue Book Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_issue'])) {
    if ($book['available_qty'] < 1) {
        $error = 'Sorry, this book is currently out of stock!';
    } else {
        // Check existing user request
        $check = $pdo->prepare("SELECT status FROM transactions WHERE user_id = ? AND book_id = ? AND status IN ('pending_issue', 'approved', 'pending_return')");
        $check->execute([$user_id, $book_id]);
        $existing = $check->fetch();

        if ($existing) {
            $error = 'You already have an active request or borrowed copy for this book!';
        } else {
            $ins = $pdo->prepare("INSERT INTO transactions (user_id, book_id, status) VALUES (?, ?, 'pending_issue')");
            if ($ins->execute([$user_id, $book_id])) {
                $message = 'Book issue request submitted successfully! Pending admin approval.';
            } else {
                $error = 'Failed to submit request.';
            }
        }
    }
}

// Check existing request status for button state
$user_status_stmt = $pdo->prepare("SELECT status FROM transactions WHERE user_id = ? AND book_id = ? AND status IN ('pending_issue', 'approved', 'pending_return')");
$user_status_stmt->execute([$user_id, $book_id]);
$active_status = $user_status_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($book['title']); ?> - Book Details</title>
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
        <div style="margin-bottom: 20px;">
            <a href="books.php" style="font-weight: 700; color: var(--text-muted); font-size: 0.9rem;">← Back to Catalog</a>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <!-- Amazon-Style Product Container -->
        <div class="product-container">
            <!-- Left Column: Cover Image -->
            <div class="product-image-box">
                <img src="<?php 
                    $cPath = '../uploads/' . $book['cover_image'];
                    echo file_exists($cPath) ? $cPath : '../uploads/default_cover.svg';
                ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" onerror="this.src='../uploads/default_cover.svg'">
            </div>

            <!-- Right Column: Book Details & Actions -->
            <div class="product-info">
                <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                <div class="product-author">By <strong><?php echo htmlspecialchars($book['author']); ?></strong></div>

                <div class="product-rating">
                    <span class="stars">
                        <?php 
                            $r = round($book['rating']);
                            for ($i = 0; $i < 5; $i++) {
                                echo $i < $r ? '★' : '☆';
                            }
                        ?>
                    </span>
                    <span class="rating-num"><?php echo number_format($book['rating'], 1); ?> / 5.0 Rating</span>
                    <span class="category-badge" style="margin-left: 10px;"><?php echo htmlspecialchars($book['category']); ?></span>
                </div>

                <!-- Specifications Box -->
                <div class="specs-grid">
                    <div class="spec-item">
                        <label>ISBN Number</label>
                        <span><?php echo htmlspecialchars($book['isbn']); ?></span>
                    </div>
                    <div class="spec-item">
                        <label>Published Year</label>
                        <span><?php echo $book['pub_year']; ?></span>
                    </div>
                    <div class="spec-item">
                        <label>Available Copies</label>
                        <span style="color: <?php echo $book['available_qty'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                            <?php echo $book['available_qty']; ?> of <?php echo $book['quantity']; ?>
                        </span>
                    </div>
                    <div class="spec-item">
                        <label>Status</label>
                        <span><?php echo $book['available_qty'] > 0 ? 'In Stock' : 'Out of Stock'; ?></span>
                    </div>
                </div>

                <!-- Description -->
                <div class="product-desc">
                    <h3>Book Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>

                <!-- Action Form / Button -->
                <form method="POST" action="book-details.php?id=<?php echo $book['id']; ?>">
                    <input type="hidden" name="request_issue" value="1">
                    
                    <?php if ($active_status): ?>
                        <button type="button" class="btn btn-secondary" disabled style="padding: 14px 28px; font-size: 1rem;">
                            Already Requested (Status: <?php echo str_replace('_', ' ', $active_status); ?>)
                        </button>
                    <?php elseif ($book['available_qty'] < 1): ?>
                        <button type="button" class="btn btn-danger" disabled style="padding: 14px 28px; font-size: 1rem;">
                            Currently Out of Stock
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn" style="padding: 14px 32px; font-size: 1rem;">
                            📖 Request / Issue Book
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
