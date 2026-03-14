<!-- ==================== PROFILE SECTION ==================== -->
<div id="profile" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">My Profile</h2>
        <p class="welcome-subtitle">Manage your profile information</p>
    </div>

    <div class="card" style="max-width: 600px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="teacher-avatar" style="width: 100px; height: 100px; font-size: 40px; margin: 0 auto 16px;"><?php 
echo strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1)); 
?></div>
            <div style="font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></div>
            <div style="font-size: 14px; color: var(--text-secondary);">Mathematics Teacher</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-top: 1px solid var(--border-color); padding-top: 24px;">
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" class="form-input" value="<?php echo $user['first_name']; ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-input" value="<?php echo $user['last_name']; ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" value="<?php echo $user['email']; ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Department</label>
                <input type="text" class="form-input" value="<?php echo $user['department']; ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">ID Number</label>
                <input type="text" class="form-input" value="<?php echo $user['id_number']; ?>" disabled>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button class="btn btn-primary">Edit Profile</button>
            <button class="btn btn-secondary">Change Password</button>
        </div>
    </div>
</div>
