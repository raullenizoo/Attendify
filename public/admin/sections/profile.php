<!-- ==================== PROFILE SECTION ==================== -->
<div id="profile" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Admin Profile</h2>
        <p class="welcome-subtitle">Manage your administrator profile</p>
    </div>

    <div class="card" style="max-width: 600px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="admin-avatar" style="width: 100px; height: 100px; font-size: 40px; margin: 0 auto 16px;">AD</div>
            <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Administrator</div>
            <div style="font-size: 14px; color: var(--text-secondary);">System Administrator</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-top: 1px solid var(--border-color); padding-top: 24px;">
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" class="form-input" value="Admin" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-input" value="User" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" value="<?php echo htmlspecialchars($admin_email); ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" class="form-input" value="+1 (555) 123-4567" disabled>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button class="btn btn-primary">Edit Profile</button>
            <button class="btn btn-secondary">Change Password</button>
        </div>
    </div>
</div>
