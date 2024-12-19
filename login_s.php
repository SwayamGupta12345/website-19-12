<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']); // Sanitize email input
    $password = $_POST['password'];
    $secret = $_POST['secret'];

    // Fetch super admin details by email
    $query = "SELECT password_hash, secret FROM super_admin WHERE email = ? AND is_admin = 1";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // Hash the provided secret for comparison
            $hashedSecret = hash('sha256', $secret);

            // Verify hashed password and secret
            if (password_verify($password, $admin['password_hash']) && $hashedSecret === $admin['secret']) {
                $_SESSION['super_admin'] = [
                    'email' => $email
                    // 'secret' => $admin['secret']
                ]; // Store email and hashed secret in session

                // Redirect to dashboard or home page
                header("Location: super_admin.php");
                exit;
            } else {
                echo "<p style='color: red;'>Invalid email, password, or secret!</p>";
            }
        } else {
            echo "<p style='color: red;'>Invalid email, password, or secret!</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>Error preparing the statement.</p>";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login</title>
    <link rel="stylesheet" href="inde.css">
    <link rel="stylesheet" href="admin_panel.css">
    <link rel="stylesheet" href="add_subject.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div class="main-content">
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
    </nav>

    <div class="main-content">
        <div class="ap_container">
            <h2 style="text-align: center; font-size: 30px;">Login</h2>
            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email">Email</label><br>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label><br>
                    <input type="password" id="password" name="password" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="secret">Secret Key</label><br>
                    <input type="text" id="secret" name="secret" required autocomplete="off">
                </div>
                <div class="soption">
                    <button type="submit" class="login-btn">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
<script>
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
</body>
</html>
