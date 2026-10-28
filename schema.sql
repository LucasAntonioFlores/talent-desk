CREATE DATABASE IF NOT EXISTS softeng2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE softeng2;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    department VARCHAR(100) NOT NULL DEFAULT 'IT',
    eligible_education TEXT NOT NULL,
    employment_type VARCHAR(50) NOT NULL DEFAULT 'Full-time',
    description TEXT NOT NULL,
    minimum_experience DECIMAL(5,1) NOT NULL DEFAULT 0,
    required_skills VARCHAR(500) NOT NULL DEFAULT '',
    experience_weight DECIMAL(5,2) NOT NULL DEFAULT 40,
    skills_weight DECIMAL(5,2) NOT NULL DEFAULT 35,
    assessment_weight DECIMAL(5,2) NOT NULL DEFAULT 25,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS applicants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    experience_years DECIMAL(5,1) NOT NULL DEFAULT 0,
    education VARCHAR(100) NOT NULL,
    assessment_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    skills_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    status ENUM('Pending', 'Under Review', 'Qualified', 'Rejected', 'Hired') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_applicant_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);

INSERT INTO admins (email, password_hash)
SELECT 'admin@talentdesk.test', '$2y$10$/ASJfFrS4/N5CbXd5hBiGut425k33LXsHR5OJPCztv4UwGpU3RAcS'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE email = 'admin@talentdesk.test');

INSERT INTO jobs (title, department, eligible_education, employment_type, description, minimum_experience, required_skills)
SELECT 'Web Developer', 'Information Technology', 'Bachelor of Science in Computer Science|Bachelor of Science in Information Technology|Bachelor of Science in Web Development', 'Full-time', 'Build and maintain reliable web experiences for our team.', 0, 'PHP, MySQL, HTML, CSS'
WHERE NOT EXISTS (SELECT 1 FROM jobs WHERE title = 'Web Developer');
