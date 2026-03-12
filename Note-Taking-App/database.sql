-- This file exists to quickly create a DB to use with the app using SQL Queries

-- Create database
CREATE DATABASE IF NOT EXISTS notes_app;
USE notes_app;

-- Create notes table
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_updated (updated_at),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
-- INSERT INTO notes (title, content) VALUES 
-- ('Welcome', 'This is your first note!'),
-- ('Plan for today', 'Buy groceries, finish project, exercise');

