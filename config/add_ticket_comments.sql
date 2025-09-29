-- Add ticket comments table
-- Chinhoyi University of Technology - Campus IT Support System

USE campus_it_support;

-- Create ticket comments table
CREATE TABLE IF NOT EXISTS ticket_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Add remember_token column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token VARCHAR(255) NULL;
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_remember_token (remember_token);

-- Add verification_token column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_token VARCHAR(255) NULL;
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_verification_token (verification_token);

-- Add reset_token columns if they don't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME NULL;
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_reset_token (reset_token);
