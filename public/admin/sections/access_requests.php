<!-- ==================== ACCESS REQUESTS SECTION ==================== -->
<div id="access-requests" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Access Requests</h2>
        <p class="welcome-subtitle">Review and manage role access requests for Teachers and Administrators</p>
    </div>

    <!-- FILTER & STATS -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="section-title">
            <i class="fas fa-filter"></i>
            Filter Requests
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
            <div class="form-group">
                <label class="form-label">Role</label>
                <select class="form-select" id="roleFilter" onchange="filterRequests()">
                    <option value="">All Roles</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Department</label>
                <select class="form-select" id="departmentFilter" onchange="filterRequests()">
                    <option value="">All Departments</option>
                    <option value="Science">Science</option>
                    <option value="English">English</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Chemistry">Chemistry</option>
                </select>
            </div>

            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button class="btn btn-secondary" onclick="resetFilters()" style="width: 100%;">Reset Filters</button>
            </div>
        </div>
    </div>

    <!-- REQUEST STATISTICS -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-number" id="pendingCount"><?php echo $pending_count; ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number" id="approvedCount"><?php echo $approved_count; ?></div>
            <div class="stat-label">Approved</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-number" id="rejectedCount"><?php echo $rejected_count; ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <!-- PENDING REQUESTS TABLE -->
    <div class="section-title">
        <i class="fas fa-hourglass-half"></i>
        Pending Access Requests (Students Requesting Teacher/Admin Access)
    </div>

    <div class="table-container">
        <table id="pendingRequestsTable">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>ID Number</th>
                    <th>Department</th>
                    <th>Year Level</th>
                    <th>Request Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pending_requests) > 0): ?>
                    <?php foreach ($pending_requests as $request): ?>
                        <tr data-request-id="<?php echo $request['id']; ?>" data-role="student">
                            <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                            <td><?php echo htmlspecialchars($request['id_number']); ?></td>
                            <td><?php echo htmlspecialchars($request['department']); ?></td>
                            <td><?php echo htmlspecialchars($request['year_level']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn btn-primary" onclick="showRoleModal(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?>')" style="width: auto; padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                    <button class="btn btn-danger" onclick="rejectRequest(<?php echo $request['id']; ?>)" style="width: auto; padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-secondary);">
                            No pending requests at this time.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- APPROVED REQUESTS TABLE -->
    <div class="section-title" style="margin-top: 40px;">
        <i class="fas fa-check-circle"></i>
        Approved Users (Teachers & Administrators)
    </div>

    <div class="table-container">
        <table id="approvedRequestsTable">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>ID Number</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Approved Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($approved_requests) > 0): ?>
                    <?php foreach ($approved_requests as $approved): ?>
                        <tr data-request-id="<?php echo $approved['id']; ?>" data-role="<?php echo $approved['role']; ?>">
                            <td><?php echo htmlspecialchars($approved['first_name'] . ' ' . $approved['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($approved['email']); ?></td>
                            <td><?php echo htmlspecialchars($approved['id_number']); ?></td>
                            <td>
                                <?php if ($approved['role'] === 'teacher'): ?>
                                    <span class="badge" style="background-color: #3b82f6; color: white;">Teacher</span>
                                <?php elseif ($approved['role'] === 'admin'): ?>
                                    <span class="badge" style="background-color: #8b5cf6; color: white;">Administrator</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($approved['department']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($approved['created_at'])); ?></td>
                            <td><span class="badge badge-approved"><i class="fas fa-check"></i> Approved</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-secondary);">
                            No approved users yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
