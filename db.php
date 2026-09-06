<?php
// db.php - Database connection & Auto-Setup
$host = 'localhost';
$db   = 'library_db';
$user = 'root';
$pass = '';

try {
    // 1. Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // 2. Ensure database exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    // 3. Auto-Create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('admin', 'user') DEFAULT 'user',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `books` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `author` VARCHAR(255) NOT NULL,
            `category` VARCHAR(100) NOT NULL,
            `isbn` VARCHAR(20) NOT NULL,
            `pub_year` INT NOT NULL,
            `rating` DECIMAL(2,1) DEFAULT 4.5,
            `description` TEXT NOT NULL,
            `cover_image` VARCHAR(255) DEFAULT 'default_cover.svg',
            `quantity` INT NOT NULL DEFAULT 1,
            `available_qty` INT NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS `transactions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `book_id` INT NOT NULL,
            `issue_date` DATE NULL,
            `return_date` DATE NULL,
            `status` ENUM('pending_issue', 'approved', 'rejected', 'pending_return', 'returned') DEFAULT 'pending_issue',
            `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE
        );
    ");

    // 4. Ensure default Admin & Student accounts exist
    $checkUser = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    if ($checkUser == 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $deepPass  = password_hash('deep123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES 
            (1, 'System Administrator', 'admin@gmail.com', ?, 'admin'),
            (2, 'Deep Student', 'deep@gmail.com', ?, 'user')
        ");
        $stmt->execute([$adminPass, $deepPass]);
    }

} catch (PDOException $e) {
    die("<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin: 40px auto; max-width: 600px;'>
        <h2>Database Connection Error</h2>
        <p>Could not connect to MySQL server on <strong>$host</strong>.</p>
        <p><strong>Fixing Steps:</strong></p>
        <ol style='line-height: 1.6;'>
            <li>Open XAMPP Control Panel and ensure <strong>MySQL is Started</strong> (Green).</li>
            <li>If MySQL password is not blank, edit <code>$pass</code> in <code>db.php</code>.</li>
        </ol>
        <p><small>Details: " . htmlspecialchars($e->getMessage()) . "</small></p>
    </div>");
}
?>
