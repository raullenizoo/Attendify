// ===============================
// Initialize Theme
// ===============================
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';

    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode');
    }

    updateThemeIcon();
}

// ===============================
// Initialize Sidebar
// ===============================
function initializeSidebar() {
    const savedSidebarState = localStorage.getItem('sidebarCollapsed') === 'true';

    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (!sidebar || !mainContent) return;

    if (savedSidebarState && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }
}

// ===============================
// Toggle Sidebar
// ===============================
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (!sidebar || !mainContent) return;

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('active');
    } else {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
}

// ===============================
// Toggle Theme
// ===============================
function toggleTheme() {
    const isDarkMode = document.documentElement.classList.toggle('dark-mode');
    localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');

    updateThemeIcon();
}

// ===============================
// Update Theme Icon
// ===============================
function updateThemeIcon() {
    const themeIcon = document.getElementById('theme-icon');
    if (!themeIcon) return;

    const isDarkMode = document.documentElement.classList.contains('dark-mode');

    themeIcon.classList.toggle('fa-sun', isDarkMode);
    themeIcon.classList.toggle('fa-moon', !isDarkMode);
}

// ===============================
// Set Current Date
// ===============================
function updateDate() {
    const dateElement = document.getElementById('current-date');
    if (!dateElement) return;

    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const today = new Date();

    dateElement.textContent = today.toLocaleDateString('en-US', options);
}

// ===============================
// Switch Sections
// ===============================
function switchSection(sectionId, event) {

    if (event) event.preventDefault();

    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });

    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) selectedSection.classList.add('active');

    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });

    if (event) {
        const nav = event.target.closest('.nav-link');
        if (nav) nav.classList.add('active');
    }

    const titles = {
    dashboard: "Teacher Dashboard",
    "take-attendance": "Take Attendance",
    "class-attendance": "Class Attendance",
    students: "Students",
    appeals: "Attendance Appeals",
    announcements: "Announcements",
    reports: "Reports",
    profile: "Profile"
};

    const pageTitle = document.getElementById("page-title");
    if (pageTitle) pageTitle.textContent = titles[sectionId] || "Dashboard";

    if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) sidebar.classList.remove('active');
    }
}


// ===============================
// User Menu
// ===============================
function toggleUserMenu() {
    const menu = document.querySelector('.user-menu');
    if (menu) menu.classList.toggle('active');
}

// function handleLogout() {
//     if (!confirm("Are you sure you want to logout?")) return;

//     // Gamitin natin ang eksaktong folder name mo (Attendify)
//     // Siguraduhin na "Attendify" ang name ng folder mo sa htdocs
//     window.location.href = "/Attendify/student/public/logout.php";
// }

// ===============================
// Close Sidebar (Mobile)
// ===============================
function closeSidebarOnClickOutside(e) {

    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');

    if (!sidebar || !toggleBtn) return;

    if (window.innerWidth <= 768) {

        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }

    }
}

// ===============================
// Page Load
// ===============================
document.addEventListener("DOMContentLoaded", () => {

    initializeTheme();
    initializeSidebar();
    updateDate();

    document.addEventListener("click", closeSidebarOnClickOutside);

});

// ===============================
// Window Resize
// ===============================
window.addEventListener("resize", () => {

    const sidebar = document.querySelector(".sidebar");

    if (window.innerWidth > 768 && sidebar) {
        sidebar.classList.remove("active");
    }

});

// Smooth scrolling
document.documentElement.style.scrollBehavior = "smooth";