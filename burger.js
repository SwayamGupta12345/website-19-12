// Toggle the menu visibility on burger menu click
document.addEventListener("DOMContentLoaded", function() {
    const burger = document.getElementById('burger-menu');
    const navLinks = document.getElementById('nav-links');

    burger.addEventListener('click', function(event) {
        event.stopPropagation();
        navLinks.classList.toggle('show');
    });

    window.addEventListener('click', function(event) {
        if (!burger.contains(event.target) && !navLinks.contains(event.target)) {
            navLinks.classList.remove('show');
        }
    });
});