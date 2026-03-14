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
            <?php if (count($all_classes) > 0): ?>
                <?php foreach ($all_classes as $class): ?>
                <button class="btn btn-primary" onclick="loadClassStudents(<?php echo $class['id']; ?>)"><?php echo htmlspecialchars($class['subject_name']) . ' - ' . htmlspecialchars($class['section_name']); ?></button>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No classes assigned.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>ID Number</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="attendance-tbody">
                <tr id="no-class-message">
                    <td colspan="4" style="text-align:center;">Select a class to view students</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="display: flex; gap: 12px; margin-top: 20px;">
        <button class="btn btn-primary" onclick="submitAttendance()" style="flex: 1;">Submit Attendance</button>
        <button class="btn btn-secondary" onclick="resetAttendanceForm()" style="flex: 1;">Cancel</button>
    </div>
</div>
