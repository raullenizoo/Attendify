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
                        } elseif($status=="late"){
                            echo '<span class="badge badge-warning"><i class="fas fa-clock"></i> Late</span>';
                        } else {
                            echo '<span class="badge badge-danger"><i class="fas fa-times"></i> Absent</span>';
                        }
                        ?>
                    </td>
                    <td><?= $row['check_in_time'] ? date("h:i A",strtotime($row['check_in_time'])) : "—" ?></td>
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
