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
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appeals->num_rows > 0): ?>
                    <?php while ($appeal = $appeals->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Student Name"><?php echo htmlspecialchars($appeal['first_name'] . ' ' . $appeal['last_name']); ?></td>
                        <td data-label="Subject"><?php echo htmlspecialchars($appeal['subject_name']); ?></td>
                        <td data-label="Date"><?php echo date("F d, Y", strtotime($appeal['attendance_date'])); ?></td>
                        <td data-label="Status"><span class="badge badge-pending">Pending</span></td>
                        <td data-label="Action"><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Review</button></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No pending appeals</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
