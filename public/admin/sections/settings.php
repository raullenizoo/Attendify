<!-- ==================== SETTINGS SECTION ==================== -->
<div id="settings" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">System Settings</h2>
        <p class="welcome-subtitle">Configure system-wide settings</p>
    </div>

    <div class="card" style="max-width: 600px;">
        <div class="section-title">
            <i class="fas fa-cog"></i>
            General Settings
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group">
                <label class="form-label">School Name</label>
                <input type="text" class="form-input" value="ABC School" placeholder="Enter school name">
            </div>

            <div class="form-group">
                <label class="form-label">School Email</label>
                <input type="email" class="form-input" value="admin@abcschool.edu" placeholder="Enter school email">
            </div>

            <div class="form-group">
                <label class="form-label">Attendance Threshold (%)</label>
                <input type="number" class="form-input" value="75" placeholder="Enter attendance threshold">
            </div>

            <div class="form-group">
                <label class="form-label">Academic Year</label>
                <input type="text" class="form-input" value="2025-2026" placeholder="Enter academic year">
            </div>

            <div style="display: flex; gap: 12px;">
                <button class="btn btn-primary">Save Settings</button>
                <button class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>
