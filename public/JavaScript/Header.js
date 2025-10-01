document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.querySelector('.toggle-button');
    const navbarLinks = document.querySelector('.navbar-links');
    const navbarButtons = document.querySelector('.navbar-buttons');

    toggleButton.addEventListener('click', () => {
        navbarLinks.classList.toggle('active');
        navbarButtons.classList.toggle('active');
    });
});