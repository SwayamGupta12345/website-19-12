<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

include 'connection.php';

// Verify if user is super admin
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

// Verify if the user is an admin
$email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT is_admin FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($isAdmin);
    $stmt->fetch();
    if (!$isAdmin) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = $_POST['data'];
    $link = $_POST['link'];
    $end_date = $_POST['end_date'];

    // Insert new news item with 'added_by'
    $stmt = $conn->prepare("INSERT INTO news (data, link, end_date, added_by) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $data, $link, $end_date, $email); // Here, $email is the logged-in user's email
        if ($stmt->execute()) {
            echo "<script>alert('News added successfully.');</script>";
        } else {
            echo "<script>alert('Error adding news: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
    header("Location: add_news.php");
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add News</title>
    <link rel="stylesheet" href="inde.css">
    <link rel="stylesheet" href="admin_panel.css">
    <link rel="stylesheet" href="add_subject.css"> 
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include "admin_nav.php"; ?>
    <div class="main-content">
    <div class="ap_container">
        <h1>Add News</h1>
        <form method="POST" class="form">
            <div class="form-group">
                <label for="data">News Title (5-10 words):</label>
                <textarea id="data" name="data" class = "select-op" required class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="link">Link:</label>
                <input type="text" id="link" name="link" class="form-control select-op">
            </div>

            <div class="form-group">
                <label for="end_date">End Date:&nbsp;</label>
                <input type="date" id="end_date" name="end_date" class="form-control select-op">
            </div>

            <div class="soption">
                <input type="submit" value="Add News">
                <a href="admin_panel.php" class="back">Return to Admin</a>
            </div>
        </form>
    </div>
    </div>
    <?php include "footer.php"; ?>
</body><script src="burger.js"></script>
</html>
