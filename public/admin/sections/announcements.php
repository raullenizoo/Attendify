<!-- ==================== ANNOUNCEMENTS SECTION ==================== -->
<div id="announcements" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">System Announcements</h2>
        <p class="welcome-subtitle">Create and manage system-wide announcements</p>
    </div>

    <div class="announcements-container">
        <div>
            <div class="section-title">
                <i class="fas fa-list"></i>
                All Announcements
            </div>

            <div class="announcements-list">
                <div class="announcement-item">
                    <div class="announcement-title">System Maintenance Scheduled</div>
                    <div class="announcement-meta">
                        <span>March 15, 2026</span>
                        <span>Admin</span>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="section-title">
                <i class="fas fa-pen-fancy"></i>
                Post New Announcement
            </div>

            <div class="post-announcement">
                <form class="post-form">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-input" placeholder="Enter announcement title">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea class="form-textarea" placeholder="Enter your announcement message"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Recipient</label>
                        <select class="form-select">
                            <option>All Users</option>
                            <option>Teachers Only</option>
                            <option>Students Only</option>
                            <option>Specific Class</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary">Post Announcement</button>
                        <button type="reset" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
