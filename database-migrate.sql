-- Run this if you already imported an OLD database.sql (missing tables/columns)
USE seo_system;

-- Allow multiple posts per platform (same platform can be posted multiple times with unique content)
-- This removes the old single-post constraint so each run creates a new backlink row
-- (If error "Can't DROP", index doesn't exist — skip this line)
ALTER TABLE backlinks ADD COLUMN post_variation TINYINT DEFAULT 1 COMMENT '1=first post, 2=second, etc.';

-- Store the post title used for each backlink — used to avoid repeating titles
-- (If error "Duplicate column", already exists — skip this line)
ALTER TABLE backlinks ADD COLUMN post_title VARCHAR(500) DEFAULT NULL;

-- If error "Duplicate column", column already exists — skip this line
ALTER TABLE projects ADD COLUMN post_image VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS project_meta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL UNIQUE,
    meta_title VARCHAR(100) NOT NULL,
    meta_description VARCHAR(200) NOT NULL,
    meta_keywords TEXT,
    og_title VARCHAR(120),
    og_description VARCHAR(220),
    og_image VARCHAR(500),
    h1_suggestion VARCHAR(255),
    schema_json LONGTEXT,
    full_head_html LONGTEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS social_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    username VARCHAR(255) DEFAULT '',
    password TEXT,
    api_key TEXT,
    api_secret TEXT,
    refresh_token TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_platform (user_id, platform),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
