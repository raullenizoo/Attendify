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
                    <th>ID Number</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows > 0): ?>
                    <?php while ($student = $students->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Student Name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td data-label="ID Number"><?php echo htmlspecialchars($student['id_number']); ?></td>
                        <td data-label="Email"><?php echo htmlspecialchars($student['email']); ?></td>
                        <td data-label="Status"><span class="badge badge-success">Active</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No students enrolled in your classes</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
