<?php
session_start(); // This must be the very first thing in your script
// Check if the user is logged in by validating the session variable
if (!isset($_SESSION['user_email']) || empty($_SESSION['user_email'])) {
    // Redirect to login page if session is not valid
    header("Location: index.php");
    exit();
}

// OPTIONAL: Implement a session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // If last activity was more than 30 minutes ago
    session_unset();     // Unset session variables
    session_destroy();   // Destroy the session
    header("Location: index.php?timeout=true"); // Redirect to login with timeout message
    exit();
}
$_SESSION['last_activity'] = time(); // Update last activity timestamp

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connection.php';
// Handle logout
if (isset($_GET['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: index.php"); // Redirect to the homepage after logout
    exit();
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
        <a class="home" href="#">HOME</a>
        <div class="nav-links" id="nav-links">
            <select name="semester" id="semester-menu" onchange="navigateToSemester()" class="styled-select">
                <option value="" disabled selected hidden>Select Semester</option>
                <option value="top">Top of the Page</option>
                <option value="sem1">I</option>
                <option value="sem2">II</option>
                <option value="sem3">III</option>
                <option value="sem4">IV</option>
                <option value="sem5">V</option>
                <option value="sem6">VI</option>
                <option value="sem7">VII</option>
                <option value="sem8">VIII</option>
            </select>

            <?php if (isset($_SESSION['user_email'])): ?>
                <?php
                // Split the email and get the part before the "@"
                $user_name = explode('@', $_SESSION['user_email'])[0];
                ?>
                <span class="user-email">
                    <?php echo $user_name; ?>
                </span>
                <span class="logout">
                    <a href="index.php?logout=true">Logout</a>
                </span>
            <?php endif; ?>
        </div>
        <?php if (isset($_SESSION['user_email']) && $_SESSION['user_type'] === 'admin'): ?>
            <a href="admin_panel.php" class="admin">Admin panel</a>
        <?php endif; ?>
    </nav>
    <div class="main-content">

        <?php include 'show_an.php'; ?>
        <?php

        include 'connection.php';

        $sem_sql = "SELECT DISTINCT sem FROM cards ORDER BY sem ASC";
        $sem_result = $conn->query($sem_sql);

        if ($sem_result->num_rows > 0) {
            while ($sem_row = $sem_result->fetch_assoc()) {
                $sem = $sem_row['sem'];

                echo '<div class="sems" id="sem' . $sem . '">';
                echo '<p class="SEMTEXT">SEM-' . $sem . '</p>';
                echo '</div>';

                echo '<div class="card-container">'; // Open card-container

                $sql = "SELECT image, subject, description FROM cards WHERE sem = $sem";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Explode the description into an array
                        $description_parts = explode(' ', $row["description"]);

                        // Select the first two or three parts
                        $short_description_parts = array_slice($description_parts, 0, 3); // Adjust the number as needed

                        // Implode the selected parts back into a string
                        $short_description = implode(' ', $short_description_parts);

                        echo '<div class="card">';
                        echo '<img src="' . $row["image"] . '" alt="Image">';
                        echo '<h3><a href="subject_details.php?sem=' . $sem . '&subject=' . urlencode($row["subject"]) . '" target="_blank">' . $row["subject"] . '</a></h3>';
                        echo '<div class="desc"><p><a href="subject_details.php?sem=' . $sem . '&subject=' . urlencode($row["subject"]) . '" target="_blank">' . $short_description . '...</a></p></div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No cards available for SEM-' . $sem . '</p>';
                }
                echo '</div>'; // Close card-container
            }
        } else {
            echo '<p>No SEM data available</p>';
        }

        $conn->close();
        ?>
    </div>
    </div>
    <?php include 'helpbox.php';
    include 'footer.php'; ?>
    <script>
        const nav = document.querySelector('nav');
const header = document.querySelector('header');
const headerHeight = header.offsetHeight;

let lastScrollY = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.scrollY;

    if (currentScroll > headerHeight) {
        if (!nav.classList.contains('sticky')) {
            nav.classList.add('sticky'); // Add sticky class
        }
    } else {
        if (nav.classList.contains('sticky')) {
            nav.classList.remove('sticky'); // Remove sticky class
        }
    }

    lastScrollY = currentScroll;
});


        function navigateToSemester() {
            const semesterMenu = document.getElementById("semester-menu");
            const selectedValue = semesterMenu.value;

            if (selectedValue === "top") {
                // Scroll to the top of the page
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else if (selectedValue) {
                // Navigate to the selected semester section with smooth scrolling
                const semesterSection = document.getElementById(selectedValue);
                if (semesterSection) {
                    semesterSection.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
            semesterMenu.value = selectedValue;
        }

        window.onclick = function(event) {
            const emailElement = document.querySelector('.user-email');
            const burgerMenu = document.getElementById('burger-menu');
            const navLinks = document.getElementById('nav-links');

            // Close nav-links if clicking outside of burger icon and nav-links
            if (navLinks.classList.contains('show') && !burgerMenu.contains(event.target) && !navLinks.contains(event.target)) {
                navLinks.classList.remove('show');
            }
        };

        document.getElementById('burger-menu').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('show');
        });

        document.getElementById('burger-menu').addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent click from propagating
            document.getElementById('nav-links').classList.toggle('show');
        });


        document.addEventListener("DOMContentLoaded", function() {
            const burger = document.querySelector('.burger');
            const navLinks = document.querySelector('.nav-links');

            // Toggle nav-links visibility when burger is clicked
            burger.addEventListener('click', () => {
                navLinks.classList.toggle('show');
            });

            // Adjust nav-links visibility based on window width
            function handleResize() {
                if (window.innerWidth > 800) {
                    navLinks.classList.remove('show'); // Ensure nav-links are always visible for wider screens
                }
            }

            // Initial check on page load
            handleResize();

            // Add resize event listener
            window.addEventListener('resize', handleResize);
        });

        // Close the menu when clicking any link inside the nav-links
        document.querySelectorAll('#nav-links a').forEach(function(link) {
            link.addEventListener('click', function() {
                document.getElementById('nav-links').classList.remove('show');
            });
        });
    </script>
</body>

</html>