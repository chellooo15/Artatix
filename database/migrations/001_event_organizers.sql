-- Artatix: multi-organizer per event + invite tokens
-- Apply: mysql -h 127.0.0.1 -P 3306 -u artatix_user -p artatix < 001_event_organizers.sql

ALTER TABLE users MODIFY COLUMN role ENUM('user','organizer','admin') NOT NULL DEFAULT 'user';

CREATE TABLE IF NOT EXISTS event_organizers (
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    can_edit_info TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, user_id),
    KEY idx_event_organizers_user (user_id),
    CONSTRAINT fk_event_organizers_event FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE,
    CONSTRAINT fk_event_organizers_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_invites (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    can_edit_info TINYINT(1) NOT NULL DEFAULT 0,
    max_uses INT NOT NULL DEFAULT 5,
    used_count INT NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    created_by INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_event_invites_event (event_id),
    KEY idx_event_invites_token (token),
    CONSTRAINT fk_event_invites_event FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE,
    CONSTRAINT fk_event_invites_creator FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
