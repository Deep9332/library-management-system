<?php
// admin/books.php - Manage Books (Add, Edit, Delete, Search)
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

// Handle Add Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title       = trim($_POST['title']);
    $author      = trim($_POST['author']);
    $category    = trim($_POST['category']);
    $isbn        = trim($_POST['isbn']);
    $pub_year    = (int)$_POST['pub_year'];
    $rating      = (float)$_POST['rating'];
    $description = trim($_POST['description']);
    $quantity    = (int)$_POST['quantity'];

    if (empty($title) || empty($author) || empty($category) || empty($isbn) || $quantity < 1) {
        $error = 'Please fill in all required book details correctly.';
    } else {
        $cover_image = 'default_cover.svg';
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $fileName = $_FILES['cover']['name'];
            $fileTmpPath = $_FILES['cover']['tmp_name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            if (in_array($fileExtension, $allowed)) {
                $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                    $cover_image = $newFileName;
                }
            }
        }

        $stmt = $pdo->prepare("INSERT INTO books (title, author, category, isbn, pub_year, rating, description, cover_image, quantity, available_qty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $author, $category, $isbn, $pub_year, $rating, $description, $cover_image, $quantity, $quantity])) {
            $message = 'New book added successfully!';
        } else {
            $error = 'Failed to add book.';
        }
    }
}

// Handle Delete Book
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $del_stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        $message = 'Book deleted successfully.';
    } else {
        $error = 'Failed to delete book.';
    }
}

// Fetch Books
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ? ORDER BY id DESC");
    $term = "%$search%";
    $stmt->execute([$term, $term, $term, $term]);
} else {
    $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
}
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">📚 Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link">📊 Dashboard</a></li>
            <li><a href="books.php" class="sidebar-link active">📖 Manage Books</a></li>
            <li><a href="requests.php" class="sidebar-link">📋 Book Requests & Users</a></li>
            <li><a href="../logout.php" class="sidebar-link" style="margin-top: 40px; color: #ef4444;">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1 class="page-title">Manage Library Books</h1>
        </header>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <!-- Add Book Form Card -->
        <div class="card-table" style="padding: 24px; margin-bottom: 28px;">
            <h2 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 18px; color: var(--text-dark);">+ Add New Book to Library</h2>
            
            <form method="POST" action="books.php" enctype="multipart/form-data">
                <input type="hidden" name="add_book" value="1">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Book Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Clean Code" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Author Name *</label>
                        <input type="text" name="author" class="form-control" placeholder="e.g. Robert C. Martin" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Software Engineering" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ISBN Number *</label>
                        <input type="text" name="isbn" class="form-control" placeholder="e.g. 978-0132350884" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Published Year *</label>
                        <input type="number" name="pub_year" class="form-control" value="2021" min="1900" max="2030" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rating (1.0 to 5.0)</label>
                        <input type="number" step="0.1" name="rating" class="form-control" value="4.5" min="1.0" max="5.0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Total Quantity *</label>
                        <input type="number" name="quantity" class="form-control" value="3" min="1" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cover Image (Optional)</label>
                        <input type="file" name="cover" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label class="form-label">Short Book Description *</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter brief overview of the book..." required></textarea>
                </div>

                <button type="submit" class="btn" style="margin-top: 6px;">Add Book to Catalog</button>
            </form>
        </div>

        <!-- Books List Table -->
        <div class="card-table">
            <div class="table-header">
                <span>Book Inventory (<?php echo count($books); ?> Titles)</span>
                <form method="GET" style="display: flex; gap: 8px;">
                    <input type="text" name="search" class="form-control" placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 6px 12px; font-size: 0.85rem; width: 200px;">
                    <button type="submit" class="btn btn-sm">Search</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>ISBN</th>
                        <th>Year</th>
                        <th>Qty</th>
                        <th>Avail</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $b): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($b['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($b['author']); ?></td>
                            <td><span class="category-badge"><?php echo htmlspecialchars($b['category']); ?></span></td>
                            <td><small style="font-family: monospace;"><?php echo htmlspecialchars($b['isbn']); ?></small></td>
                            <td><?php echo $b['pub_year']; ?></td>
                            <td><?php echo $b['quantity']; ?></td>
                            <td><strong style="color: <?php echo $b['available_qty'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>;"><?php echo $b['available_qty']; ?></strong></td>
                            <td>
                                <a href="books.php?delete=<?php echo $b['id']; ?>" onclick="return confirm('Are you sure you want to delete this book?');" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
