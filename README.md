# 📚 Library Management System - Student & Viva Guide (Hindi + English)

यह गाइड **Semester 5 Viva Examination** के लिए तैयार की गई है ताकि आप प्रोजेक्ट के हर कोड और फ्लो को आसानी से समझ सकें और टीचर को एक्सप्लेन कर सकें।

---

## 🔑 Login Credentials

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Admin** | `admin@gmail.com` | `admin123` | Admin Panel (`/admin/dashboard.php`) |
| **Student (Deep)** | `deep@gmail.com` | `deep123` | Student Panel (`/user/dashboard.php`) |

---

## 📌 1. Project Architecture (प्रोजेक्ट कैसे काम करता है)

- **Frontend**: HTML5, CSS3 (Blue + White Theme), Vanilla JavaScript.
- **Backend**: Native PHP (PDO Prepared Statements for Security).
- **Database**: MySQL (`library_db`) strictly **3 Tables**.

---

## 📄 2. File-by-File Simple Explanation

### Root Files
- **`db.php`**: Database Connector using PDO (`mysql:host=localhost;dbname=library_db`).
- **`style.css`**: Design System. Modern Blue & White styles for sidebars, stat boxes, book cards, and the Amazon-style detail page.
- **`index.php`**: Public Landing Page. Shows hero section and book cards.
- **`login.php`**: Authentication Page. Verifies user credentials using `password_verify()`.
- **`register.php`**: Registration Page. Hashes password with `password_hash()` and inserts student user.
- **`logout.php`**: Destroys session.

### Admin Panel (`admin/`)
- **`admin/dashboard.php`**: Displays 4 metric cards and recent issue requests table.
- **`admin/books.php`**: Admin can Add new books, Delete books, and Search catalog.
- **`admin/requests.php`**: Approve issue requests, Reject requests, Confirm returns, and View registered students list.

### Student User Panel (`user/`)
- **`user/dashboard.php`**: Displays student stats and active borrowed items.
- **`user/books.php`**: Browse & search all library books.
- **`user/book-details.php`**: **Amazon-Style Product Details Page**. Displays large book cover, rating stars, ISBN, Published Year, description, and an **Issue Book** button.
- **`user/my-books.php`**: Displays all student requests and **Return Book** action.
"# library-management-system" 
