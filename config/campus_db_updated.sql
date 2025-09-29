-- Updated Database Schema
-- Praisetech - Campus IT Support System

-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS campus_db;

-- Use the database
USE campus_db;

-- Create the users table with enhanced security
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  token VARCHAR(64) DEFAULT NULL,
  token_expires TIMESTAMP NULL,
  last_login TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_token (token),
  INDEX idx_token_expires (token_expires)
);

-- Create support tickets table (for future use)
CREATE TABLE support_tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
  priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create activity logs table (for future use)
CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(255) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample users (remove in production)
INSERT INTO users (name, email) VALUES
('John Doe', 'john.doe@campus.edu'),
('Jane Smith', 'jane.smith@campus.edu'),
('Admin User', 'admin@campus.edu');

-- Create indexes for better performance
CREATE INDEX idx_tickets_user ON support_tickets(user_id);
CREATE INDEX idx_tickets_status ON support_tickets(status);
CREATE INDEX idx_logs_user ON activity_logs(user_id);
CREATE INDEX idx_logs_created ON activity_logs(created_at);
