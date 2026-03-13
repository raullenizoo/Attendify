<?php
    session_start();
    require '../../config/db.php';
    require '../../includes/security.php';

    if (!isset($_SESSION['user_id'])) {
    header("Location: /Attendify/pages/get-started.php"); // redirect to login/start page
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
    $email = $user['email'];
    $department = $user['department'];

    /* =========================
    Total Students Card 
    ========================= */
        // TOTAL STUDENTS under this teacher
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT ce.student_id) AS total_students
        FROM class_enrollments ce
        JOIN class_sections cs ON ce.class_section_id = cs.id
        WHERE cs.teacher_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_students = $stmt->get_result()->fetch_assoc()['total_students'];

    
    // Classes Today Card

    $stmt = $conn->prepare("
    SELECT COUNT(*) AS total_classes
    FROM class_sections
    WHERE teacher_id = ? AND is_active = 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $classes_today = $stmt->get_result()->fetch_assoc()['total_classes'];

    // Present Today Card
    $stmt = $conn->prepare("
    SELECT COUNT(*) AS present_today
    FROM attendance_records ar
    JOIN class_sections cs ON ar.class_section_id = cs.id
    WHERE cs.teacher_id = ?
    AND ar.attendance_date = CURDATE()
    AND ar.status = 'present'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $present_today = $stmt->get_result()->fetch_assoc()['present_today'];

    // Absent Today Card
    $stmt = $conn->prepare("
    SELECT COUNT(*) AS absent_today
    FROM attendance_records ar
    JOIN class_sections cs ON ar.class_section_id = cs.id
    WHERE cs.teacher_id = ?
    AND ar.attendance_date = CURDATE()
    AND ar.status = 'absent'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $absent_today = $stmt->get_result()->fetch_assoc()['absent_today'];


    // pencing appeals card
    $pending_appeals = 0;

    // =========================
    // TODAY'S CLASSES
    // =========================
    $stmt = $conn->prepare("
    SELECT 
        cs.id,
        s.subject_name,
        cs.section_name,
        cs.schedule,
        cs.room
    FROM class_sections cs
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.teacher_id = ? 
    AND cs.is_active = 1
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $today_classes = $stmt->get_result();


    // =========================
    // RECENT ATTENDANCE ACTIVITY
    // =========================
    $stmt = $conn->prepare("
    SELECT 
        u.first_name,
        u.last_name,
        s.subject_name,
        ar.attendance_date,
        ar.status
    FROM attendance_records ar
    JOIN users u ON ar.student_id = u.id
    JOIN class_sections cs ON ar.class_section_id = cs.id
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.teacher_id = ?
    ORDER BY ar.attendance_date DESC
    LIMIT 10
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recent_attendance = $stmt->get_result();



    // =========================
    // PENDING APPEALS
    // =========================
    $stmt = $conn->prepare("
    SELECT 
    u.first_name,
    u.last_name,
    s.subject_name,
    aa.attendance_date,
    aa.status
    FROM attendance_appeals aa
    JOIN users u ON aa.student_id = u.id
    JOIN class_sections cs ON aa.class_section_id = cs.id
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.teacher_id = ?
    AND aa.status = 'pending'
    ORDER BY aa.created_at DESC
    LIMIT 5
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $appeals = $stmt->get_result();

    // =========================
    // ANNOUNCEMENTS
    // =========================
    $stmt = $conn->prepare("
    SELECT title, created_at
    FROM announcements
    WHERE author_id = ?
    ORDER BY created_at DESC
    LIMIT 5
    ");

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

                <!-- ==================== DASHBOARD SECTION ==================== -->
                <div id="dashboard" class="section active">
                    <!-- WELCOME SECTION -->
                    <div class="welcome-section">
                        <h2 class="welcome-title">Welcome back, Instructor! 👋</h2>
                        <p class="welcome-subtitle">Today is <span id="current-date"></span> — Here is your class attendance overview today.</p>
                    </div>

                    <!-- STATISTICS CARDS -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?php echo $total_students; ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stat-number"><?php echo $classes_today; ?></div>
                            <div class="stat-label">Classes Today</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $present_today; ?></div>
                            <div class="stat-label">Present Today</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $absent_today; ?></div>
                            <div class="stat-label">Absent Today</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $pending_appeals; ?></div>
                            <div class="stat-label">Pending Appeals</div>
                        </div>
                    </div>

                    <!-- TODAY'S CLASSES -->
                    <div class="section-title">
                        <i class="fas fa-calendar-check"></i>
                        Today's Classes
                    </div>

                    <div class="classes-grid">
                        <?php if ($today_classes->num_rows > 0): ?>
                        <?php while ($class = $today_classes->fetch_assoc()): ?>
                        <div class="class-card">
                            <div class="class-time">
                                <i class="fas fa-clock"></i>
                                <?php echo htmlspecialchars($class['schedule']); ?>
                            </div>
                            <div class="class-subject"><?php echo htmlspecialchars($class['subject_name']); ?></div>
                            <div class="class-section">
                                <i class="fas fa-layer-group"></i>
                                <?php echo htmlspecialchars($class['section_name']); ?>
                            </div>
                            <div class="class-room">
                                <i class="fas fa-door-open"></i>
                                <?php echo htmlspecialchars($class['room']); ?>
                            </div>
                            <button class="btn btn-primary">Take Attendance</button>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>

                        <p>No classes assigned today.</p>
                    <?php endif; ?>
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
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_attendance->num_rows > 0): ?>
                                <?php while ($row = $recent_attendance->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <td><?php echo date("F d, Y", strtotime($row['attendance_date'])); ?></td>
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i>
                                        <?php
                                        $status = $row['status'];

                                        if ($status == 'present') {
                                            echo '<span class="badge badge-success"><i class="fas fa-check"></i> Present</span>';
                                        } elseif ($status == 'late') {
                                            echo '<span class="badge badge-warning"><i class="fas fa-clock"></i> Late</span>';
                                        } elseif ($status == 'absent') {
                                            echo '<span class="badge badge-danger"><i class="fas fa-times"></i> Absent</span>';
                                        } else {
                                            echo '<span class="badge badge-secondary">Excused</span>';
                                        }
                                        ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center;">No attendance records yet</td>
                                    </tr>
                                    <?php endif; ?>
                            </tbody>
                                    
                        </table>
                    </div>

                    <!-- PENDING APPEALS & ANNOUNCEMENTS -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px;">
                        <!-- PENDING APPEALS -->
                        <div>
                            <div class="section-title">
                                <i class="fas fa-flag"></i>
                                Pending Appeals
                            </div>

                            <div class="appeals-list">
                                <?php if ($appeals->num_rows > 0): ?>
                                <?php while ($row = $appeals->fetch_assoc()): ?>
                                <div class="appeal-item">
                                    <div class="appeal-info">
                                        <div class="appeal-student"><?php echo htmlspecialchars($row['first_name']." ".$row['last_name']); ?></div>
                                        <div class="appeal-details">
                                            <span><strong>Subject:</strong> <?php echo htmlspecialchars($row['subject_name']); ?></span>
                                            <span><strong>Date:</strong> <?php echo date("F d, Y", strtotime($row['attendance_date'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="appeal-status">
                                        <span class="badge badge-pending">Pending</span>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <p>No pending appeals.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ANNOUNCEMENTS -->
                        <div>
                            <div class="section-title">
                                <i class="fas fa-megaphone"></i>
                                Recent Announcements
                            </div>

                            <div class="announcements-list">
                                <?php if ($announcements->num_rows > 0): ?>
                                <?php while ($row = $announcements->fetch_assoc()): ?>
                                <div class="announcement-item">
                                    <div class="announcement-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <div class="announcement-date"><?php echo date("F d, Y - h:i A", strtotime($row['created_at'])); ?></div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <p>No announcements.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ==================== TAKE ATTENDANCE SECTION ==================== -->
                <div id="take-attendance" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Take Attendance</h2>
                        <p class="welcome-subtitle">Mark attendance for your students</p>
                    </div>

                    <div class="card" style="margin-bottom: 24px;">
                        <div class="section-title">
                            <i class="fas fa-clipboard-check"></i>
                            Select Class
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <button class="btn btn-primary">Mathematics - Class 10-A</button>
                            <button class="btn btn-secondary">Physics - Class 10-B</button>
                            <button class="btn btn-secondary">English - Class 10-C</button>
                            <button class="btn btn-secondary">Chemistry - Class 10-A</button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Roll No.</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aarav Patel</td>
                                    <td>001</td>
                                    <td><span class="badge badge-success">Present</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                </tr>
                                <tr>
                                    <td>Priya Sharma</td>
                                    <td>002</td>
                                    <td><span class="badge badge-success">Present</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                </tr>
                                <tr>
                                    <td>Rohan Gupta</td>
                                    <td>003</td>
                                    <td><span class="badge badge-danger">Absent</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                </tr>
                                <tr>
                                    <td>Ananya Singh</td>
                                    <td>004</td>
                                    <td><span class="badge badge-warning">Late</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                </tr>
                                <tr>
                                    <td>Vikram Kumar</td>
                                    <td>005</td>
                                    <td><span class="badge badge-success">Present</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 20px;">
                        <button class="btn btn-primary" style="flex: 1;">Submit Attendance</button>
                        <button class="btn btn-secondary" style="flex: 1;">Cancel</button>
                    </div>
                </div>

                <!-- ==================== CLASS ATTENDANCE SECTION ==================== -->
                <div id="class-attendance" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Class Attendance</h2>
                        <p class="welcome-subtitle">View attendance statistics for your classes</p>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Total Students</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Attendance %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Class 10-A (Mathematics)</td>
                                    <td>35</td>
                                    <td>33</td>
                                    <td>1</td>
                                    <td>1</td>
                                    <td><span class="badge badge-success">94%</span></td>
                                </tr>
                                <tr>
                                    <td>Class 10-B (Physics)</td>
                                    <td>32</td>
                                    <td>30</td>
                                    <td>2</td>
                                    <td>0</td>
                                    <td><span class="badge badge-success">94%</span></td>
                                </tr>
                                <tr>
                                    <td>Class 10-C (English)</td>
                                    <td>38</td>
                                    <td>35</td>
                                    <td>2</td>
                                    <td>1</td>
                                    <td><span class="badge badge-success">92%</span></td>
                                </tr>
                                <tr>
                                    <td>Class 10-A (Chemistry)</td>
                                    <td>40</td>
                                    <td>30</td>
                                    <td>7</td>
                                    <td>3</td>
                                    <td><span class="badge badge-warning">75%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== STUDENTS SECTION ==================== -->
                <div id="students" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Students</h2>
                        <p class="welcome-subtitle">Manage your students and their information</p>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Roll No.</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aarav Patel</td>
                                    <td>001</td>
                                    <td>aarav.patel@school.edu</td>
                                    <td>10-A</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>Priya Sharma</td>
                                    <td>002</td>
                                    <td>priya.sharma@school.edu</td>
                                    <td>10-B</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>Rohan Gupta</td>
                                    <td>003</td>
                                    <td>rohan.gupta@school.edu</td>
                                    <td>10-C</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>Ananya Singh</td>
                                    <td>004</td>
                                    <td>ananya.singh@school.edu</td>
                                    <td>10-A</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>Vikram Kumar</td>
                                    <td>005</td>
                                    <td>vikram.kumar@school.edu</td>
                                    <td>10-B</td>
                                    <td><span class="badge badge-success">Active</span></td>
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
                                <tr>
                                    <td>Neha Verma</td>
                                    <td>Mathematics</td>
                                    <td>March 09, 2026</td>
                                    <td>Family Event</td>
                                    <td><span class="badge badge-approved">Approved</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">View</button></td>
                                </tr>
                                <tr>
                                    <td>Sanjay Iyer</td>
                                    <td>English</td>
                                    <td>March 08, 2026</td>
                                    <td>Doctor Appointment</td>
                                    <td><span class="badge badge-rejected">Rejected</span></td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">View</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== ANNOUNCEMENTS SECTION ==================== -->
                <div id="announcements" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">Announcements</h2>
                        <p class="welcome-subtitle">Create and manage announcements for your students</p>
                    </div>

                    <div class="announcements-container">
                        <div>
                            <div class="section-title">
                                <i class="fas fa-list"></i>
                                Recent Announcements
                            </div>

                            <div class="announcements-list">
                                <div class="announcement-item">
                                    <div class="announcement-title">Class Rescheduled</div>
                                    <div class="announcement-date">March 12, 2026 - 2:30 PM</div>
                                </div>

                                <div class="announcement-item">
                                    <div class="announcement-title">Exam Schedule Released</div>
                                    <div class="announcement-date">March 10, 2026 - 10:15 AM</div>
                                </div>

                                <div class="announcement-item">
                                    <div class="announcement-title">Holiday Notice</div>
                                    <div class="announcement-date">March 08, 2026 - 9:45 AM</div>
                                </div>

                                <div class="announcement-item">
                                    <div class="announcement-title">Staff Meeting Reminder</div>
                                    <div class="announcement-date">March 07, 2026 - 3:20 PM</div>
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
                                        <label class="form-label">Select Class</label>
                                        <select class="form-input">
                                            <option>All Classes</option>
                                            <option>Class 10-A</option>
                                            <option>Class 10-B</option>
                                            <option>Class 10-C</option>
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
                        <h2 class="welcome-title">Reports</h2>
                        <p class="welcome-subtitle">View and generate attendance reports</p>
                    </div>

                    <div class="card" style="margin-bottom: 24px;">
                        <div class="section-title">
                            <i class="fas fa-filter"></i>
                            Generate Report
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Class</label>
                                <select class="form-input">
                                    <option>Select Class</option>
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
                                <button class="btn btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Class</th>
                                    <th>Period</th>
                                    <th>Generated Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Attendance Summary - March 2026</td>
                                    <td>Class 10-A</td>
                                    <td>March 1-12, 2026</td>
                                    <td>March 12, 2026</td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Download</button></td>
                                </tr>
                                <tr>
                                    <td>Student Attendance Details</td>
                                    <td>Class 10-B</td>
                                    <td>March 1-12, 2026</td>
                                    <td>March 12, 2026</td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Download</button></td>
                                </tr>
                                <tr>
                                    <td>Monthly Attendance Report</td>
                                    <td>Class 10-C</td>
                                    <td>February 1-28, 2026</td>
                                    <td>March 01, 2026</td>
                                    <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Download</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== PROFILE SECTION ==================== -->
                <div id="profile" class="section">
                    <div class="welcome-section">
                        <h2 class="welcome-title">My Profile</h2>
                        <p class="welcome-subtitle">Manage your profile information</p>
                    </div>

                    <div class="card" style="max-width: 600px;">
                        <div style="text-align: center; margin-bottom: 24px;">
                            <div class="teacher-avatar" style="width: 100px; height: 100px; font-size: 40px; margin: 0 auto 16px;"><?php 
echo strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1)); 
?></div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></div>
                            <div style="font-size: 14px; color: var(--text-secondary);">Mathematics Teacher</div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-input" value="<?php echo $user['first_name']; ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-input" value="<?php echo $user['last_name']; ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-input" value="<?php echo $user['email']; ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-input" value="<?php echo $user['department']; ?>" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">ID Number</label>
                                <input type="text" class="form-input" value="<?php echo $user['id_number']; ?>" disabled>
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

    <script src="/Attendify/public/assets/js/dashboard.js"></script>
</body>
</html>
