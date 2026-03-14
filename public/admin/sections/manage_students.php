<!-- ==================== MANAGE STUDENTS SECTION ==================== -->
<div id="manage-students" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Manage Students</h2>
        <p class="welcome-subtitle">Add, edit, or remove student records</p>
    </div>

    <div style="margin-bottom: 20px;">
        <button class="btn btn-primary" style="width: auto; padding: 10px 20px;">
            <i class="fas fa-plus"></i> Add New Student
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Roll No.</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_requests as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['id_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['department']); ?></td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
