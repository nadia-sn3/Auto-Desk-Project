<div class="notification-popup">
    <div class="notification-header">
        <h3>Notifications <?= $unreadCount > 0 ? "<span class='badge'>$unreadCount</span>" : '' ?></h3>
        <button class="mark-all-read">Mark all as read</button>
    </div>
    
    <div class="notification-list">
        <?php if (empty($notifications)): ?>
            <div class="notification-empty">
                <p>No new notifications</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item" data-id="<?= $notification['notification_id'] ?>">
                    <div class="notification-badge unread"></div>
                    <div class="notification-content">
                        <h4><?= htmlspecialchars($notification['title']) ?></h4>
                        <p><?= htmlspecialchars($notification['message']) ?></p>
                        <small><?= date('M j, g:i a', strtotime($notification['created_at'])) ?></small>
                    </div>
                    <?php if ($notification['link']): ?>
                        <a href="<?= $notification['link'] ?>" class="notification-link"></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="notification-footer">
        <a href="notifications.php">View all notifications</a>
    </div>
</div>