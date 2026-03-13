<?php
    session_start();
    include '../../db/config.php';
    require '../../includes/security.php';

    if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/get-started.php"); // redirect to login/start page
    exit();
    }

    $user_id = $_SESSION['user_id'];

    $sql_user_name = "SELECT first_name, last_name, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql_user_name);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Attendance Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/teacher-dashboard.css">
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
        echo strtoupper(substr($teacher['first_name'],0,1) . substr($teacher['last_name'],0,1)); 
        ?></div>
                        <div class="teacher-info">
                            <span class="teacher-name"><?php echo "Mr. " . htmlspecialchars($teacher['first_name']); ?></span>
                            <span class="teacher-role"><?php echo ucfirst($teacher['role']); ?></span>
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
                            <div class="stat-number">145</div>
                            <div class="stat-label">Total Students</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stat-number">4</div>
                            <div class="stat-label">Classes Today</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-number">128</div>
                            <div class="stat-label">Present Today</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-number">12</div>
                            <div class="stat-label">Absent Today</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="stat-number">3</div>
                            <div class="stat-label">Pending Appeals</div>
                        </div>
                    </div>

                    <!-- TODAY'S CLASSES -->
                    <div class="section-title">
                        <i class="fas fa-calendar-check"></i>
                        Today's Classes
                    </div>

                    <div class="classes-grid">
                        <div class="class-card">
                            <div class="class-time">
                                <i class="fas fa-clock"></i>
                                09:00 AM - 10:00 AM
                            </div>
                            <div class="class-subject">Mathematics</div>
                            <div class="class-section">
                                <i class="fas fa-layer-group"></i>
                                Class 10-A
                            </div>
                            <div class="class-room">
                                <i class="fas fa-door-open"></i>
                                Room 201
                            </div>
                            <button class="btn btn-primary">Take Attendance</button>
                        </div>

                        <div class="class-card">
                            <div class="class-time">
                                <i class="fas fa-clock"></i>
                                10:30 AM - 11:30 AM
                            </div>
                            <div class="class-subject">Physics</div>
                            <div class="class-section">
                                <i class="fas fa-layer-group"></i>
                                Class 10-B
                            </div>
                            <div class="class-room">
                                <i class="fas fa-door-open"></i>
                                Lab 105
                            </div>
                            <button class="btn btn-primary">Take Attendance</button>
                        </div>

                        <div class="class-card">
                            <div class="class-time">
                                <i class="fas fa-clock"></i>
                                01:00 PM - 02:00 PM
                            </div>
                            <div class="class-subject">English</div>
                            <div class="class-section">
                                <i class="fas fa-layer-group"></i>
                                Class 10-C
                            </div>
                            <div class="class-room">
                                <i class="fas fa-door-open"></i>
                                Room 305
                            </div>
                            <button class="btn btn-primary">Take Attendance</button>
                        </div>

                        <div class="class-card">
                            <div class="class-time">
                                <i class="fas fa-clock"></i>
                                02:30 PM - 03:30 PM
                            </div>
                            <div class="class-subject">Chemistry</div>
                            <div class="class-section">
                                <i class="fas fa-layer-group"></i>
                                Class 10-A
                            </div>
                            <div class="class-room">
                                <i class="fas fa-door-open"></i>
                                Lab 202
                            </div>
                            <button class="btn btn-primary">Take Attendance</button>
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
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aarav Patel</td>
                                    <td>Mathematics</td>
                                    <td>March 12, 2026</td>
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Present</span></td>
                                </tr>
                                <tr>
                                    <td>Priya Sharma</td>
                                    <td>Physics</td>
                                    <td>March 12, 2026</td>
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Present</span></td>
                                </tr>
                                <tr>
                                    <td>Rohan Gupta</td>
                                    <td>English</td>
                                    <td>March 12, 2026</td>
                                    <td><span class="badge badge-danger"><i class="fas fa-times"></i> Absent</span></td>
                                </tr>
                                <tr>
                                    <td>Ananya Singh</td>
                                    <td>Chemistry</td>
                                    <td>March 12, 2026</td>
                                    <td><span class="badge badge-warning"><i class="fas fa-clock"></i> Late</span></td>
                                </tr>
                                <tr>
                                    <td>Vikram Kumar</td>
                                    <td>Mathematics</td>
                                    <td>March 11, 2026</td>
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Present</span></td>
                                </tr>
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
                                <div class="appeal-item">
                                    <div class="appeal-info">
                                        <div class="appeal-student">Arjun Desai</div>
                                        <div class="appeal-details">
                                            <span><strong>Subject:</strong> Physics</span>
                                            <span><strong>Date:</strong> March 10, 2026</span>
                                        </div>
                                    </div>
                                    <div class="appeal-status">
                                        <span class="badge badge-pending">Pending</span>
                                    </div>
                                </div>

                                <div class="appeal-item">
                                    <div class="appeal-info">
                                        <div class="appeal-student">Neha Verma</div>
                                        <div class="appeal-details">
                                            <span><strong>Subject:</strong> Mathematics</span>
                                            <span><strong>Date:</strong> March 09, 2026</span>
                                        </div>
                                    </div>
                                    <div class="appeal-status">
                                        <span class="badge badge-pending">Pending</span>
                                    </div>
                                </div>

                                <div class="appeal-item">
                                    <div class="appeal-info">
                                        <div class="appeal-student">Sanjay Iyer</div>
                                        <div class="appeal-details">
                                            <span><strong>Subject:</strong> English</span>
                                            <span><strong>Date:</strong> March 08, 2026</span>
                                        </div>
                                    </div>
                                    <div class="appeal-status">
                                        <span class="badge badge-pending">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ANNOUNCEMENTS -->
                        <div>
                            <div class="section-title">
                                <i class="fas fa-megaphone"></i>
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
                            <div class="teacher-avatar" style="width: 100px; height: 100px; font-size: 40px; margin: 0 auto 16px;">MR</div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Mr. Robert</div>
                            <div style="font-size: 14px; color: var(--text-secondary);">Mathematics Teacher</div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-input" value="Robert" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-input" value="Johnson" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-input" value="robert.johnson@school.edu" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-input" value="+1 (555) 123-4567" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-input" value="Science" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-input" value="TEACH-2024-001" disabled>
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

    <script>
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
                'take-attendance': 'Take Attendance',
                'class-attendance': 'Class Attendance',
                'students': 'Students',
                'appeals': 'Attendance Appeals',
                'announcements': 'Announcements',
                'reports': 'Reports',
                'profile': 'My Profile'
            };
            document.getElementById('page-title').textContent = titles[sectionId];

            // Close sidebar on mobile
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        }

        // Notification bell toggle
        function toggleNotifications() {
            alert('You have 5 new notifications!');
        }

        // User menu toggle
        function toggleUserMenu() {
            alert('User profile menu would open here');
        }

        // Logout handler
        function handleLogout() {

        if (!confirm("Are you sure you want to logout?")) return;

    // Redirect to logout PHP
            window.location.href = "/Attendify/pages/teacher/logout.php";
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
    </script>
</body>
</html>
