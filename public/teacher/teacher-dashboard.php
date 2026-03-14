<?php
    require '../../config/db.php';
    require '../../includes/security.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: /Attendify/public/get-started.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // =========================
    // 1. USER PROFILE
    // =========================
    $stmt = $conn->prepare("SELECT id, first_name, last_name, id_number, email, department, year_level, section, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $full_name = $user['first_name'] . ' ' . $user['last_name'];
    $id_number = $user['id_number'];
    $email = $user['email'];
    $department = $user['department'];

    // =========================
    // TOTAL STUDENTS
    // =========================
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT ce.student_id) AS total_students FROM class_enrollments ce JOIN class_sections cs ON ce.class_section_id = cs.id JOIN users u ON ce.student_id = u.id WHERE cs.teacher_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_students = $stmt->get_result()->fetch_assoc()['total_students'];

    // =========================
    // CLASSES TODAY
    // =========================
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_classes FROM class_sections WHERE teacher_id = ? AND is_active = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $classes_today = $stmt->get_result()->fetch_assoc()['total_classes'];

    // =========================
    // PRESENT TODAY
    // =========================
    $stmt = $conn->prepare("SELECT COUNT(*) AS present_today FROM attendance_records ar JOIN class_sections cs ON ar.class_section_id = cs.id WHERE cs.teacher_id = ? AND ar.attendance_date = CURDATE() AND ar.status = 'present'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $present_today = $stmt->get_result()->fetch_assoc()['present_today'];

    // =========================
    // ABSENT TODAY
    // =========================
    $stmt = $conn->prepare("SELECT COUNT(*) AS absent_today FROM attendance_records ar JOIN class_sections cs ON ar.class_section_id = cs.id WHERE cs.teacher_id = ? AND ar.attendance_date = CURDATE() AND ar.status = 'absent'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $absent_today = $stmt->get_result()->fetch_assoc()['absent_today'];


    // Pending Appeals Count
    $stmt = $conn->prepare("SELECT COUNT(*) AS pending_count FROM attendance_appeals aa JOIN class_sections cs ON aa.class_section_id = cs.id WHERE cs.teacher_id = ? AND aa.status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pending_appeals = $stmt->get_result()->fetch_assoc()['pending_count'] ?? 0;

    // =========================
    // TODAY'S CLASSES
    // =========================
    $stmt = $conn->prepare("SELECT cs.id, s.subject_name, cs.section_name, cs.schedule, cs.room FROM class_sections cs JOIN subjects s ON cs.subject_id = s.id WHERE cs.teacher_id = ? AND cs.is_active = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $today_classes = $stmt->get_result();

    // =========================
    // ALL CLASSES FOR TEACHER (used in multiple sections)
    // =========================
    $stmt = $conn->prepare("SELECT cs.id, s.subject_name, cs.section_name FROM class_sections cs JOIN subjects s ON cs.subject_id = s.id WHERE cs.teacher_id = ? ORDER BY cs.id");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $all_classes_result = $stmt->get_result();
    $all_classes = [];
    while ($row = $all_classes_result->fetch_assoc()) {
        $all_classes[] = $row;
    }

    // =========================
    // RECENT ATTENDANCE ACTIVITY
    // =========================
    $stmt = $conn->prepare("SELECT u.first_name, u.last_name, s.subject_name, ar.attendance_date, ar.status FROM attendance_records ar JOIN users u ON ar.student_id = u.id JOIN class_sections cs ON ar.class_section_id = cs.id JOIN subjects s ON cs.subject_id = s.id WHERE cs.teacher_id = ? ORDER BY ar.attendance_date DESC LIMIT 10");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recent_attendance = $stmt->get_result();

    // =========================
    // CLASS ATTENDANCE STATISTICS
    // =========================
    $stmt = $conn->prepare("SELECT cs.id, s.subject_name, cs.section_name, COUNT(DISTINCT ce.student_id) AS total_students, SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN ar.status='absent' THEN 1 ELSE 0 END) AS absent, SUM(CASE WHEN ar.status='late' THEN 1 ELSE 0 END) AS late FROM class_sections cs JOIN subjects s ON cs.subject_id = s.id JOIN class_enrollments ce ON cs.id = ce.class_section_id LEFT JOIN attendance_records ar ON ce.student_id = ar.student_id AND ar.class_section_id = cs.id WHERE cs.teacher_id = ? GROUP BY cs.id, s.subject_name, cs.section_name");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $class_attendance = $stmt->get_result();



    // =========================
    // PENDING APPEALS
    // =========================
    $stmt = $conn->prepare("SELECT u.first_name, u.last_name, s.subject_name, aa.attendance_date, aa.status FROM attendance_appeals aa JOIN users u ON aa.student_id = u.id JOIN class_sections cs ON aa.class_section_id = cs.id JOIN subjects s ON cs.subject_id = s.id WHERE cs.teacher_id = ? AND aa.status = 'pending' ORDER BY aa.created_at DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $appeals = $stmt->get_result();

    // =========================
    // ENROLLED STUDENTS
    // =========================
    $stmt = $conn->prepare("SELECT DISTINCT u.id, u.first_name, u.last_name, u.id_number, u.email FROM users u JOIN class_enrollments ce ON u.id = ce.student_id JOIN class_sections cs ON ce.class_section_id = cs.id WHERE cs.teacher_id = ? ORDER BY u.first_name, u.last_name");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $students = $stmt->get_result();

    // =========================
    // ANNOUNCEMENTS
    // =========================
    $stmt = $conn->prepare("SELECT id, title, content, created_at FROM announcements WHERE author_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $announcements = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Attendance Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Attendify/public/assets/css/teacher-dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="#" class="logo">
                    <i class="fas fa-chalkboard-user"></i>
                    <span>Attendify.</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <li class="nav-item">
                    <button class="nav-link active" onclick="switchSection('dashboard', event)">
                        <i class="fas fa-home"></i>
                        <span> Teacher Dashboard</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('take-attendance', event)">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Take Attendance</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('class-attendance', event)">
                        <i class="fas fa-chart-bar"></i>
                        <span>Class Attendance</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('students', event)">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
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
                        <i class="fas fa-file-chart-line"></i>
                        <span>Reports</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="switchSection('profile', event)">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
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
                    <input type="text" placeholder="Search students, classes...">
                </div>

                <div class="top-nav-right">
                    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>

                    <button class="notification-bell" onclick="toggleNotifications()" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">5</span>
                    </button>

                    <div class="teacher-profile" onclick="toggleUserMenu()" title="User Profile">
                        <div class="teacher-avatar"><?php 
        echo strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1)); 
        ?></div>
                        <div class="teacher-info">
                            <span class="teacher-name"><?php echo "Mr. " . htmlspecialchars($user['first_name']); ?></span>
                            <span class="teacher-role"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- CONTENT AREA -->
            <div class="content">
                <?php include __DIR__ . '/sections/dashboard.php'; ?>
                <?php include __DIR__ . '/sections/take_attendance.php'; ?>
                <?php include __DIR__ . '/sections/class_attendance.php'; ?>
                <?php include __DIR__ . '/sections/students.php'; ?>
                <?php include __DIR__ . '/sections/appeals.php'; ?>
                <?php include __DIR__ . '/sections/announcements.php'; ?>
                <?php include __DIR__ . '/sections/reports.php'; ?>
                <?php include __DIR__ . '/sections/profile.php'; ?>
            </div>
        </div>
    </div>

    <script src="/Attendify/public/assets/js/dashboard.js"></script>
</body>
</html>

