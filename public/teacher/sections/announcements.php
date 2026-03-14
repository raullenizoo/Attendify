<!-- ==================== ANNOUNCEMENTS SECTION ==================== -->
<div id="announcements" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Announcements</h2>
        <p class="welcome-subtitle">Create and manage announcements for your students</p>
    </div>

    <div class="announcements-container">
        <div>
            <div class="section-title">
                <i class="fas fa-list"></i>
                Recent Announcements
            </div>

            <div class="announcements-list">
                <?php if ($announcements->num_rows > 0): ?>
                    <?php while ($announcement = $announcements->fetch_assoc()): ?>
                    <div class="announcement-item">
                        <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                        <div class="announcement-date"><?php echo date("F d, Y - h:i A", strtotime($announcement['created_at'])); ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                <p>No announcements yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="section-title">
                <i class="fas fa-pen-fancy"></i>
                Post New Announcement
            </div>

            <div class="post-announcement">
                <form id="announcement-form" class="post-form" onsubmit="postAnnouncement(event)">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input id="announcement-title" type="text" class="form-input" placeholder="Enter announcement title" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea id="announcement-content" class="form-textarea" placeholder="Enter your announcement message" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Class</label>
                        <select id="announcement-class" class="form-input">
                            <option value="">All Students (School-wide)</option>
                            <?php foreach ($all_classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['subject_name']) . ' - ' . htmlspecialchars($class['section_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select id="announcement-priority" class="form-input">
                            <option value="normal" selected>Normal</option>
                            <option value="important">Important</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary">Post Announcement</button>
                        <button type="reset" class="btn btn-secondary" id="announcement-clear">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
