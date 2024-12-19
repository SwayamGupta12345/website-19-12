<?php
session_start(); // This must be the very first thing in your script

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (isset($_GET['logout'])) {
    session_start(); // Start session to access it
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session completely
    setcookie(session_name(), '', time() - 3600, '/'); // Clear session cookie
    header("Location: index.php"); // Redirect to login or homepage
    exit();
}
include 'connection.php';

// Handle user registration 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = $_POST['reg_email'];
    $password = $_POST['reg_password'];
    $secret = $_POST['secret'];

    // Regular expression for stronger email validation
    $pattern = "/^(23|24|25|26|27|28|29)[0-9]{8}|(2[3-9][0-9]{6}|99(23|24|25|26|27|28|29)[0-9]{4}|JEG(23|24|25|26|27|28|29)[0-9]{4}|NRG(23|24|25|26|27|28|29)[0-9]{4}|ECN(23|24|25|26|27|28|29)[0-9]{4}|NJG(23|24|25|26|27|28|29)[0-9]{4})@mail\.jiit\.ac\.in$/";

    // Check if email matches the pattern
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match($pattern, $email)) {
        echo "<script>alert('Invalid email format. Please use an appropriate JIIT email address.');</script>";
    } else {
        // Check if the email already exists
        $checkEmailStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();

        if ($checkEmailStmt->num_rows > 0) {
            // Email already exists, show an error message
            echo "<script>alert('This email is already registered. Please use a different email or login.');</script>";
        } else {
            // Hash the password and secret string
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $secretHash = hash('sha256', $secret); // Use SHA-256 for hashing the secret string

            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO users (email, password_hash, secret_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $passwordHash, $secretHash);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Registration successful! Now Login');
                    window.location.href = 'index.php';
                </script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }

        $checkEmailStmt->close();
    }
}

//handle forgot password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_pass'])) {
    header("Location: forgot_p.php");
}
// Handle user login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Trim input to remove extra spaces
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if the email and password are not empty
    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in both email and password.');</script>";
        exit();
    }

    // First, check if the email exists in the admin table
    $stmt = $conn->prepare("SELECT password_hash FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Admin found
        $stmt->bind_result($passwordHash);
        $stmt->fetch();

        // Verify the password for admin
        if (password_verify($password, $passwordHash)) {
            // Store session data for admin
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = "admin";

            header("Location: admin_panel.php");
            exit();
        } else {
            echo "<script>alert('Invalid credentials.');</script>";
        }
    } else {
        // If not found in admin, check the users table
        $stmt->close(); // Close the previous statement

        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User found   
            $stmt->bind_result($passwordHash);
            $stmt->fetch();

            // Verify the password for user
            if (password_verify($password, $passwordHash)) {
                // Store session data for regular user
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = "user";
                header("Location: index1.php"); // Redirect to a regular page
                exit();
            } else {
                echo "<script>alert('Invalid credentials.');</script>";
            }
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Jaypee Learning Platform</title>
    <link rel="stylesheet" href="inde.css">
    <link rel="stylesheet" href="phone.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <div class="logo-text">
            <img src="jaypee_main_logo.jpeg" alt="Jaypee Learning Hub" class="logo">
            <h1>Jaypee Learning Hub</h1>
        </div>
    </header>
    <nav class="navb">
        <div class="nav-bar">
            <div class="login-but">
                <a href="#" class="login-button">Login</a>
            </div>
            <div class="register-but">
                <a href="#" id="show-register">Register</a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="flex-col login-form" id="login-form">
        <button class="close-button" id="close-button">&times;</button> <!-- Close Button -->
        <h2>Login</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <form method="POST">
            <button type="submit" name="forgot_pass">Forgot Password</button>
            <p>Not a user? <a href="#" id="show-register-login">Don't worry, register first!</a></p> <!-- Updated ID -->
        </form>
        <div id="message"></div> <!-- For displaying messages -->
    </div>

    <!-- Registration Form -->
<div class="flex-col register-form" id="register-form">
    <button class="close-button" id="close-register-button">&times;</button> <!-- Close Button -->
    <h2>Register</h2>
    <form method="POST" action="">
        <input type="email" name="reg_email" placeholder="Email" required>
        <input type="password" name="reg_password" placeholder="Create Password" required>
        
        <!-- Styled Paragraph -->
        <p class="special-string-info"><b>Enter a special string that you will remember.</b></p>
        
        <input type="text" name="secret" placeholder="Your Pet's name or something" required>
        <button type="submit" name="register">Register</button>
    </form>
    <p>Remember your username and password? <a href="#" id="login-button">Don't worry, login!</a></p>
    <div id="register-message"></div> <!-- For displaying registration messages -->
</div>
    <div class="main-content"><?php
        include 'show_an.php';
        include 'helpbox.php'; ?>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        // Toggle dropdown menu
        // Close dropdown if clicking outside
        window.onclick = function(event) {
            const emailElement = document.querySelector('.user-email');
            const dropdown = document.getElementById('dropdown-content');
            const navLinks = document.getElementById('nav-links');

            // Close dropdown if clicking outside of email and dropdown
            if (dropdown.classList.contains('show') && !emailElement.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }

            // Close nav-links if clicking outside of  icon and nav-links
            if (navLinks.classList.contains('show') && !navLinks.contains(event.target)) {
                navLinks.classList.remove('show');
            }
        };

        // Close the menu when clicking any link inside the nav-links
        document.querySelectorAll('#nav-links a').forEach(function(link) {
            link.addEventListener('click', function() {
                document.getElementById('nav-links').classList.remove('show');
            });
        });

        // Toggle login form on "Login" button click
        document.querySelector('.login-button').addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation(); // Prevent the document click listener from firing
            document.getElementById('login-form').classList.toggle('active');
        });

        // Close login form on close button click
        document.getElementById('close-button').addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent the document click listener from firing
            document.getElementById('login-form').classList.remove('active');
        });

        // Show registration form when "register first" is clicked in login form
        document.getElementById('show-register-login').addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation(); // Prevent the document click listener from firing
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('register-form').classList.add('active');
        });

        // Show registration form when "register first" is clicked
        document.getElementById('show-register').addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation(); // Prevent the document click listener from firing
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('register-form').classList.add('active');
        });

        // // Show login form when "login" is clicked on registration form
        document.getElementById('login-button').addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation(); // Prevent the document click listener from firing
            document.getElementById('register-form').classList.remove('active');
            document.getElementById('login-form').classList.add('active');
        });
        // Close button functionality for the registration form
        document.getElementById('close-register-button').addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent the document click listener from firing
            document.getElementById('register-form').classList.remove('active');
        });


        // Close login or registration form when clicking outside
        document.addEventListener('click', function(event) {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');

            // Close login form if active and clicked outside
            if (loginForm.classList.contains('active') && !loginForm.contains(event.target)) {
                loginForm.classList.remove('active');
            }

            // Close register form if active and clicked outside
            if (registerForm.classList.contains('active') && !registerForm.contains(event.target)) {
                registerForm.classList.remove('active');
            }
        });
    </script>
</body>

</html>