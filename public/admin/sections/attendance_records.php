<!-- ==================== ATTENDANCE RECORDS SECTION ==================== -->
<div id="attendance-records" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Attendance Records</h2>
        <p class="welcome-subtitle">View and manage all attendance records</p>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <div class="section-title">
            <i class="fas fa-filter"></i>
            Filter Records
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
            <div class="form-group">
                <label class="form-label">Class</label>
                <select class="form-select">
                    <option>All Classes</option>
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
                <button class="btn btn-primary">Filter</button>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Aarav Patel</td>
                    <td>10-A</td>
                    <td>Mathematics</td>
                    <td>March 12, 2026</td>
                    <td><span class="badge badge-success">Present</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
