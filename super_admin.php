<?php
session_start();

if (!isset($_SESSION['super_admin']) || empty($_SESSION['super_admin']['email'])) {
    header("Location: index.php"); // Redirect to login page if not logged in or email is empty
    exit();
}

include 'connection.php';

// Verify if user is admin
$email = $_SESSION['super_admin']['email'];
$stmt = $conn->prepare("SELECT is_admin FROM super_admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($isAdmin);
    $stmt->fetch();
    if (!$isAdmin) {
        header("Location: index.php"); // Redirect if not an admin
        exit();
    }
} else {
    header("Location: index.php"); // Redirect if no admin record found
    exit();
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="inde.css">
    <link rel="stylesheet" href="admin_panel.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo-text">
            <img src="jaypee_main_logo.jpeg" alt="Jaypee Learning Hub" class="logo">
            <h1>Jaypee Learning Hub</h1>
        </div>
    </header>

    <nav>
        <div class="burger" id="burger-menu">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <a class="home" href="index.php">HOME</a>
        <div class="nav-links" id="nav-links">

        <span class="logout">
            <a href="admin_panel.php" class="admin">Admin Panel</a>
        </span>
        <span class="logout">
            <a href="super_admin.php" class="admin">Super Admin Panel</a>
        </span>
        <?php
        // Split the email and get the part before the "@"
        $user_name = explode('@', $_SESSION['super_admin']['email'])[0]; ?>
        <span class="user-email">
            <?php echo $user_name; ?>
        </span>
        <span class="logout">
            <a href="index1.php">Main Page</a>
        </span>
        <span class="logout">
            <a href="index.php?logout=true">Logout</a>
        </span>
    </div>
    </nav>

    <div class="main-content">
        <div class="card-container">
            <a class="card card1" href="add_subject.php">
                <h3>Add New Subject</h3>
            </a>
            <a class="card card1" href="edit_subject.php">
                <h3>Edit a Subject</h3>
            </a>
            <a class="card" href="delete_subject.php">
                <h3>Delete a Subject</h3>
            </a>
            <a class="card" href="add_link.php">
                <h3>Add a Data Link</h3>
            </a>
            <a class="card" href="edit_link.php">
                <h3>Edit a Data Link</h3>
            </a>
            <a class="card" href="delete_link.php">
                <h3>Delete a Data Link</h3>
            </a>
            <a class="card" href="add_announcements.php">
                <h3>Add New Announcement</h3>
            </a>
            <a class="card" href="edit_announcements.php">
                <h3>Edit Announcement</h3>
            </a>
            <a class="card" href="delete_announcements.php">
                <h3>Delete Announcement</h3>
            </a>
            <a class="card" href="add_news.php">
                <h3>Add News</h3>
            </a>
            <a class="card" href="edit_news.php">
                <h3>Edit News</h3>
            </a>
            <a class="card" href="delete_news.php">
                <h3>Delete News</h3>
            </a>
            <a class="card" href="add_admin.php">
                <h3>Add Admin</h3>
            </a>
            <a class="card" href="delete_admin.php">
                <h3>Delete Admin</h3>
            </a>        
        </div>
    </div>
    <?php include "footer.php"; ?>
</body>
<script>
    // Toggle the menu visibility on burger menu click
    document.addEventListener("DOMContentLoaded", function() {
        const burger = document.getElementById('burger-menu');
        const navLinks = document.getElementById('nav-links');

        burger.addEventListener('click', function(event) {
            event.stopPropagation();
            navLinks.classList.toggle('show');
        });

        window.addEventListener('click', function(event) {
            if (!burger.contains(event.target) && !navLinks.contains(event.target)) {
                navLinks.classList.remove('show');
            }
        });
    });
    const nav = document.querySelector('nav');
    const placeholder = document.createElement('div');
    placeholder.classList.add('nav-placeholder');
    nav.parentNode.insertBefore(placeholder, nav);

    const navHeight = nav.offsetHeight;

    window.addEventListener('scroll', () => {
        if (window.scrollY > nav.offsetTop) {
            nav.classList.add('sticky'); // Make nav sticky
            placeholder.style.height = `${navHeight}px`; // Set placeholder height
        } else {
            nav.classList.remove('sticky'); // Remove sticky behavior
            placeholder.style.height = '0'; // Reset placeholder height
        }
    });

    window.addEventListener('resize', () => {
        const navLinks = document.querySelector('.nav-links');
        if (window.innerWidth > 800) {
            navLinks.classList.remove('show');
        }
    });

</script>
</html>

<?php
$conn->close();
?>
