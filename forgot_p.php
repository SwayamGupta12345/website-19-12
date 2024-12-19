<?php
// forgot_p.php
session_start();
include "connection.php"; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    $newPassword = $_POST['new_password']; // Assuming the user enters the new password

    // Validate email format
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if the email exists in the users table
        $stmt = $conn->prepare("SELECT secret_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Fetch the stored secret hash
            $stmt->bind_result($storedSecret);
            $stmt->fetch();

            // Get the entered secret string from the form
            $enteredSecret = $_POST['secret']; // The user enters the secret string

            // Hash the entered secret string using SHA-256
            $enteredSecretHash = hash('sha256', $enteredSecret);

            // Verify if the entered secret matches the stored hash
            if ($enteredSecretHash === $storedSecret) {
                // If the secret matches, update the user's password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the new password
                $updateStmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $hashedPassword, $email);
                if ($updateStmt->execute()) {
                    $_SESSION['message'] = "Password has been reset successfully.";
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Failed to reset the password. Please try again.";
                }
                $updateStmt->close();
            } else {
                $error = "Invalid secret string. Please try again.";
            }
        } else {
            $error = "Email not found in our records.";
        }

        $stmt->close();
    } else {
        $error = "Please enter a valid email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        :root {
            /* Inherit existing colors */
            --background-main: #F2E9E4;
            --background-button: #4A4E69;
            --text-primary: #22223B;
            --text-light: #F2E9E4;
            --border-primary: #4A4E69;
        }
        body {
            background-color: var(--background-main);
            color: var(--text-primary);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .forgot-password-container {
            background: #fff;
            padding: 20px;
            border: 1px solid var(--border-primary);
            border-radius: 8px;
            box-shadow: 0px 4px 8px var(--box-shadow-color);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 10px;
            text-align: center;
            color: var(--text-primary);
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="email"], input[type="text"], input[type="password"] {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid var(--border-primary);
            border-radius: 4px;
        }
        button {
            background: var(--background-button);
            color: var(--text-light);
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        button:hover {
            background: var(--background-nav-link-hover);
        }
        .message, .error {
            margin: 10px 0;
            text-align: center;
            font-size: 0.9em;
        }
        .message {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="text" name="secret" placeholder="Enter your secret string" required> <!-- Added input for secret -->
            <input type="password" name="new_password" placeholder="Enter new password" required> <!-- Added input for new password -->
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php elseif (isset($error)): ?>
            <div class="error"><?= $error; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
