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
                        <span class="badge badge-success"><?= $percentage ?>%</span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
