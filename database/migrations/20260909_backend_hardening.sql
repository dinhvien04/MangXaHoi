-- Run once on an EXISTING Handbook database before using the hardened backend.
-- Back up the database first. MySQL/MariaDB DDL statements may commit implicitly, so this migration is intentionally not described as atomic.

-- Remove duplicate relationship rows before adding UNIQUE constraints.
DELETE a FROM likes a JOIN likes b ON a.post_id=b.post_id AND a.user_id=b.user_id AND a.id>b.id;
DELETE a FROM follow_list a JOIN follow_list b ON a.follower_id=b.follower_id AND a.user_id=b.user_id AND a.id>b.id;
DELETE a FROM block_list a JOIN block_list b ON a.user_id=b.user_id AND a.blocked_user_id=b.blocked_user_id AND a.id>b.id;

-- Remove orphan rows that would prevent foreign keys from being added.
DELETE l FROM likes l LEFT JOIN posts p ON p.id=l.post_id LEFT JOIN users u ON u.id=l.user_id WHERE p.id IS NULL OR u.id IS NULL;
DELETE c FROM comments c LEFT JOIN posts p ON p.id=c.post_id LEFT JOIN users u ON u.id=c.user_id WHERE p.id IS NULL OR u.id IS NULL;
DELETE f FROM follow_list f LEFT JOIN users a ON a.id=f.follower_id LEFT JOIN users b ON b.id=f.user_id WHERE a.id IS NULL OR b.id IS NULL;
DELETE b FROM block_list b LEFT JOIN users a ON a.id=b.user_id LEFT JOIN users c ON c.id=b.blocked_user_id WHERE a.id IS NULL OR c.id IS NULL;
DELETE m FROM messages m LEFT JOIN users a ON a.id=m.from_user_id LEFT JOIN users b ON b.id=m.to_user_id WHERE a.id IS NULL OR b.id IS NULL;
DELETE n FROM notifications n LEFT JOIN users a ON a.id=n.from_user_id LEFT JOIN users b ON b.id=n.to_user_id WHERE a.id IS NULL OR b.id IS NULL;
DELETE p FROM posts p LEFT JOIN users u ON u.id=p.user_id WHERE u.id IS NULL;

-- Preserve old reported items for admin review; make normal historical posts visible.
UPDATE posts SET is_approved=1 WHERE COALESCE(is_reported,0)=0;
UPDATE posts SET is_reported=0 WHERE is_reported IS NULL;
UPDATE posts SET is_approved=1 WHERE is_approved IS NULL AND is_reported=0;

ALTER TABLE users
  MODIFY first_name VARCHAR(100) NOT NULL,
  MODIFY last_name VARCHAR(100) NOT NULL,
  MODIFY username VARCHAR(30) NOT NULL,
  MODIFY password VARCHAR(255) NOT NULL,
  MODIFY profile_pic VARCHAR(255) NOT NULL DEFAULT 'default_profile.jpg',
  MODIFY ac_status TINYINT NOT NULL DEFAULT 0,
  ADD UNIQUE KEY uq_users_email (email),
  ADD UNIQUE KEY uq_users_username (username),
  ADD KEY idx_users_status_role (ac_status, role);

ALTER TABLE posts
  MODIFY post_img VARCHAR(255) NOT NULL,
  MODIFY is_reported TINYINT(1) NOT NULL DEFAULT 0,
  MODIFY is_approved TINYINT(1) NOT NULL DEFAULT 1,
  ADD KEY idx_posts_user_created (user_id, id),
  ADD KEY idx_posts_visibility (is_approved, is_reported, id),
  ADD CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE block_list
  ADD UNIQUE KEY uq_block_pair (user_id, blocked_user_id),
  ADD KEY idx_block_target (blocked_user_id),
  ADD CONSTRAINT fk_block_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_block_target FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE follow_list
  ADD UNIQUE KEY uq_follow_pair (follower_id, user_id),
  ADD KEY idx_follow_user (user_id),
  ADD CONSTRAINT fk_follow_follower FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_follow_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE comments
  ADD KEY idx_comments_post_id (post_id, id),
  ADD KEY idx_comments_user_id (user_id),
  ADD CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE likes
  ADD UNIQUE KEY uq_like_pair (post_id, user_id),
  ADD KEY idx_likes_user_id (user_id),
  ADD CONSTRAINT fk_likes_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE messages
  MODIFY read_status TINYINT(1) NOT NULL DEFAULT 0,
  ADD KEY idx_messages_from_to_id (from_user_id, to_user_id, id),
  ADD KEY idx_messages_to_read (to_user_id, read_status, id),
  ADD CONSTRAINT fk_messages_from FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_messages_to FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE;

UPDATE notifications SET post_id=NULL WHERE post_id='' OR post_id='0';
ALTER TABLE notifications MODIFY post_id INT NULL, MODIFY read_status TINYINT(1) NOT NULL DEFAULT 0, MODIFY message VARCHAR(500) NOT NULL;
DELETE n FROM notifications n LEFT JOIN posts p ON p.id=n.post_id WHERE n.post_id IS NOT NULL AND p.id IS NULL;
ALTER TABLE notifications
  ADD KEY idx_notifications_to_read (to_user_id, read_status, id),
  ADD KEY idx_notifications_from (from_user_id),
  ADD KEY idx_notifications_post (post_id),
  ADD CONSTRAINT fk_notifications_to FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_notifications_from FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_notifications_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE;

CREATE TABLE rate_limits (
  rate_key CHAR(64) NOT NULL,
  attempts INT NOT NULL DEFAULT 0,
  window_started_at DATETIME NOT NULL,
  PRIMARY KEY (rate_key),
  KEY idx_rate_window (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
