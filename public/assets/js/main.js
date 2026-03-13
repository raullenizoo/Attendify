const textElement = document.getElementById('typing-text');
    const phrases = ["Smart", "Efficient", "Real-time"];
    let phraseIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typeSpeed = 100;

    function type() {
        const currentPhrase = phrases[phraseIndex];
        
        if (isDeleting) {
            textElement.textContent = currentPhrase.substring(0, charIndex - 1);
            charIndex--;
            typeSpeed = 50;
        } else {
            textElement.textContent = currentPhrase.substring(0, charIndex + 1);
            charIndex++;
            typeSpeed = 150;
        }
        
        if (!isDeleting && charIndex === currentPhrase.length) {
            isDeleting = true;
            typeSpeed = 2000;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            phraseIndex = (phraseIndex + 1) % phrases.length;
            typeSpeed = 500;
        }
        
        setTimeout(type, typeSpeed);
    }

    document.addEventListener('DOMContentLoaded', type);

    const toggleBtn = document.querySelector(".theme-toggle");

    // Load saved theme when page loads
    window.addEventListener("DOMContentLoaded", () => {
        const savedTheme = localStorage.getItem("theme");
        
        if (savedTheme === "dark") {
            document.body.classList.add("dark-mode");
            toggleBtn.textContent = "☀️";
        }
    });

    // Toggle theme on click
    toggleBtn.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        
        const isDark = document.body.classList.contains("dark-mode");
        
        if (isDark) {
            toggleBtn.textContent = "☀️";
            localStorage.setItem("theme", "dark");
        } else {
            toggleBtn.textContent = "🌙";
            localStorage.setItem("theme", "light");
        }
    });

    // Mobile menu toggle
    const menuIcon = document.querySelector('.menu-icon');
    const navUl = document.querySelector('nav ul');

    menuIcon.addEventListener('click', () => {
        navUl.classList.toggle('active');
    });