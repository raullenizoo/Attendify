<?php
session_start();
require '../../config/db.php';

$user_id = $_SESSION['user_id'];

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: /Attendify/public/get-started.php");
    exit();
}



// =========================
// 1. USER PROFILE
// =========================
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$full_name = $user['first_name'] . ' ' . $user['last_name'];
$id_number = $user['id_number'];

// =========================
// 2. ATTENDANCE SUMMARY
// =========================
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_classes,
        SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN status='absent' THEN 1 ELSE 0 END) AS absent,
        SUM(CASE WHEN status='late' THEN 1 ELSE 0 END) AS late
    FROM attendance_records
    WHERE student_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$total_classes = $summary['total_classes'] ?? 0;
$present = $summary['present'] ?? 0;
$absent = $summary['absent'] ?? 0;
$late = $summary['late'] ?? 0;
$attendance_pct = $total_classes > 0 ? round(($present/$total_classes)*100, 2) : 0;

// =========================
// 3. RECENT ATTENDANCE
// =========================
$stmt = $conn->prepare("
    SELECT 
        ar.attendance_date,
        ar.status,
        ar.check_in_time,
        s.subject_name,
        CONCAT(u.first_name,' ',u.last_name) AS teacher
    FROM attendance_records ar
    JOIN class_sections cs ON ar.class_section_id = cs.id
    JOIN subjects s ON cs.subject_id = s.id
    JOIN users u ON cs.teacher_id = u.id
    WHERE ar.student_id = ?
    ORDER BY ar.attendance_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_attendance = $stmt->get_result();

// =========================
// 4. UPCOMING CLASSES
// =========================
$stmt = $conn->prepare("
    SELECT 
        s.subject_name,
        cs.room,
        cs.schedule,
        CONCAT(u.first_name,' ',u.last_name) AS teacher
    FROM class_sections cs
    JOIN subjects s ON cs.subject_id = s.id
    JOIN users u ON cs.teacher_id = u.id
    JOIN class_enrollments ce ON ce.class_section_id = cs.id
    WHERE ce.student_id = ?
    ORDER BY cs.schedule
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$schedule = $stmt->get_result();

// =========================
// 5. ATTENDANCE BY SUBJECT
// =========================
$stmt = $conn->prepare("
    SELECT 
        s.subject_name,
        COUNT(ar.id) AS total_classes,
        SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN ar.status='absent' THEN 1 ELSE 0 END) AS absent,
        SUM(CASE WHEN ar.status='late' THEN 1 ELSE 0 END) AS late
    FROM attendance_records ar
    JOIN class_sections cs ON ar.class_section_id = cs.id
    JOIN subjects s ON cs.subject_id = s.id
    WHERE ar.student_id = ?
    GROUP BY s.subject_name
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subject_attendance = $stmt->get_result();

// =========================
// 6. COMPLETE ATTENDANCE HISTORY
// =========================
$stmt = $conn->prepare("
    SELECT 
        ar.attendance_date,
        s.subject_name,
        CONCAT(t.first_name,' ',t.last_name) AS teacher_name,
        ar.status,
        ar.check_in_time,
        ar.remarks
    FROM attendance_records ar
    JOIN class_sections cs ON ar.class_section_id = cs.id
    JOIN subjects s ON cs.subject_id = s.id
    JOIN users t ON cs.teacher_id = t.id
    WHERE ar.student_id = ?
    ORDER BY ar.attendance_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attendance_history = $stmt->get_result(); // ✅ Use this variable later

// =========================
// 7. ANNOUNCEMENTS
// =========================
$stmt = $conn->prepare("
    SELECT DISTINCT
        a.id,
        a.title,
        a.content,
        a.priority,
        a.created_at,
        a.class_section_id,
        CONCAT(u.first_name,' ',u.last_name) AS author_name,
        s.subject_name,
        cs.section_name
    FROM announcements a
    JOIN users u ON a.author_id = u.id
    LEFT JOIN class_sections cs ON a.class_section_id = cs.id
    LEFT JOIN subjects s ON cs.subject_id = s.id
    LEFT JOIN class_enrollments ce ON ce.student_id = ? AND ce.class_section_id = a.class_section_id
    WHERE a.class_section_id IS NULL OR ce.student_id IS NOT NULL
    ORDER BY a.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$announcements = $stmt->get_result();

// =========================
// 8. ANNOUNCEMENTS COUNT
// =========================
$announcement_count = $announcements->num_rows;
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Dashboard - Attendance Monitoring System</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="/Attendify/public/assets/css/student-dashboard.css">
        <script src="/Attendify/public/assets/js/dashboard.js"></script>
    </head>
    <body>
        <div class="dashboard-container">
            <!-- SIDEBAR -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <a href="#" class="logo">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Attendify.</span>
                    </a>
                </div>

                <nav class="sidebar-nav">
                    <li class="nav-item">
                        <button class="nav-link active" onclick="switchSection('dashboard', event)">
                            <i class="fas fa-home"></i>
                            <span> Student Dashboard</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="switchSection('my-attendance', event)">
                            <i class="fas fa-calendar-check"></i>
                            <span>My Attendance</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="switchSection('attendance-history', event)">
                            <i class="fas fa-history"></i>
                            <span>Attendance History</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="switchSection('announcements', event)">
                            <i class="fas fa-bell"></i>
                            <span>Announcements</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="switchSection('profile', event)">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
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

                    <div class="top-nav-right">
                        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>

                        <button class="notification-bell" onclick="toggleNotifications()" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge"><?= $announcement_count ?></span>
                        </button>

                        <div class="user-profile" onclick="toggleUserMenu()" title="User Profile">
                            <div class="user-avatar"><?= strtoupper(substr($full_name,0,1)) ?></div>
                            <div class="user-info">
                                <span class="user-name"> <?= htmlspecialchars($full_name) ?></span>
                                <span class="user-role">Student</span>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- CONTENT AREA -->
                <div class="content">

                    <?php include __DIR__ . '/sections/dashboard.php'; ?>
                    <?php include __DIR__ . '/sections/my_attendance.php'; ?>
                    <?php include __DIR__ . '/sections/attendance_history.php'; ?>
                    <?php include __DIR__ . '/sections/announcements.php'; ?>
                    <?php include __DIR__ . '/sections/profile.php'; ?>

                </div>
            </div>
        </div>
    </body>
    </html>