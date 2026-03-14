<!-- ==================== REPORTS SECTION ==================== -->
<div id="reports" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Reports & Analytics</h2>
        <p class="welcome-subtitle">View comprehensive attendance reports and analytics</p>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <div class="section-title">
            <i class="fas fa-filter"></i>
            Generate Report
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
            <div class="form-group">
                <label class="form-label">Report Type</label>
                <select class="form-select">
                    <option>Select Report Type</option>
                    <option>Attendance Summary</option>
                    <option>Student Attendance</option>
                    <option>Teacher Performance</option>
                    <option>Class Attendance</option>
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

    <div class="section-title">
        <i class="fas fa-chart-line"></i>
        Analytics Dashboard
    </div>

    <div class="chart-grid">
        <div class="chart-container">
            <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Weekly Attendance Trend</h3>
            <div class="chart-placeholder">
                <i class="fas fa-chart-line"></i> Chart Placeholder
            </div>
        </div>

        <div class="chart-container">
            <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Student Attendance Rate</h3>
            <div class="chart-placeholder">
                <i class="fas fa-pie-chart"></i> Chart Placeholder
            </div>
        </div>

        <div class="chart-container">
            <h3 style="margin-bottom: 16px; font-size: 15px; font-weight: 600;">Absence Statistics</h3>
            <div class="chart-placeholder">
                <i class="fas fa-bar-chart"></i> Chart Placeholder
            </div>
        </div>
    </div>
</div>
