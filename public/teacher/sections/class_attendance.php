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
                <?php if ($class_attendance->num_rows > 0): ?>
                    <?php while ($class = $class_attendance->fetch_assoc()): ?>
                    <?php
                    $total = $class['total_students'] ?? 0;
                    $present = $class['present'] ?? 0;
                    $absent = $class['absent'] ?? 0;
                    $late = $class['late'] ?? 0;
                    $percentage = $total > 0 ? round((($present) / $total) * 100) : 0;
                    $badge_class = $percentage >= 80 ? 'badge-success' : ($percentage >= 70 ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td data-label="Class"><?php echo htmlspecialchars($class['subject_name']) . ' (' . htmlspecialchars($class['section_name']) . ')'; ?></td>
                        <td data-label="Total Students"><?php echo $total; ?></td>
                        <td data-label="Present"><?php echo $present; ?></td>
                        <td data-label="Absent"><?php echo $absent; ?></td>
                        <td data-label="Late"><?php echo $late; ?></td>
                        <td data-label="Attendance %"><span class="badge <?php echo $badge_class; ?>"><?php echo $percentage; ?>%</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No class attendance data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
