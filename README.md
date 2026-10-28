# Talent Desk - Applicant Tracking & Scoring System

A full-stack employee management and applicant tracking system (ATS) developed for Software Engineering 2. This platform allows HR administrators to create job listings, manage applications, and automatically rank candidates using a dynamic, weighted scoring algorithm.

## Features
* **Custom Weighted Scoring:** Evaluates applicants mathematically based on configurable weights for experience, technical assessments, and core skills.
* **Dynamic Role Requirements:** Job prerequisites and eligible education backgrounds dynamically populate based on the selected department via Vanilla JavaScript.
* **HR Dashboard:** Secure administrator portal to update candidate statuses (Pending, Under Review, Qualified, Hired) and monitor leaderboard metrics.
* **Security First:** Implements manual CSRF token verification across all state-changing POST requests, strict PDO prepared statements to prevent SQL injection, and bcrypt password hashing.

## Tech Stack
* **Frontend:** HTML5, CSS3, Vanilla JavaScript
* **Backend:** PHP 8+ (Procedural)
* **Database:** MySQL / MariaDB (via PDO)
* **Environment:** Designed for local XAMPP deployment or standard cPanel/InfinityFree hosting.

## Installation
1. Clone this repository to your local server directory (e.g., `htdocs` for XAMPP).
2. Create a new MySQL database named `softeng2`.
3. Import the `schema.sql` file into your database to generate the tables and default admin account.
4. Access the application via your web browser. 

**Default Admin Credentials:**
* **Email:** admin@talentdesk.test
* **Password:** password
