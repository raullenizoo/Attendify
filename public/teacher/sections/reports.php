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
                    <th>Class</th>
                    <th>Total Students</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT cs.id, s.subject_name, cs.section_name, COUNT(DISTINCT ce.student_id) AS total_students, SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN ar.status='absent' THEN 1 ELSE 0 END) AS absent, SUM(CASE WHEN ar.status='late' THEN 1 ELSE 0 END) AS late FROM class_sections cs JOIN subjects s ON cs.subject_id = s.id JOIN class_enrollments ce ON cs.id = ce.class_section_id LEFT JOIN attendance_records ar ON ce.student_id = ar.student_id AND ar.class_section_id = cs.id WHERE cs.teacher_id = ? GROUP BY cs.id, s.subject_name, cs.section_name");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $report_data = $stmt->get_result();
                ?>
                <?php if ($report_data->num_rows > 0): ?>
                    <?php while ($report = $report_data->fetch_assoc()): ?>
                    <?php
                    $total = $report['total_students'] ?? 0;
                    $present = $report['present'] ?? 0;
                    $absent = $report['absent'] ?? 0;
                    $late = $report['late'] ?? 0;
                    $percentage = $total > 0 ? round((($present) / $total) * 100) : 0;
                    $badge_class = $percentage >= 80 ? 'badge-success' : ($percentage >= 70 ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td data-label="Class"><?php echo htmlspecialchars($report['subject_name']) . ' (' . htmlspecialchars($report['section_name']) . ')'; ?></td>
                        <td data-label="Total Students"><?php echo $total; ?></td>
                        <td data-label="Present"><?php echo $present; ?></td>
                        <td data-label="Absent"><?php echo $absent; ?></td>
                        <td data-label="Late"><?php echo $late; ?></td>
                        <td data-label="Attendance %"><span class="badge <?php echo $badge_class; ?>"><?php echo $percentage; ?>%</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No attendance data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
