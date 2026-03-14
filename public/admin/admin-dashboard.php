<?php
// ====================
// public/admin/admin-dashboard.php
// ====================

// 1️⃣ (session is started by includes/security.php)

/* =============================================================
   BOOTSTRAP INCLUDE — one line, everything else is handled
   ============================================================= */
require_once __DIR__ . '/../bootstrap.php';

// 2️⃣ Protect page: only admins allowed
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . BASE_URL . "get-started.php");
    exit();
}

// 3️⃣ Handle AJAX requests before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $user_id = intval($_POST['user_id'] ?? 0);
    $requested_role = $_POST['requested_role'] ?? '';

    if (!$user_id || !in_array($requested_role, ['teacher', 'admin'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit();
    }

    if ($_POST['action'] === 'accept_request') {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $requested_role, $user_id);
        $success = $stmt->execute();
        $stmt->close();

        echo json_encode([
            'success' => $success,
            'message' => $success ? "User role updated to $requested_role" : "Database error"
        ]);
        exit();
    }

    if ($_POST['action'] === 'reject_request') {
        echo json_encode([
            'success' => true,
            'message' => "Request rejected. User remains student."
        ]);
        exit();
    }
}

// 4️⃣ Fetch pending student requests
$pending_requests = [];
$result = $conn->query("
    SELECT id, first_name, last_name, email, id_number, department, year_level, section, role, created_at
    FROM users
    WHERE role = 'student'
    ORDER BY created_at DESC
    LIMIT 100
");
while ($row = $result?->fetch_assoc()) {
    $pending_requests[] = $row;
}

// 5️⃣ Fetch approved teachers/admins
$approved_requests = [];
$result = $conn->query("
    SELECT id, first_name, last_name, email, id_number, department, year_level, section, role, created_at
    FROM users
    WHERE role IN ('teacher', 'admin')
    ORDER BY created_at DESC
    LIMIT 100
");
while ($row = $result?->fetch_assoc()) {
    $approved_requests[] = $row;
}

// 6️⃣ Statistics
$pending_count  = count($pending_requests);
$approved_count = count($approved_requests);

// 7️⃣ Admin info (from session)
$admin_name  = $_SESSION['user_name'] ?? '';
$admin_email = $_SESSION['user_email'] ?? '';


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Attendifyv1/public/assets/css/admin-dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="#" class="logo">
                    <i class="fas fa-shield-alt"></i>
                    <span>Attendify.</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <li class="nav-item">
                    <button class="nav-link active" onclick="switchSection('dashboard', event)">
                        <i class="fas fa-home"></i>
                        <span>Admin Dashboard</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('access-requests', event)">
                        <i class="fas fa-user-check"></i>
                        <span>Access Requests</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('manage-students', event)">
                        <i class="fas fa-users"></i>
                        <span>Manage Students</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('manage-teachers', event)">
                        <i class="fas fa-chalkboard-user"></i>
                        <span>Manage Teachers</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('classes', event)">
                        <i class="fas fa-book"></i>
                        <span>Classes / Subjects</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('attendance-records', event)">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Attendance Records</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('appeals', event)">
                        <i class="fas fa-flag"></i>
                        <span>Attendance Appeals</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('announcements', event)">
                        <i class="fas fa-bell"></i>
                        <span>Announcements</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('reports', event)">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports & Analytics</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('settings', event)">
                        <i class="fas fa-cog"></i>
                        <span>System Settings</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('profile', event)">
                        <i class="fas fa-user-circle"></i>
                        <span>Admin Profile</span>
                    </button>
                </li>
            </nav>

            <div class="logout-section">
                <form action="logout.php" method="POST">
                    <button class="nav-link" onclick="handleLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- TOP NAVIGATION -->
            <nav class="top-nav">
                <div class="top-nav-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title" id="page-title">Dashboard</h1>
                </div>

                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search students, teachers, classes...">
                </div>

                <div class="top-nav-right">
                    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>

                    <button class="notification-bell" onclick="toggleNotifications()" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge"><?php echo $pending_count; ?></span>
                    </button>

                    <div class="admin-profile" onclick="toggleUserMenu()" title="User Profile">
                        <div class="admin-avatar">AD</div>
                        <div class="admin-info">
                            <span class="admin-name">Admin</span>
                            <span class="admin-role">Administrator</span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- CONTENT AREA -->
            <div class="content">

                <?php include __DIR__ . '/sections/dashboard.php'; ?>

                <?php include __DIR__ . '/sections/access_requests.php'; ?>

                <?php include __DIR__ . '/sections/manage_students.php'; ?>

                <?php include __DIR__ . '/sections/manage_teachers.php'; ?>

                <?php include __DIR__ . '/sections/classes.php'; ?>

                <?php include __DIR__ . '/sections/attendance_records.php'; ?>

                <?php include __DIR__ . '/sections/appeals.php'; ?>

                <?php include __DIR__ . '/sections/announcements.php'; ?>

                <?php include __DIR__ . '/sections/reports.php'; ?>

                <?php include __DIR__ . '/sections/settings.php'; ?>

                <?php include __DIR__ . '/sections/profile.php'; ?>

            </div>
        </div>
    </div>

    <!-- ROLE SELECTION MODAL -->
    <div id="roleModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 600;">Select Role for <span id="modalUserName"></span></h3>
            <p style="margin-bottom: 20px; color: #666; font-size: 14px;">Choose which role to grant access to:</p>
            
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                <button class="btn btn-primary" onclick="acceptWithRole('teacher')" style="width: 100%; padding: 12px;">
                    <i class="fas fa-chalkboard-user"></i> Grant Teacher Access
                </button>
                <button class="btn btn-primary" onclick="acceptWithRole('admin')" style="width: 100%; padding: 12px; background-color: #8b5cf6;">
                    <i class="fas fa-shield-alt"></i> Grant Admin Access
                </button>
            </div>

            <button class="btn btn-secondary" onclick="closeRoleModal()" style="width: 100%; padding: 12px;">Cancel</button>
        </div>
    </div>

    <script>
        let currentRequestId = null;

        // Initialize theme from localStorage
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-mode');
                updateThemeIcon();
            }
        }

        // Initialize sidebar state from localStorage
        function initializeSidebar() {
            const savedSidebarState = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (savedSidebarState && window.innerWidth > 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
        }

        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('active');
            } else {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
        }

        // Toggle theme
        function toggleTheme() {
            const isDarkMode = document.documentElement.classList.toggle('dark-mode');
            const theme = isDarkMode ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
            updateThemeIcon();
        }

        // Update theme icon
        function updateThemeIcon() {
            const themeIcon = document.getElementById('theme-icon');
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            if (isDarkMode) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }

        // Set current date
        function updateDate() {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const today = new Date();
            document.getElementById('current-date').textContent = today.toLocaleDateString('en-US', options);
        }

        // Switch sections
        function switchSection(sectionId, event) {
            if (event) {
                event.preventDefault();
            }

            // Hide all sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.classList.add('active');
            }

            // Update active nav link
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            if (event && event.target) {
                event.target.closest('.nav-link').classList.add('active');
            }

            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'access-requests': 'Access Requests',
                'manage-students': 'Manage Students',
                'manage-teachers': 'Manage Teachers',
                'classes': 'Classes / Subjects',
                'attendance-records': 'Attendance Records',
                'appeals': 'Attendance Appeals',
                'announcements': 'Announcements',
                'reports': 'Reports & Analytics',
                'settings': 'System Settings',
                'profile': 'Admin Profile'
            };
            document.getElementById('page-title').textContent = titles[sectionId];

            // Close sidebar on mobile
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        }

        // ==================== ACCESS REQUESTS FUNCTIONS ====================

        /**
         * Show role selection modal
         */
        function showRoleModal(requestId, userName) {
            currentRequestId = requestId;
            document.getElementById('modalUserName').textContent = userName;
            document.getElementById('roleModal').style.display = 'flex';
        }

        /**
         * Close role selection modal
         */
        function closeRoleModal() {
            document.getElementById('roleModal').style.display = 'none';
            currentRequestId = null;
        }

        /**
         * Accept request with selected role
         */
        function acceptWithRole(role) {
            if (!currentRequestId) return;

            const formData = new FormData();
            formData.append('action', 'accept_request');
            formData.append('user_id', currentRequestId);
            formData.append('requested_role', role);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`Access request accepted! User is now a ${role}.`, 'success');
                    
                    // Remove row from pending table
                    const row = document.querySelector(`tr[data-request-id="${currentRequestId}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Update pending count
                    updatePendingCount();
                    
                    // Close modal
                    closeRoleModal();
                    
                    // Reload page after 2 seconds to refresh data
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification(data.message || 'Error accepting request', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while processing the request', 'error');
            });
        }

        /**
         * Reject an access request
         */
        function rejectRequest(requestId) {
            if (confirm('Are you sure you want to reject this access request?')) {
                const formData = new FormData();
                formData.append('action', 'reject_request');
                formData.append('user_id', requestId);
                formData.append('requested_role', 'student');

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Access request rejected.', 'error');
                        
                        // Remove row from pending table
                        const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
                        if (row) {
                            row.remove();
                        }
                        
                        // Update pending count
                        updatePendingCount();
                    } else {
                        showNotification(data.message || 'Error rejecting request', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while processing the request', 'error');
                });
            }
        }

        /**
         * Filter requests by role and department
         */
        function filterRequests() {
            const roleFilter = document.getElementById('roleFilter').value;
            const departmentFilter = document.getElementById('departmentFilter').value;

            // Filter pending requests
            const pendingRows = document.querySelectorAll('#pendingRequestsTable tbody tr');
            pendingRows.forEach(row => {
                let show = true;
                if (roleFilter && row.dataset.role !== roleFilter) show = false;
                if (departmentFilter) {
                    const deptCell = row.cells[3]?.textContent || '';
                    if (!deptCell.includes(departmentFilter)) show = false;
                }
                row.style.display = show ? '' : 'none';
            });

            // Filter approved requests
            const approvedRows = document.querySelectorAll('#approvedRequestsTable tbody tr');
            approvedRows.forEach(row => {
                let show = true;
                if (roleFilter && row.dataset.role !== roleFilter) show = false;
                if (departmentFilter) {
                    const deptCell = row.cells[4]?.textContent || '';
                    if (!deptCell.includes(departmentFilter)) show = false;
                }
                row.style.display = show ? '' : 'none';
            });
        }

        /**
         * Reset all filters
         */
        function resetFilters() {
            document.getElementById('roleFilter').value = '';
            document.getElementById('departmentFilter').value = '';
            filterRequests();
        }

        /**
         * Update the pending requests count
         */
        function updatePendingCount() {
            const pendingRows = document.querySelectorAll('#pendingRequestsTable tbody tr:not([style*="display: none"])');
            document.getElementById('pendingCount').textContent = pendingRows.length;
        }

        /**
         * Show notification message
         */
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 24px;
                border-radius: 8px;
                font-weight: 500;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
                color: white;
            `;

            if (type === 'success') {
                notification.style.backgroundColor = '#10b981';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#ef4444';
            } else {
                notification.style.backgroundColor = '#3b82f6';
            }

            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Notification bell toggle
        function toggleNotifications() {
            alert('You have pending access requests!');
        }

        // User menu toggle
        function toggleUserMenu() {
            alert('User profile menu would open here');
        }

        // Logout handler
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        // Close sidebar when clicking outside on mobile
        function closeSidebarOnClickOutside(e) {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            initializeSidebar();
            updateDate();
            updatePendingCount();

            // Add click outside handler for mobile sidebar
            document.addEventListener('click', closeSidebarOnClickOutside);
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }

            .btn-success {
                background-color: #10b981;
                color: white;
                border: none;
                cursor: pointer;
                border-radius: 6px;
                transition: background-color 0.3s ease;
            }

            .btn-success:hover {
                background-color: #059669;
            }

            .btn-danger {
                background-color: #ef4444;
                color: white;
                border: none;
                cursor: pointer;
                border-radius: 6px;
                transition: background-color 0.3s ease;
            }

            .btn-danger:hover {
                background-color: #dc2626;
            }

            #roleModal {
                background-color: rgba(0, 0, 0, 0.5);
            }

            #roleModal > div {
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
