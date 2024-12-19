<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

include 'connection.php';
include 'check_admin.php';

// Fetch all distinct semesters for the dropdown
$table="";
$semesters = [];
$semStmt = $conn->prepare("SELECT DISTINCT sem FROM cards ORDER BY sem ASC");
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
    // Get the data from the POST request
    $sem = $_POST['sem'];
    $subject = $_POST['subject'];
    $type = $_POST['type'];

    // Prepare the correct table based on semester
    $table = "sem" . intval($sem);
    $_SESSION['table'] = $table; // Store the value for future use

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

    // Close the statement
    $stmt->close();

    exit();
}
// Handle the edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Edit_item'])) {
    // Retrieve the $table value from session
    if (isset($_SESSION['table']) && isset($_SESSION['old_description'])) {
        $table = $_SESSION['table'];  // Use the session value
        $old_description = $_SESSION['old_description'];  // Correctly retrieve the old description
    } else {
        echo '<script>alert("Enter data properly");</script>';
        header("refresh:1; url=edit_link.php");
        exit();
    }

    $new_description = $_POST['description'];
    $link = $_POST['link'];
    $type = $_POST['type'];

    // Check if the link is a Google Drive link
    if (preg_match('/drive\.google\.com\/file\/d\/([^\/]+)\/view/', $link, $matches)) {
        $file_id = $matches[1];
        $link = "https://drive.google.com/uc?export=download&id=" . $file_id;
    }

    // Safely prepare the SQL query to update the selected record
    $query = "UPDATE `$table` SET description = ?, link = ?, type = ? WHERE description = ? AND added_by = ?";
    $stmt = $conn->prepare($query);

    // Bind parameters (5 parameters to bind)
    $stmt->bind_param("sssss", $new_description, $link, $type, $old_description, $email);
    
    // Execute the query
    $execute_result = $stmt->execute();

    // Check if rows were updated
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Update successful!')</script><br>";
        header("refresh:1; url=edit_link.php");
    } else {
        echo "<script>alert('Updata unsuccessful retry again.');</script>";
        header("refresh:1; url=edit_link.php");
    }

    $stmt->close();

    // Unset the session variable and redirect
    unset($_SESSION['table']);
    unset($_SESSION['old_description']);
    $_SESSION['old_description']="";
    exit();
}

// Optionally, you can unset $_SESSION['table'] after a page reload or any other action
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    unset($_SESSION['table']);  // Clear the session variable when page reloads
    unset($_SESSION['old_description']);
    $_SESSION['old_description']="";
}
// Handle the session update for description when an item is selected
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_description'])) {
    if (isset($_POST['description'])) {
        $_SESSION['old_description'] = $_POST['description'];
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data</title>
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
        <h1>Edit Data</h1>

        <form method="POST" id="EditForm" class="form select-op" onsubmit="return confirmEdit();">
            <div class="form-group">
                <label for="sem">Select Semester:</label>
                <select name="sem" id="sem" class = "select-op" required>
                    <option value="" class = "select-op">Select a semester</option>
                    <?php foreach ($semesters as $sem): ?>
                        <option class = "select-op" value="<?php echo $sem; ?>"><?php echo "Semester $sem"; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Select Subject:</label>
                <select name="subject" class = "select-op" id="subject" required>
                    <option value="" class = "select-op">Select a subject</option>
                </select>
            </div>
            <div class="form-group">
                <label for="type">Select Type:</label>
                <select name="type" class = "select-op" id="type" required>
                    <option value="" class = "select-op">Select a type</option>
                    <option value="college" class = "select-op">College Resources</option>
                    <option value="pyq" class = "select-op">PYQ Resources</option>
                    <option value="youtube" class = "select-op">YouTube Resources</option>
                    <option value="other" class = "select-op">Other Resources</option>
                    <option value="book" class = "select-op">Book Resources</option>
                </select>
            </div>

            <div id="dataList" class="display" style="display:none;">
                <label for="description">Select Data to Edit:</label>
                <select name="description" class = "select-op" id="description" required>
                    <option class = "select-op" value="">Select an item</option>
                </select>
            </div>

            <div id="dataDetails" class="data-details" style="display:none;">
                <h3>Details</h3>
                <p><strong>Description:</strong> <input type="text" class = "select-op" id="itemDescription" name="description" /></p>
                <p><strong>Link:</strong><input type="url" class = "select-op" id="itemLink" name="link" style="width:600px;"></p>
                <p><strong>Type:</strong> 
                    <select name="type" class = "select-op" id="itemType">
                        <option class = "select-op" value="college">College Resources</option>
                        <option value="pyq" class = "select-op">Pyq Resources</option>
                        <option class = "select-op" value="youtube">YouTube Resources</option>
                        <option class = "select-op" value="other">Other Resources</option>
                        <option class = "select-op" value="book">Book Resources</option>
                    </select>
                </p>
            </div>

            <div class="soption">
                <input type="submit" id="submit_button" name="Edit_item" value="Edit Data">
                <a href="admin_panel.php" class="back">Return to Admin</a>
            </div>
        </form>
    </div>
    </div>
    <script>
        function confirmEdit() {
            if (confirm('Are you sure you want to Edit this data?')) {
                // Disable the button to prevent multiple submissions
                document.getElementById('submitButton').disabled = true;
                return true; // Allow the form to be submitted
            }
            return false; // Cancel the submission
        }
        $(document).ready(function() {
            var dataItems = []; // To store fetched data items

            $('#sem').change(function() {
                const sem = $(this).val();
                $('#subject').prop('disabled', !sem).html('<option value="">Select a subject</option>');
                if (sem) {
                    $.each(<?php echo json_encode($subjects); ?>[sem], function(index, subject) {
                        $('#subject').append(new Option(subject, subject));
                    });
                }
                $('#dataList').hide();
                $('#dataDetails').hide(); // Hide data details section initially
            });

            $('#subject, #type').change(function() {
                const sem = $('#sem').val();
                const subject = $('#subject').val();
                const type = $('#type').val();
                if (sem && subject && type) {
                    $.post('', { fetch_data: true, sem, subject, type }, function(response) {
                        try {
                            dataItems = JSON.parse(response);
                            console.log(dataItems); // For debugging
                            $('#description').html('<option value="">Select an item</option>'); // Reset dropdown

                            if (dataItems.length > 0) {
                                $.each(dataItems, function(index, item) {
                                    $('#description').append(new Option(item.description, item.description));
                                });
                                $('#dataList').show();
                            } else {
                                $('#dataList').hide(); // No data, hide the dropdown
                            }

                            $('#dataDetails').hide(); // Hide data details section until an item is selected
                        } catch (e) {
                            console.error("Failed to parse response:", response);
                        }
                    });
                }
            });

            $('#description').change(function() {
                const description = $(this).val();
                if (description) {
                    // Find the selected item details
                    const selectedItem = dataItems.find(item => item.description === description);
                    if (selectedItem) {
                        // Populate the item details into input fields
                        $('#itemDescription').val(selectedItem.description);
                        $('#itemLink').val(selectedItem.link);
                        $('#itemType').val(selectedItem.type);
                        $('#dataDetails').show();
                         // Set the old description in the session through AJAX to keep it updated
            $.post('', { set_description: true, description: selectedItem.description });
                    }
                }
            });
        });
    </script>
    <?php include "footer.php"; ?>
</body><script src="burger.js"></script>
</html>
