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
} // Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['subject']);
    $password = trim($_POST['apass']);

    // Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    if (strlen($password) < 6) {
        echo "Password must be at least 6 characters.";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO admin (email, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script> alert('New admin added successfully.')</script>";
        header('location:super_admin.php');
    } else {
        if ($conn->errno == 1062) {
            echo "Admin with this email already exists.";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
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
        <div class="ap_container">
            <h1>Add New Admin</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="admin email">Admin Email:</label>
                    <input type="email" id="admin email" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="admin_pass">Admin password:</label>
                    <input type="password" id="admin_pass" name="apass" required>
                </div>
                <h3>Note: Check the details twice before submission.</h3>
                <div class="soption">
                    <input type="submit" value="Add Admin">
                    <a href="super_admin.php" class="back">Return to Admin</a>
                </div>
            </form>
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