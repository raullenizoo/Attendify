<!-- ==================== MANAGE TEACHERS SECTION ==================== -->
<div id="manage-teachers" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Manage Teachers</h2>
        <p class="welcome-subtitle">Add, edit, or remove teacher records</p>
    </div>

    <div style="margin-bottom: 20px;">
        <button class="btn btn-primary" style="width: auto; padding: 10px 20px;">
            <i class="fas fa-plus"></i> Add New Teacher
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Teacher Name</th>
                    <th>Employee ID</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_filter($approved_requests, fn($r) => $r['role'] === 'teacher') as $teacher): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['id_number']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td><button class="btn btn-secondary" style="width: auto; padding: 6px 12px;">Edit</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
