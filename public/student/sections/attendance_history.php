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
