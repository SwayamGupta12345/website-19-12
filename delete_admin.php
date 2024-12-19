<?php
session_start();

if (!isset($_SESSION['super_admin']) || empty($_SESSION['super_admin']['email'])) {
    header("Location: index.php"); // Redirect to login page if not logged in or email is empty
    exit();
}

include 'connection.php';

// Verify if the user is a super admin
$email = $_SESSION['super_admin']['email'];
$stmt = $conn->prepare("SELECT is_admin FROM super_admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($isAdmin);
    $stmt->fetch();
    if (!$isAdmin) {
        header("Location: index.php"); // Redirect if not a super admin
        exit();
    }
} else {
    header("Location: index.php"); // Redirect if no super admin record found
    exit();
}
$stmt->close();

// Check if delete request is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminEmail = trim($_POST['admin_email']);

    // Validate the email
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    // Prevent deletion of the current super admin
    if ($adminEmail === $_SESSION['super_admin']['email']) {
        echo "You cannot delete yourself.";
        exit(); 
    }

    // Delete admin from database
    $stmt = $conn->prepare("DELETE FROM admin WHERE email = ?");
    $stmt->bind_param("s", $adminEmail);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<script>alert('Admin deleted successfully.'); window.location.href = 'super_admin.php';</script>";
    } else {
        echo "Admin not found or error occurred.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Admin</title>
    <link rel="stylesheet" href="inde.css">
    <link rel="stylesheet" href="admin_panel.css">
    <link rel="stylesheet" href="add_subject.css">
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
        <div class="ap_container">
            <h1>Delete Admin</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="admin_email">Admin Email:</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                </div>
                <h3>Note: Deleting an admin is irreversible. Double-check the details before submission.</h3>
                <div class="soption">
                    <input type="submit" value="Delete Admin">
                    <a href="super_admin.php" class="back">Return to Admin Panel</a>
                </div>
            </form>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
<script>
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
            nav.classList.add('sticky');
            placeholder.style.height = `${navHeight}px`;
        } else {
            nav.classList.remove('sticky');
            placeholder.style.height = '0';
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