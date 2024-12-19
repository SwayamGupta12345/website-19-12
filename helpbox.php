<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>.help-icon {
  position: fixed;
  bottom: 20px;
  left: 20px;
  width: 50px;
  height: 50px;
  background-color: #007BFF;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 20px;
  z-index: 1000;
}

.help-box {
  position: fixed;
  bottom: 80px;
  left: 40px;
  max-width: 90%;
  width: 400px;
  background-color: #f9f9f9;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  border-radius: 20px;
  padding: 20px;
  display: none;
  z-index: 999;
}

.help-box form {
  display: flex;
  flex-direction: column;
  gap: 5px; /* Add spacing between elements */
  position: relative; /* Ensure positioning context for children */
}

.help-box textarea {
  width: 100%;
  height: 100px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  resize: none;
}

.help-box input[type="email"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.help-box button {
  background-color: #007BFF;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  align-self: flex-end; /* Align the button to the right */
}

.help-box button:hover {
  background-color: #0056b3;
}

  </style>
</head>
<body>
  <div class="help-icon" onclick="toggleHelpBox()">?</div>
  <div class="help-box" id="helpBox">
    <form method="POST">
      <input type="email" name="email" placeholder="Your Email" required>
      <textarea name="question" placeholder="Type your question here..." required></textarea>
      <button type="submit" name="helpbox">Submit</button>
    </form>
  </div>
  <script>
    const helpBox = document.getElementById('helpBox');
    const helpIcon = document.querySelector('.help-icon');

    // Toggle help box visibility
    function toggleHelpBox() {
      const isVisible = helpBox.style.display === 'block';
      helpBox.style.display = isVisible ? 'none' : 'block';
    }

    // Close help box when clicking outside
    document.addEventListener('click', (event) => {
      if (!helpBox.contains(event.target) && !helpIcon.contains(event.target)) {
        helpBox.style.display = 'none';
      }
    });
  </script>
</body>
</html>

<?php
include 'connection.php';
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['helpbox'])) {
  // Get data from POST request
  $email = $conn->real_escape_string($_POST['email']);
  $question = $conn->real_escape_string($_POST['question']);
  // Insert data into the helpbox table
  $sql = "INSERT INTO helpbox (email, problem_text, resolved) VALUES ('$email', '$question', FALSE)";
  if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Your problem will be looked upon and resolved in 2-3 days.')</script>";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}
?>
