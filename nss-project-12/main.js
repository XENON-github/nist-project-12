document.addEventListener('DOMContentLoaded', () => {
    // Hamburger Menu Logic
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // Dark Mode Toggle Logic
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;

    // Function to apply the saved theme
    const applyTheme = () => {
        let darkMode = localStorage.getItem('darkMode');
        if (darkMode === 'enabled') {
            body.classList.add('dark-mode');
            if(darkModeToggle) darkModeToggle.checked = true;
        } else {
            body.classList.remove('dark-mode');
            if(darkModeToggle) darkModeToggle.checked = false;
        }
    };

    // Apply the theme on initial load
    applyTheme();

    // Add event listener for the toggle switch
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', () => {
            let darkMode = localStorage.getItem('darkMode');
            if (darkMode !== 'enabled') {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
            // Re-apply the theme to reflect the change
            applyTheme();
        });
    }
});