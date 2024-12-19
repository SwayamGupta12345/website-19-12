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

// Fetch all announcements titles added by the current user
$announcementItems = [];
$annStmt = $conn->prepare("SELECT title FROM announcements WHERE added_by = ?");
$annStmt->bind_param("s", $email);
$annStmt->execute();
$annStmt->bind_result($announcementTitle);
while ($annStmt->fetch()) {
    $announcementItems[] = $announcementTitle;
}
$annStmt->close();

// Handle AJAX request to fetch announcement details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fetch_data'])) {
    $selectedTitle = $_POST['fetch_data'];
    $stmt = $conn->prepare("SELECT title, content, image, link, start_date, end_date FROM announcements WHERE title = ? AND added_by = ?");
    $stmt->bind_param("ss", $selectedTitle, $email);
    $stmt->execute();
    $stmt->bind_result($title, $content, $image, $link, $start_date, $end_date);
    $stmt->fetch();
    echo json_encode(['title' => $title, 'content' => $content, 'image' => $image, 'link' => $link, 'start_date' => $start_date, 'end_date' => $end_date]);
    $stmt->close();
    exit();
}

// Handle announcement deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title_item'])) {
    $titleToDelete = $_POST['title_item'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE title = ? AND added_by = ?");
    $stmt->bind_param("ss", $titleToDelete, $email);
    if ($stmt->execute()) {
        echo "<script>alert('Announcement deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting announcement: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    header("Location: delete_announcements.php");
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Announcement</title>
    <link rel="stylesheet" href="inde.css">
    <link rel="stylesheet" href="admin_panel.css">
    <link rel="stylesheet" href="add_subject.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include "admin_nav.php"; ?>
    <div class="main-content">
    <div class="ap_container">
        <h1>Delete Announcement</h1>

        <!-- Dropdown to select announcement -->
        <form method="POST" id="deleteForm" class="form" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
            <label for="title_item">Select Announcement to Delete:</label>
            <select name="title_item" class="sdata select-op" id="title_item" required>
                <option value="" class="sdata1 select-op">Select an announcement</option>
                <?php foreach ($announcementItems as $announcementTitle): ?>
                    <option class = "select-op" value="<?php echo htmlspecialchars($announcementTitle); ?>"><?php echo htmlspecialchars($announcementTitle); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Display details of the selected announcement -->
            <div id="announcementDetails" class = "display">
                <h3>Announcement Details</h3>
                <p><strong>Title:</strong> <span class = "select-op" id="annTitle"></span></p>
                <p><strong>Content:</strong> <span id="annContent"></span></p>
                <!-- <p><strong>Image URL:</strong> <span id="annImage"></span></p> -->
                <p><strong>Link URL:</strong> <span id="annLink"></span></p>
                <p><strong>Start Date:</strong> <span id="annStartDate"></span></p>
                <p><strong>End Date:</strong> <span id="annEndDate"></span></p>
            </div>

            <div class="soption">
                <input type="submit" value="Delete Announcement">
                <a href="admin_panel.php" class="back">Return to Admin</a>
            </div>
        </form>
    </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#title_item').change(function() {
                const selectedAnnouncement = $(this).val();
                if (selectedAnnouncement) {
                    $.ajax({
                        type: 'POST',
                        url: '',  // Current page URL
                        data: { fetch_data: selectedAnnouncement },
                        success: function(response) {
                            const announcementDetails = JSON.parse(response);
                            $('#annTitle').text(announcementDetails.title);
                            $('#annContent').text(announcementDetails.content);
                            $('#annImage').text(announcementDetails.image);
                            $('#annLink').text(announcementDetails.link);
                            $('#annStartDate').text(announcementDetails.start_date);
                            $('#annEndDate').text(announcementDetails.end_date);
                            $('#announcementDetails').show();
                        }
                    });
                } else {
                    $('#announcementDetails').hide();
                }
            });
        });
    </script>
    <?php include 'footer.php' ?>
</body><script src="burger.js"></script>
</html>