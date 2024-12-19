<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

include 'connection.php';
include 'check_admin.php';

// Fetch all distinct semesters for the dropdown
$semesters = [];
$semStmt = $conn->prepare("SELECT DISTINCT sem FROM cards ORDER BY SEM ASC");
$semStmt->execute();
$semStmt->bind_result($semData);
while ($semStmt->fetch()) {
    $semesters[] = $semData;
}
$semStmt->close();

$subjects = [];
$subStmt = $conn->prepare("SELECT DISTINCT subject FROM cards WHERE sem = ? and added_by=?");
foreach ($semesters as $sem) {
    $subStmt->bind_param("is", $sem, $email);
    $subStmt->execute();
    $subStmt->bind_result($subject);
    while ($subStmt->fetch()) {
        $subjects[$sem][] = $subject;
    }
}
$subStmt->close();

// Handle AJAX request to fetch data based on selections
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fetch_data'])) {
    $sem = $_POST['sem'];
    $subject = $_POST['subject'];
    $type = $_POST['type'];

    $table = "sem" . intval($sem);

    // Prepare the SQL query to fetch the records
    $stmt = $conn->prepare("SELECT description, link, type FROM $table WHERE subject = ? AND type = ? AND added_by = ?");
    $stmt->bind_param("sss", $subject, $type, $email);
    $stmt->execute();
    $stmt->bind_result($description, $link, $type);

    // Initialize the data array
    $dataItems = [];

    // Fetch all records and store them in the array
    while ($stmt->fetch()) {
        $dataItems[] = ['description' => $description, 'link' => $link, 'type' => $type];
    }

    // Send the data back as JSON response
    echo json_encode($dataItems);

    $stmt->close();
    exit();
}

// Handle the deletion process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Delete_item']) && isset($_POST['description'])) {
    $description = $_POST['description'];
    $sem = $_POST['sem'];
    $subject = $_POST['subject'];
    $type = $_POST['type'];

    $table = "sem" . intval($sem);

    // Prepare the delete query
    $stmt = $conn->prepare("DELETE FROM $table WHERE description = ? AND subject = ? AND type = ? AND added_by = ?");
    $stmt->bind_param("ssss", $description, $subject, $type, $email);

    if ($stmt->execute()) {
        echo "<script>alert('Data deleted successfully.'); window.location.href='admin_panel.php';</script>";
    } else {
        echo "<script>alert('Error deleting data. Please try again.');</script>";
    }

    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Data</title>
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
        <h1>Delete Data</h1>

        <form method="POST" id="DeleteForm" class="form" onsubmit="return confirm('Are you sure you want to delete this data?');">
            <div class="form-group">
                <label for="sem">Select Semester:</label>
                <select name="sem" class="select-op" id="sem" required>
                    <option class="select-op" value="">Select a semester</option>
                    <?php foreach ($semesters as $sem): ?>
                        <option class="select-op" value="<?php echo $sem; ?>"><?php echo "Semester $sem"; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Select Subject:</label>
                <select name="subject" class="select-op" id="subject" required>
                    <option class="select-op" value="">Select a subject</option>
                </select>
            </div>
            <div class="form-group">
                <label for="type">Select Type:</label>
                <select name="type" class="select-op" id="type" required>
                    <option class="select-op" value="">Select a type</option>
                    <option class="select-op" value="college">College Resources</option>
                    <option value="pyq" class = "select-op">Pyq Resources</option>
                    <option class="select-op" value="youtube">YouTube Resources</option>
                    <option class="select-op" value="other">Other Resources</option>
                    <option class="select-op" value="book">Book Resources</option>
                </select>
            </div>

            <div id="dataList" class="display" style="display:none;">
                <label for="description">Select Data to Delete:</label>
                <select class="select-op" name="description" id="description" required>
                    <option class="select-op" value="">Select an item</option>
                </select>
            </div>

            <div id="dataDetails" class="data-details" style="display:none;">
                <h3>Details</h3>
                <p><strong>Description:</strong> <span id="itemDescription"></span></p>
                <p><strong>Link:</strong> <span id="itemLink"></span></p>
                <p><strong>Type:</strong> <span id="itemType"></span></p>
            </div>

            <div class="soption">
                <input type="submit" name="Delete_item" value="Delete Data">
                <a href="admin_panel.php" class="back">Return to Admin</a>
            </div>
        </form>
    </div>
</div>

    <script>
        $(document).ready(function() {
            var dataItems = [];

            $('#sem').change(function() {
                const sem = $(this).val();
                $('#subject').prop('disabled', !sem).html('<option value="">Select a subject</option>');
                if (sem) {
                    $.each(<?php echo json_encode($subjects); ?>[sem], function(index, subject) {
                        $('#subject').append(new Option(subject, subject));
                    });
                }
                $('#dataList').hide();
                $('#dataDetails').hide();
            });

            $('#subject, #type').change(function() {
                const sem = $('#sem').val();
                const subject = $('#subject').val();
                const type = $('#type').val();
                if (sem && subject && type) {
                    $.post('', { fetch_data: true, sem, subject, type }, function(response) {
                        try {
                            dataItems = JSON.parse(response);
                            console.log(dataItems);
                            $('#description').html('<option value="">Select an item</option>');

                            if (dataItems.length > 0) {
                                $.each(dataItems, function(index, item) {
                                    $('#description').append(new Option(item.description, item.description));
                                });
                                $('#dataList').show();
                            } else {
                                $('#dataList').hide();
                            }

                            $('#dataDetails').hide();
                        } catch (e) {
                            console.error("Failed to parse response:", response);
                        }
                    });
                }
            });

            $('#description').change(function() {
                const description = $(this).val();
                if (description) {
                    const selectedItem = dataItems.find(item => item.description === description);
                    if (selectedItem) {
                        $('#dataDetails').html(`
                            <h3>Details</h3>
                            <p><strong>Description:</strong> ${selectedItem.description}</p>
                            <p><strong>Link:</strong> <a href="${selectedItem.link}" target="_blank">${selectedItem.link}</a></p>
                            <p><strong>Type:</strong> ${selectedItem.type}</p>
                        `).show();
                    }
                }
            });
        });
    </script>

    <?php include "footer.php"; ?>
</body><script src="burger.js"></script>
</html>
