-- SEO 80/20 System Database Schema
CREATE DATABASE IF NOT EXISTS seo_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE seo_system;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    website_url VARCHAR(500) NOT NULL,
    target_keyword VARCHAR(255) NOT NULL,
    target_site VARCHAR(500),
    post_image VARCHAR(255) DEFAULT NULL,
    seo_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE social_accounts (
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

CREATE TABLE seo_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    rank INT DEFAULT 0,
    seo_score INT DEFAULT 0,
    backlinks_count INT DEFAULT 0,
    issues_found TEXT,
    report_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE backlinks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    backlink_url VARCHAR(1000) NOT NULL,
    platform VARCHAR(100),
    da_score INT DEFAULT 0,
    status ENUM('pending','created','verified') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    search_volume INT DEFAULT 0,
    difficulty INT DEFAULT 0,
    selected TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE content_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(500),
    article LONGTEXT,
    status ENUM('draft','approved','published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE onpage_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    issue_type VARCHAR(100),
    issue_detail TEXT,
    fix_code TEXT,
    status ENUM('open','approved','fixed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE project_meta (
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

CREATE TABLE rank_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    keyword VARCHAR(255),
    rank_position INT,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
