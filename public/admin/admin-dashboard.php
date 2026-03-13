<?php
// ====================
// public/admin/admin-dashboard.php
// ====================

// 1️⃣ Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
                <button class="nav-link" onclick="handleLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
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

                <!-- ==================== DASHBOARD SECTION ==================== -->
                <div id="dashboard" class="section active">
                    <!-- WELCOME SECTION -->
                    <div class="welcome-section">
                        <h2 class="welcome-title">Welcome back, Administrator! 👋</h2>
                        <p class="welcome-subtitle">Today is <span id="current-date"></span> — Here is the overview of today's attendance activity.</p>
                    </div>

                    <!-- QUICK ACTIONS -->
                    <div class="quick-actions">
                        <button class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            Add Student
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            Add Teacher
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            Create Class
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-file-export"></i>
                            Export Report
                        </button>
                    </div>

                    <!-- SYSTEM OVERVIEW STATISTICS -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?php echo count($pending_requests); ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-chalkboard-user"></i>
                            </div>
                            <div class="stat-number"><?php echo count(array_filter($approved_requests, fn($r) => $r['role'] === 'teacher')); ?></div>
                            <div class="stat-label">Total Teachers</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-number">42</div>
                            <div class="stat-label">Total Classes</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="stat-number">92.5%</div>
                            <div class="stat-label">Today's Attendance Rate</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $pending_count; ?></div>
                            <div class="stat-label">Pending Requests</div>
                        </div>
                    </div>

                    <!-- RECENT ATTENDANCE ACTIVITY -->
                    <div class="section-title">
                        <i class="fas fa-history"></i>
                        Recent Attendance Activity
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aarav Patel</td>
                                    <td>Mathematics</td>
                                    <td>Mr. Robert</td>
                                    <td>March 12, 2026</td>
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Present</span></td>
                                </tr>
                                <tr>
                                    <td>Priya Sharma</td>
                                    <td>Physics</td>
                                    <td>Dr. Sarah Smith</td>
                                    <td>March 12, 2026</td>
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Present</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== ACCESS REQUESTS SECTION ==================== -->
                <div id="access-requests" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Access Requests</h2>
                        <p class="welcome-subtitle">Review and manage role access requests for Teachers and Administrators</p>
                    </div>

                    <!-- FILTER & STATS -->
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="section-title">
                            <i class="fas fa-filter"></i>
                            Filter Requests
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <select class="form-select" id="roleFilter" onchange="filterRequests()">
                                    <option value="">All Roles</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="departmentFilter" onchange="filterRequests()">
                                    <option value="">All Departments</option>
                                    <option value="Science">Science</option>
                                    <option value="English">English</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Chemistry">Chemistry</option>
                                </select>
                            </div>

                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button class="btn btn-secondary" onclick="resetFilters()" style="width: 100%;">Reset Filters</button>
                            </div>
                        </div>
                    </div>

                    <!-- REQUEST STATISTICS -->
                    <div class="stats-grid" style="margin-bottom: 24px;">
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stat-number" id="pendingCount"><?php echo $pending_count; ?></div>
                            <div class="stat-label">Pending Requests</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-number" id="approvedCount"><?php echo $approved_count; ?></div>
                            <div class="stat-label">Approved</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-number" id="rejectedCount"><?php echo $rejected_count; ?></div>
                            <div class="stat-label">Rejected</div>
                        </div>
                    </div>

                    <!-- PENDING REQUESTS TABLE -->
                    <div class="section-title">
                        <i class="fas fa-hourglass-half"></i>
                        Pending Access Requests (Students Requesting Teacher/Admin Access)
                    </div>

                    <div class="table-container">
                        <table id="pendingRequestsTable">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>ID Number</th>
                                    <th>Department</th>
                                    <th>Year Level</th>
                                    <th>Request Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($pending_requests) > 0): ?>
                                    <?php foreach ($pending_requests as $request): ?>
                                        <tr data-request-id="<?php echo $request['id']; ?>" data-role="student">
                                            <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                                            <td><?php echo htmlspecialchars($request['id_number']); ?></td>
                                            <td><?php echo htmlspecialchars($request['department']); ?></td>
                                            <td><?php echo htmlspecialchars($request['year_level']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                            <td>
                                                <div style="display: flex; gap: 8px;">
                                                    <button class="btn btn-primary" onclick="showRoleModal(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?>')" style="width: auto; padding: 6px 12px; font-size: 12px;">
                                                        <i class="fas fa-check"></i> Accept
                                                    </button>
                                                    <button class="btn btn-danger" onclick="rejectRequest(<?php echo $request['id']; ?>)" style="width: auto; padding: 6px 12px; font-size: 12px;">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-secondary);">
                                            No pending requests at this time.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- APPROVED REQUESTS TABLE -->
                    <div class="section-title" style="margin-top: 40px;">
                        <i class="fas fa-check-circle"></i>
                        Approved Users (Teachers & Administrators)
                    </div>

                    <div class="table-container">
                        <table id="approvedRequestsTable">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>ID Number</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Approved Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($approved_requests) > 0): ?>
                                    <?php foreach ($approved_requests as $approved): ?>
                                        <tr data-request-id="<?php echo $approved['id']; ?>" data-role="<?php echo $approved['role']; ?>">
                                            <td><?php echo htmlspecialchars($approved['first_name'] . ' ' . $approved['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($approved['email']); ?></td>
                                            <td><?php echo htmlspecialchars($approved['id_number']); ?></td>
                                            <td>
                                                <?php if ($approved['role'] === 'teacher'): ?>
                                                    <span class="badge" style="background-color: #3b82f6; color: white;">Teacher</span>
                                                <?php elseif ($approved['role'] === 'admin'): ?>
                                                    <span class="badge" style="background-color: #8b5cf6; color: white;">Administrator</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($approved['department']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($approved['created_at'])); ?></td>
                                            <td><span class="badge badge-approved"><i class="fas fa-check"></i> Approved</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-secondary);">
                                            No approved users yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== MANAGE STUDENTS SECTION ==================== -->
                <div id="manage-students" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Manage Students</h2>
                        <p class="welcome-subtitle">Add, edit, or remove student records</p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-primary" style="width: auto; padding: 10px 20px;">
                            <i class="fas fa-plus"></i> Add New Student
                        </button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Roll No.</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_requests as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['id_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['department']); ?></td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== MANAGE TEACHERS SECTION ==================== -->
                <div id="manage-teachers" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Manage Teachers</h2>
                        <p class="welcome-subtitle">Add, edit, or remove teacher records</p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-primary" style="width: auto; padding: 10px 20px;">
                            <i class="fas fa-plus"></i> Add New Teacher
                        </button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Teacher Name</th>
                                    <th>Employee ID</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_filter($approved_requests, fn($r) => $r['role'] === 'teacher') as $teacher): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['id_number']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== CLASSES SECTION ==================== -->
                <div id="classes" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Classes / Subjects</h2>
                        <p class="welcome-subtitle">Manage classes and subjects</p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-primary" style="width: auto; padding: 10px 20px;">
                            <i class="fas fa-plus"></i> Add New Class
                        </button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Class 10-A</td>
                                    <td>Mathematics</td>
                                    <td>Mr. Robert</td>
                                    <td>35</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== ATTENDANCE RECORDS SECTION ==================== -->
                <div id="attendance-records" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Attendance Records</h2>
                        <p class="welcome-subtitle">View and manage all attendance records</p>
                    </div>

                    <div class="card" style="margin-bottom: 24px;">
                        <div class="section-title">
                            <i class="fas fa-filter"></i>
                            Filter Records
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Class</label>
                                <select class="form-select">
                                    <option>All Classes</option>
                                    <option>Class 10-A</option>
                                    <option>Class 10-B</option>
                                    <option>Class 10-C</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-input">
                            </div>

                            <div class="form-group">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-input">
                            </div>

                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aarav Patel</td>
                                    <td>10-A</td>
                                    <td>Mathematics</td>
                                    <td>March 12, 2026</td>
                                    <td><span class="badge badge-success">Present</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== APPEALS SECTION ==================== -->
                <div id="appeals" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Attendance Appeals</h2>
                        <p class="welcome-subtitle">Review and manage student attendance appeals</p>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Arjun Desai</td>
                                    <td>Physics</td>
                                    <td>March 10, 2026</td>
                                    <td>Medical Emergency</td>
                                    <td><span class="badge badge-pending">Pending</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Review</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== ANNOUNCEMENTS SECTION ==================== -->
                <div id="announcements" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">System Announcements</h2>
                        <p class="welcome-subtitle">Create and manage system-wide announcements</p>
                    </div>

                    <div class="announcements-container">
                        <div>
                            <div class="section-title">
                                <i class="fas fa-list"></i>
                                All Announcements
                            </div>

                            <div class="announcements-list">
                                <div class="announcement-item">
                                    <div class="announcement-title">System Maintenance Scheduled</div>
                                    <div class="announcement-meta">
                                        <span>March 15, 2026</span>
                                        <span>Admin</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="section-title">
                                <i class="fas fa-pen-fancy"></i>
                                Post New Announcement
                            </div>

                            <div class="post-announcement">
                                <form class="post-form">
                                    <div class="form-group">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-input" placeholder="Enter announcement title">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-textarea" placeholder="Enter your announcement message"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Recipient</label>
                                        <select class="form-select">
                                            <option>All Users</option>
                                            <option>Teachers Only</option>
                                            <option>Students Only</option>
                                            <option>Specific Class</option>
                                        </select>
                                    </div>

                                    <div style="display: flex; gap: 12px;">
                                        <button type="submit" class="btn btn-primary">Post Announcement</button>
                                        <button type="reset" class="btn btn-secondary">Clear</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== REPORTS SECTION ==================== -->
                <div id="reports" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Reports & Analytics</h2>
                        <p class="welcome-subtitle">View comprehensive attendance reports and analytics</p>
                    </div>

                    <div class="card" style="margin-bottom: 24px;">
                        <div class="section-title">
                            <i class="fas fa-filter"></i>
                            Generate Report
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Report Type</label>
                                <select class="form-select">
                                    <option>Select Report Type</option>
                                    <option>Attendance Summary</option>
                                    <option>Student Attendance</option>
                                    <option>Teacher Performance</option>
                                    <option>Class Attendance</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-input">
                            </div>

                            <div class="form-group">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-input">
                            </div>

                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button class="btn btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-chart-line"></i>
                        Analytics Dashboard
                    </div>

                    <div class="chart-grid">
                        <div class="chart-container">
                            <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Weekly Attendance Trend</h3>
                            <div class="chart-placeholder">
                                <i class="fas fa-chart-line"></i> Chart Placeholder
                            </div>
                        </div>

                        <div class="chart-container">
                            <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Student Attendance Rate</h3>
                            <div class="chart-placeholder">
                                <i class="fas fa-pie-chart"></i> Chart Placeholder
                            </div>
                        </div>

                        <div class="chart-container">
                            <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Absence Statistics</h3>
                            <div class="chart-placeholder">
                                <i class="fas fa-bar-chart"></i> Chart Placeholder
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== SETTINGS SECTION ==================== -->
                <div id="settings" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">System Settings</h2>
                        <p class="welcome-subtitle">Configure system-wide settings</p>
                    </div>

                    <div class="card" style="max-width: 600px;">
                        <div class="section-title">
                            <i class="fas fa-cog"></i>
                            General Settings
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">School Name</label>
                                <input type="text" class="form-input" value="ABC School" placeholder="Enter school name">
                            </div>

                            <div class="form-group">
                                <label class="form-label">School Email</label>
                                <input type="email" class="form-input" value="admin@abcschool.edu" placeholder="Enter school email">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Attendance Threshold (%)</label>
                                <input type="number" class="form-input" value="75" placeholder="Enter attendance threshold">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Academic Year</label>
                                <input type="text" class="form-input" value="2025-2026" placeholder="Enter academic year">
                            </div>

                            <div style="display: flex; gap: 12px;">
                                <button class="btn btn-primary">Save Settings</button>
                                <button class="btn btn-secondary">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== PROFILE SECTION ==================== -->
                <div id="profile" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Admin Profile</h2>
                        <p class="welcome-subtitle">Manage your administrator profile</p>
                    </div>

                    <div class="card" style="max-width: 600px;">
                        <div style="text-align: center; margin-bottom: 24px;">
                            <div class="admin-avatar" style="width: 100px; height: 100px; font-size: 40px; margin: 0 auto 16px;">AD</div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Administrator</div>
                            <div style="font-size: 14px; color: var(--text-secondary);">System Administrator</div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-input" value="Admin" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-input" value="User" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-input" value="<?php echo htmlspecialchars($admin_email); ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-input" value="+1 (555) 123-4567" disabled>
                            </div>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 24px;">
                            <button class="btn btn-primary">Edit Profile</button>
                            <button class="btn btn-secondary">Change Password</button>
                        </div>
                    </div>
                </div>

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
