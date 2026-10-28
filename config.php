<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'softeng2';
const DB_USER = 'root';
const DB_PASS = '';

const JOB_CATALOG = [
    'Information Technology' => [
        'Software Developer' => ['Bachelor of Science in Computer Science', 'Bachelor of Science in Information Technology', 'Bachelor of Science in Software Engineering'],
        'Web Developer' => ['Bachelor of Science in Computer Science', 'Bachelor of Science in Information Technology', 'Bachelor of Science in Web Development'],
        'Database Administrator' => ['Bachelor of Science in Computer Science', 'Bachelor of Science in Information Technology'],
    ],
    'Engineering' => [
        'Civil Engineer' => ['Bachelor of Science in Civil Engineering'],
        'Mechanical Engineer' => ['Bachelor of Science in Mechanical Engineering'],
        'Electrical Engineer' => ['Bachelor of Science in Electrical Engineering'],
    ],
    'Business' => [
        'Accountant' => ['Bachelor of Science in Accountancy'],
        'Marketing Specialist' => ['Bachelor of Science in Marketing', 'Bachelor of Science in Business Administration'],
        'Human Resources Officer' => ['Bachelor of Science in Psychology', 'Bachelor of Science in Human Resource Management'],
    ],
    'Healthcare' => [
        'Registered Nurse' => ['Bachelor of Science in Nursing'],
        'Medical Technologist' => ['Bachelor of Science in Medical Technology'],
    ],
    'Food Service' => [
        'Fast Food Crew Member' => ['High School Diploma'],
        'Cashier' => ['High School Diploma'],
        'Kitchen Staff' => ['High School Diploma'],
        'Shift Supervisor' => ['High School Diploma', 'Bachelor of Science in Hospitality Management', 'Bachelor of Science in Business Administration'],
    ],
];

function job_requirements(string $department, string $title): array
{
    return JOB_CATALOG[$department][$title] ?? [];
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Database connection failed. Please check your configuration.');
    }
    return $pdo;
}

function start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        session_start();
    }
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    start_session();
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);
        exit('Invalid form submission. Please go back and try again.');
    }
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function require_admin(): void
{
    start_session();
    if (empty($_SESSION['admin_id'])) {
        redirect('login.php');
    }
}

function score_applicant(array $applicant, array $job): float
{
    $experienceWeight = (float) ($job['experience_weight'] ?? 40);
    $skillsWeight = (float) ($job['skills_weight'] ?? 35);
    $assessmentWeight = (float) ($job['assessment_weight'] ?? 25);

    $minimumExperience = (float) ($job['minimum_experience'] ?? 0);
    $experience = $minimumExperience > 0
        ? min(100, ((float) $applicant['experience_years'] / $minimumExperience * 100))
        : 100;
    $skills = min(100, max(0, (float) $applicant['skills_score']));
    $assessment = min(100, max(0, (float) $applicant['assessment_score']));

    $totalWeight = max(1, $experienceWeight + $skillsWeight + $assessmentWeight);
    return round(($experience * $experienceWeight + $skills * $skillsWeight + $assessment * $assessmentWeight) / $totalWeight, 2);
}
