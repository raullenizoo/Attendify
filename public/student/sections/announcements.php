<!-- ==================== ANNOUNCEMENTS SECTION ==================== -->
<div id="announcements" class="section">
    <div class="welcome-section">
        <h2 class="welcome-title">Announcements</h2>
        <p class="welcome-subtitle">Stay updated with the latest news and announcements</p>
    </div>

    <div class="announcements-grid">
        <?php if ($announcements->num_rows === 0): ?>
            <div class="announcement-card">
                <div class="announcement-content" style="text-align:center; padding: 40px;">
                    No announcements yet. Check back later.
                </div>
            </div>
        <?php else: ?>
            <?php while($row = $announcements->fetch_assoc()): ?>
            <div class="announcement-card">
                <div class="announcement-header">
                    <div>
                        <div class="announcement-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="announcement-date"><?php echo date("F d, Y", strtotime($row['created_at'])); ?></div>
                    </div>
                    <div class="announcement-badge">
                        <?php
                            $priority = $row['priority'] ?? 'normal';
                            $badgeClass = 'badge-info';
                            if ($priority === 'important') $badgeClass = 'badge-warning';
                            if ($priority === 'urgent') $badgeClass = 'badge-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($priority); ?></span>
                    </div>
                </div>
                <div class="announcement-teacher">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($row['author_name']); ?>
                    <?php if ($row['class_section_id']): ?>
                        &middot; <strong><?php echo htmlspecialchars($row['subject_name'] . ' - ' . $row['section_name']); ?></strong>
                    <?php else: ?>
                        &middot; <strong>School-wide</strong>
                    <?php endif; ?>
                </div>
                <div class="announcement-content">
                    <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
