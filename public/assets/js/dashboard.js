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
//     if (confirm("Are you sure you want to logout?")) {
//         document.querySelector('.logout-section form').submit();
//     }
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
// Load Class Students
// ===============================
let currentClassId = null;
let attendanceData = {};

function loadClassStudents(classId) {
    currentClassId = classId;
    
    fetch('/Attendify/public/teacher/handlers/load_class_students.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ class_id: classId })
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('attendance-tbody');
        const noClassMsg = document.getElementById('no-class-message');
        
        console.log('Response:', data);
        
        if (data.success && data.students && data.students.length > 0) {
            tbody.innerHTML = '';
            attendanceData = {};
            
            data.students.forEach(student => {
                const studentId = student.id;
                attendanceData[studentId] = {
                    name: student.first_name + ' ' + student.last_name,
                    status: 'present'
                };
                
                const row = `
                    <tr>
                        <td data-label="Student Name">${student.first_name} ${student.last_name}</td>
                        <td data-label="ID Number">${student.id_number}</td>
                        <td data-label="Status">
                            <select class="form-input" style="width: auto; padding: 6px;" onchange="changeAttendanceStatus(${studentId}, this.value)">
                                <option value="present" selected>Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                                <option value="excused">Excused</option>
                            </select>
                        </td>
                        <td data-label="Action">
                            <span class="badge badge-success" id="status-${studentId}">Present</span>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No students in this class</td></tr>';
            console.log('Error or no students:', data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        const tbody = document.getElementById('attendance-tbody');
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color: red;">Error loading students. Check browser console.</td></tr>';
    });
}

// ===============================
// Change Attendance Status
// ===============================
function changeAttendanceStatus(studentId, status) {
    if (attendanceData[studentId]) {
        attendanceData[studentId].status = status;
    }
    
    const badge = document.getElementById(`status-${studentId}`);
    if (badge) {
        badge.classList.remove('badge-success', 'badge-warning', 'badge-danger', 'badge-secondary');
        
        const statusClass = {
            'present': 'badge-success',
            'late': 'badge-warning',
            'absent': 'badge-danger',
            'excused': 'badge-secondary'
        };
        
        const statusText = {
            'present': 'Present',
            'late': 'Late',
            'absent': 'Absent',
            'excused': 'Excused'
        };
        
        badge.classList.add(statusClass[status]);
        badge.textContent = statusText[status];
    }
}

// ===============================
// Submit Attendance
// ===============================
function submitAttendance() {
    if (!currentClassId) {
        alert('Please select a class first');
        return;
    }
    
    if (Object.keys(attendanceData).length === 0) {
        alert('No students found');
        return;
    }
    
    const attendanceRecords = [];
    for (const [studentId, data] of Object.entries(attendanceData)) {
        attendanceRecords.push({
            student_id: studentId,
            status: data.status
        });
    }
    
    fetch('/Attendify/public/teacher/handlers/submit_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            class_id: currentClassId,
            records: attendanceRecords
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Attendance submitted successfully!');
            resetAttendanceForm();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit attendance'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting attendance');
    });
}

// ===============================
// Reset Attendance Form
// ===============================
function resetAttendanceForm() {
    currentClassId = null;
    attendanceData = {};
    document.getElementById('attendance-tbody').innerHTML = '<tr id="no-class-message"><td colspan="4" style="text-align:center;">Select a class to view students</td></tr>';
}

// ===============================
// Change Attendance Status
// ===============================
function changeAttendanceStatus(studentId, status) {
    if (attendanceData[studentId]) {
        attendanceData[studentId].status = status;
    }
    
    const badge = document.getElementById(`status-${studentId}`);
    if (badge) {
        badge.classList.remove('badge-success', 'badge-warning', 'badge-danger', 'badge-secondary');
        
        const statusClass = {
            'present': 'badge-success',
            'late': 'badge-warning',
            'absent': 'badge-danger',
            'excused': 'badge-secondary'
        };
        
        const statusText = {
            'present': 'Present',
            'late': 'Late',
            'absent': 'Absent',
            'excused': 'Excused'
        };
        
        badge.classList.add(statusClass[status]);
        badge.textContent = statusText[status];
    }
}

// ===============================
// Submit Attendance
// ===============================
function submitAttendance() {
    if (!currentClassId) {
        alert('Please select a class first');
        return;
    }
    
    if (Object.keys(attendanceData).length === 0) {
        alert('No students found');
        return;
    }
    
    const attendanceRecords = [];
    for (const [studentId, data] of Object.entries(attendanceData)) {
        attendanceRecords.push({
            student_id: studentId,
            status: data.status
        });
    }
    
    fetch('/Attendify/public/teacher/handlers/submit_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            class_id: currentClassId,
            records: attendanceRecords
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Attendance submitted successfully!');
            resetAttendanceForm();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit attendance'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting attendance');
    });
}

// ===============================
// Reset Attendance Form
// ===============================
function resetAttendanceForm() {
    currentClassId = null;
    attendanceData = {};
    document.getElementById('attendance-tbody').innerHTML = '<tr id="no-class-message"><td colspan="4" style="text-align:center;">Select a class to view students</td></tr>';
}

// ===============================
// Post Announcement
// ===============================
function postAnnouncement(event) {
    event.preventDefault();

    const title = document.getElementById('announcement-title')?.value.trim();
    const content = document.getElementById('announcement-content')?.value.trim();
    const classSectionId = document.getElementById('announcement-class')?.value;
    const priority = document.getElementById('announcement-priority')?.value;

    if (!title || !content) {
        alert('Please enter a title and message before posting.');
        return;
    }

    fetch('/Attendify/public/teacher/handlers/post_announcement.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            title,
            content,
            class_section_id: classSectionId,
            priority
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error posting announcement: ' + (data.message || 'Unknown error'));
            return;
        }

        prependAnnouncement(data.announcement);

        const form = document.getElementById('announcement-form');
        if (form) form.reset();

        alert('Announcement posted successfully.');
    })
    .catch(error => {
        console.error('Announcement Error:', error);
        alert('Error posting announcement. Check console for details.');
    });
}

function prependAnnouncement(announcement) {
    const list = document.querySelector('.announcements-list');
    if (!list) return;

    const item = document.createElement('div');
    item.className = 'announcement-item';

    const titleEl = document.createElement('div');
    titleEl.className = 'announcement-title';
    titleEl.textContent = announcement.title;

    const dateEl = document.createElement('div');
    dateEl.className = 'announcement-date';
    const createdAt = new Date(announcement.created_at);
    dateEl.textContent = createdAt.toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });

    item.appendChild(titleEl);
    item.appendChild(dateEl);

    list.prepend(item);
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