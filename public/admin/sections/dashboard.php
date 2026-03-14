<!-- ==================== DASHBOARD SECTION ==================== -->
<div id="dashboard" class="section active">
    <!-- WELCOME SECTION -->
    <div class="welcome-section">
        <h2 class="welcome-title">Welcome back, Administrator! 👋</h2>
        <p class="welcome-subtitle">Today is <span id="current-date"></span> — Here is the overview of today's attendance activity.</p>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="quick-actions">
        <button class="action-btn">
            <i class="fas fa-plus-circle"></i>
            Add Student
        </button>
        <button class="action-btn">
            <i class="fas fa-plus-circle"></i>
            Add Teacher
        </button>
        <button class="action-btn">
            <i class="fas fa-plus-circle"></i>
            Create Class
        </button>
        <button class="action-btn">
            <i class="fas fa-file-export"></i>
            Export Report
        </button>
    </div>

    <!-- SYSTEM OVERVIEW STATISTICS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number"><?php echo count($pending_requests); ?></div>
            <div class="stat-label">Total Students</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="stat-number"><?php echo count(array_filter($approved_requests, fn($r) => $r['role'] === 'teacher')); ?></div>
            <div class="stat-label">Total Teachers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon secondary">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-number">42</div>
            <div class="stat-label">Total Classes</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-number">92.5%</div>
            <div class="stat-label">Today's Attendance Rate</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-number"><?php echo $pending_count; ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
    </div>

    <!-- RECENT ATTENDANCE ACTIVITY -->
    <div class="section-title">
        <i class="fas fa-history"></i>
        Recent Attendance Activity
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Aarav Patel</td>
                    <td>Mathematics</td>
                    <td>Mr. Robert</td>
                    <td>March 12, 2026</td>
                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Present</span></td>
                </tr>
                <tr>
                    <td>Priya Sharma</td>
                    <td>Physics</td>
                    <td>Dr. Sarah Smith</td>
                    <td>March 12, 2026</td>
                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Present</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
