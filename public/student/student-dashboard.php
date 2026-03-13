<?php
session_start();
require '../../config/db.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: /Attendify/public/get-started.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
        a.title, 
        a.content, 
        a.created_at,
        CONCAT(u.first_name,' ',u.last_name) AS author_name
    FROM announcements a
    JOIN users u ON a.author_id = u.id
    LEFT JOIN class_enrollments ce ON ce.student_id = ?
    WHERE a.class_section_id IS NULL OR a.class_section_id = ce.class_section_id
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
        <link rel="stylesheet" href="/Attendifyv1/public/assets/css/student-dashboard.css">
        <script src="/Attendifyv1/public/assets/js/dashboard.js"></script>
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
                    <button class="nav-link" onclick="handleLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
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

                    <!-- ==================== DASHBOARD SECTION ==================== -->
                    <div id="dashboard" class="section active">
                        <!-- WELCOME SECTION -->
                        <div class="welcome-section">
                            <h2 class="welcome-title">Welcome back, <?= htmlspecialchars($full_name) ?>! 👋</h2>
                            <p class="welcome-subtitle">Today is <span id="current-date"></span></p>
                        </div>

                        <!-- ATTENDANCE SUMMARY CARDS -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon primary">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="stat-number"><?= $total_classes ?></div>
                                <div class="stat-label">Total Classes</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-number"><?= $present ?></div>
                                <div class="stat-label">Present</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon danger">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stat-number"><?= $absent ?></div>
                                <div class="stat-label">Absent</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-number"><?= $late ?></div>
                                <div class="stat-label">Late</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon secondary">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div class="stat-number"><?= $attendance_pct ?>%</div>
                                <div class="stat-label">Attendance %</div>
                            </div>
                        </div>

                        <!-- RECENT ATTENDANCE TABLE -->
                        <div class="section-title">
                            <i class="fas fa-table"></i>
                            Recent Attendance
                        </div>

                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php if($recent_attendance->num_rows > 0): ?>

                                <?php while($row = $recent_attendance->fetch_assoc()): ?>

                                <tr>

                                <td><?= date("F d, Y", strtotime($row['attendance_date'])) ?></td>

                                <td><?= htmlspecialchars($row['subject_name']) ?></td>

                                <td><?= htmlspecialchars($row['teacher']) ?></td>

                                <td>

                                <?php
                                $status = $row['status'];

                                if($status=="present"){
                                    echo '<span class="badge badge-success"><i class="fas fa-check"></i> Present</span>';
                                }
                                elseif($status=="late"){
                                    echo '<span class="badge badge-warning"><i class="fas fa-clock"></i> Late</span>';
                                }
                                else{
                                    echo '<span class="badge badge-danger"><i class="fas fa-times"></i> Absent</span>';
                                }
                                ?>

                                </td>

                                <td>
                                <?= $row['check_in_time'] ? date("h:i A",strtotime($row['check_in_time'])) : "—" ?>
                                </td>

                                </tr>

                                <?php endwhile; ?>

                                <?php else: ?>

                                <tr>
                                <td colspan="5" style="text-align:center;">No attendance records</td>
                                </tr>

                                <?php endif; ?>

                                </tbody>
                            </table>
                        </div>

                        <!-- CLASS SCHEDULE SECTION -->
                        <div class="section-title">
                            <i class="fas fa-calendar-alt"></i>
                            Upcoming Classes
                        </div>

                        <div class="schedule-grid">

                        <?php while($row = $schedule->fetch_assoc()): ?>

                        <div class="schedule-card">

                        <div class="schedule-time">
                        <i class="fas fa-clock"></i>
                        <?= htmlspecialchars($row['schedule']) ?>
                        </div>

                        <div class="schedule-subject">
                        <?= htmlspecialchars($row['subject_name']) ?>
                        </div>

                        <div class="schedule-teacher">
                        <i class="fas fa-chalkboard-user"></i>
                        <?= htmlspecialchars($row['teacher']) ?>
                        </div>

                        <div class="schedule-room">
                        <i class="fas fa-door-open"></i>
                        <?= htmlspecialchars($row['room']) ?>
                        </div>

                        </div>

                        <?php endwhile; ?>

                        </div>
                    </div>

                    <!-- ==================== MY ATTENDANCE SECTION ==================== -->
                    <div id="my-attendance" class="section">
                        <div class="welcome-section">
                            <h2 class="welcome-title">My Attendance</h2>
                            <p class="welcome-subtitle">View your current attendance status and statistics</p>
                        </div>

                        <div class="stats-grid">

                        <div class="stat-card">
                        <div class="stat-icon primary">
                        <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-number"><?= $total_classes ?></div>
                        <div class="stat-label">Total Classes</div>
                        </div>

                        <div class="stat-card">
                        <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?= $present ?></div>
                        <div class="stat-label">Present</div>
                        </div>

                        <div class="stat-card">
                        <div class="stat-icon danger">
                        <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-number"><?= $absent ?></div>
                        <div class="stat-label">Absent</div>
                        </div>

                        <div class="stat-card">
                        <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?= $late ?></div>
                        <div class="stat-label">Late</div>
                        </div>

                        <div class="stat-card">
                        <div class="stat-icon secondary">
                        <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stat-number"><?= $attendance_pct ?>%</div>
                        <div class="stat-label">Attendance %</div>
                        </div>

                        </div>

                        <div class="section-title">
                            <i class="fas fa-list"></i>
                            Attendance by Subject
                        </div>

                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Total Classes</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Late</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>

                                        <?php while($row = $subject_attendance->fetch_assoc()): ?>

                                        <?php
                                        $total = $row['total_classes'];
                                        $present = $row['present'];
                                        $absent = $row['absent'];
                                        $late = $row['late'];

                                        $percentage = $total > 0 ? round(($present/$total)*100) : 0;
                                        ?>

                                        <tr>

                                        <td><?= htmlspecialchars($row['subject_name']) ?></td>

                                        <td><?= $total ?></td>

                                        <td><?= $present ?></td>

                                        <td><?= $absent ?></td>

                                        <td><?= $late ?></td>

                                        <td>

                                        <span class="badge badge-success">
                                        <?= $percentage ?>%
                                        </span>

                                        </td>

                                        </tr>

                                        <?php endwhile; ?>

                                        </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ==================== ATTENDANCE HISTORY SECTION ==================== -->
                    <div id="attendance-history" class="section">
                        <div class="welcome-section">
                            <h2 class="welcome-title">Attendance History</h2>
                            <p class="welcome-subtitle">Complete record of your attendance</p>
                        </div>

                        <div class="section-title">
                            <i class="fas fa-calendar"></i>
                            All Attendance Records
                        </div>

                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Status</th>
                                        <th>Check-In Time</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $attendance_history->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date("F d, Y", strtotime($row['attendance_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                                        <td>
                                            <?php
                                            $status = $row['status'];
                                            if($status == "present") echo '<span class="badge badge-success"><i class="fas fa-check"></i> Present</span>';
                                            elseif($status == "late") echo '<span class="badge badge-warning"><i class="fas fa-clock"></i> Late</span>';
                                            elseif($status == "absent") echo '<span class="badge badge-danger"><i class="fas fa-times"></i> Absent</span>';
                                            else echo '<span class="badge badge-secondary">Excused</span>';
                                            ?>
                                        </td>
                                        <td><?= $row['check_in_time'] ? date("h:i A", strtotime($row['check_in_time'])) : "—" ?></td>
                                        <td><?= htmlspecialchars($row['remarks'] ?? '—') ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ==================== ANNOUNCEMENTS SECTION ==================== -->
                    <div id="announcements" class="section">
                        <div class="welcome-section">
                            <h2 class="welcome-title">Announcements</h2>
                            <p class="welcome-subtitle">Stay updated with the latest news and announcements</p>
                        </div>

                        <div class="announcements-grid">
                            <?php while($row = $announcements->fetch_assoc()): ?>
                            <div class="announcement-card">
                                <div class="announcement-header">
                                    <div>
                                        <div class="announcement-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <div class="announcement-date"><?php echo date("F d, Y", strtotime($row['created_at'])); ?></div>
                                    </div>
                                </div>
                                <div class="announcement-teacher">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($row['author_name']); ?>
                                </div>
                                <div class="announcement-content">
                                    <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- ==================== PROFILE SECTION ==================== -->
                    <div id="profile" class="section">
                        <div class="welcome-section">
                            <h2 class="welcome-title">My Profile</h2>
                            <p class="welcome-subtitle">Manage your personal information</p>
                        </div>

                        <div class="profile-container">
                            <div class="profile-card">
                                <div class="profile-avatar-large"><?php 
    echo strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1));
    ?></div>
                                <div class="profile-name"><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></div>
                                <div class="profile-role">Student</div>

                                <div class="profile-info-grid">
                                    <div class="profile-info-item">
                                        <span class="profile-info-label">Student ID</span>
                                        <span class="profile-info-value"><?php echo htmlspecialchars($user['id_number']); ?></span>
                                    </div>
                                    <div class="profile-info-item">
                                        <span class="profile-info-label">Email</span>
                                        <span class="profile-info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div class="profile-info-item">
                                        <span class="profile-info-label">Class</span>
                                        <span class="profile-info-value"><?php echo htmlspecialchars($user['year_level'] . " - " . $user['section']); ?></span>
                                    </div>
                                    <div class="profile-info-item">
                                        <span class="profile-info-label">Enrollment Date</span>
                                        <span class="profile-info-value"><?php echo date("F d, Y", strtotime($user['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </body>
    </html>