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
    </div><a class="home" href="index1.php">HOME</a>
    <div class="nav-links" id="nav-links">

        <span class="logout">
            <a href="admin_panel.php" class="admin">Admin Panel</a>
        </span>
        <?php
        // Split the email and get the part before the "@"
        $user_name = explode('@', $_SESSION['user_email'])[0]; ?>
        <span class="user-email">
            <?php echo $user_name; ?>
        </span>
        <span class="logout">
            <a href="index1.php">Main Page</a>
        </span>
        <span class="logout">
            <a href="index.php?logout=true">Logout</a>
        </span>
    </div>
</nav>
<script>
    // Select the nav and create a placeholder
const nav = document.querySelector('nav');
const placeholder = document.createElement('div');
placeholder.classList.add('nav-placeholder');
nav.parentNode.insertBefore(placeholder, nav);

// Get the height of the nav
const navHeight = nav.offsetHeight;

// Add a scroll event listener
window.addEventListener('scroll', () => {
    if (window.scrollY > nav.offsetTop) {
        nav.classList.add('sticky'); // Make nav sticky
        placeholder.style.height = `${navHeight}px`; // Set placeholder height
    } else {
        nav.classList.remove('sticky'); // Remove sticky behavior
        placeholder.style.height = '0'; // Reset placeholder height
    }
});

    // Reset the menu layout on window resize
    window.addEventListener('resize', () => {
        const navLinks = document.querySelector('.nav-links');
        if (window.innerWidth > 800) {
            navLinks.classList.remove('show');
        }
    });
</script>