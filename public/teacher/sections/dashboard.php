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
                    <td data-label="Student Name"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td data-label="Subject"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td data-label="Date"><?php echo date("F d, Y", strtotime($row['attendance_date'])); ?></td>
                    <td data-label="Status">
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
                        <div class="appeal-student"><?php echo htmlspecialchars($row['first_name']." ". $row['last_name']); ?></div>
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
                    <?php while ($announcement = $announcements->fetch_assoc()): ?>
                    <div class="announcement-item">
                        <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                        <div class="announcement-date"><?php echo date("F d, Y - h:i A", strtotime($announcement['created_at'])); ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                <p>No announcements yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
