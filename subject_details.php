<?php
session_start();

// Redirect to index.php if not logged in or if user_type is neither 'admin' nor 'user'
if (!isset($_SESSION['user_email']) || !in_array($_SESSION["user_type"], ["admin", "user"])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Details</title>
    <link rel="stylesheet" href="subject_details.css">
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
        <a class="home" href="index1.php">HOME</a>
        <div class="nav-links" id="nav-links">
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
        <?php

        include 'connection.php';

        // Display subject details
        $subject = $_GET['subject'];
        $sem = (int)$_GET['sem'];
        $semTable = "sem" . $sem;

        $sql = "SELECT image, subject, description FROM cards WHERE sem = $sem AND subject = '$subject'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo '<h1 class="head">' . $row["subject"] . '</h1>';
            echo '<div class="container">';
            echo '<p>' . $row["description"] . '</p>';
            echo '<img src="' . $row["image"] . '" alt="Subject Image" class="image">';
        } else {
            echo '<p>No data available for this subject.</p>';
        }
        echo '</div>';

        // Display subject links by type
        echo '<div class="flex">';
        $types = ['college', 'pyq', 'youtube', 'other', 'books'];

        foreach ($types as $type) {
            echo '<h3>' . ucfirst($type) . ' Resources</h3>';
            echo '<table class="subject-links">';
            echo '<tr><th>Description</th><th>Link</th></tr>';

            $link_sql = "SELECT description, link FROM $semTable WHERE subject = '$subject' AND type='$type'";
            $link_result = $conn->query($link_sql);

            if ($link_result->num_rows > 0) {
                while ($link_row = $link_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $link_row["description"] . '</td>';
                    echo '<td><a href="' . $link_row["link"] . '">Download Link</a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="2">No links available for this subject.</td></tr>';
            }
            echo '</table>';
        }
        echo '</div>';

        $conn->close();
        ?>
    </div>
    <?php include "footer.php"; ?>
    <script>
        // Select the nav and header elements
        const nav = document.querySelector('nav');
        const header = document.querySelector('header');

        // Get the header height
        const headerHeight = header.offsetHeight;

        // Add a scroll event listener
        window.addEventListener('scroll', () => {
            if (window.scrollY > headerHeight) {
                nav.classList.add('sticky'); // Add sticky class when past the header
            } else {
                nav.classList.remove('sticky'); // Remove sticky class otherwise
            }
        });
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