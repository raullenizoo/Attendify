<!-- ==================== PROFILE SECTION ==================== -->
<div id="profile" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">My Profile</h2>
        <p class="welcome-subtitle">Manage your personal information</p>
    </div>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-avatar-large"><?php echo strtoupper(substr($user['first_name'],0,1) . substr($user['last_name'],0,1)); ?></div>
            <div class="profile-name"><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></div>
            <div class="profile-role">Student</div>

            <div class="profile-info-grid">
                <div class="profile-info-item">
                    <span class="profile-info-label">Student ID</span>
                    <span class="profile-info-value"><?php echo htmlspecialchars($user['id_number']); ?></span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Email</span>
                    <span class="profile-info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Class</span>
                    <span class="profile-info-value"><?php echo htmlspecialchars($user['year_level'] . " - " . $user['section']); ?></span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Enrollment Date</span>
                    <span class="profile-info-value"><?php echo date("F d, Y", strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
