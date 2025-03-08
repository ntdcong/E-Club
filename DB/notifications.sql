-- Notifications table for club announcements
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    club_id INT,
    sender_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notification recipients table to track read status
CREATE TABLE notification_recipients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT,
    user_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);