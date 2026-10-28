<?php
require_once __DIR__ . '/partials.php';
$jobs = db()->query('SELECT * FROM jobs ORDER BY title')->fetchAll();
$selected = (int) ($_GET['job'] ?? $_POST['job_id'] ?? ($jobs[0]['id'] ?? 0));
$selectedJob = null;
foreach ($jobs as $job) {
    if ((int) $job['id'] === $selected) {
        $selectedJob = $job;
        break;
    }
}
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $experienceYears = filter_var($_POST['experience_years'] ?? 0, FILTER_VALIDATE_FLOAT);
    $assessmentScore = filter_var($_POST['assessment_score'] ?? 0, FILTER_VALIDATE_FLOAT);
    $skillsScore = filter_var($_POST['skills_score'] ?? 0, FILTER_VALIDATE_FLOAT);
    $education = (string) ($_POST['education'] ?? '');
    $jobStmt = db()->prepare('SELECT * FROM jobs WHERE id = ?');
    $jobStmt->execute([$selected]);
    $postedJob = $jobStmt->fetch();
    if (!$postedJob) {
        $error = 'Please select a valid position.';
    } elseif ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid name and email address.';
    } elseif ($experienceYears === false || $experienceYears < 0 || $assessmentScore === false || $assessmentScore < 0 || $assessmentScore > 100 || $skillsScore === false || $skillsScore < 0 || $skillsScore > 100) {
        $error = 'Please enter valid scores and experience values.';
    } elseif (!in_array($education, explode('|', $postedJob['eligible_education']), true)) {
        $error = 'This education background does not match the selected position.';
    } else {
        $stmt = db()->prepare('INSERT INTO applicants (job_id,full_name,email,experience_years,education,assessment_score,skills_score) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$selected, $fullName, $email, $experienceYears, $education, $assessmentScore, $skillsScore]);
        $message = 'Your application has been received. Thank you!';
    }
}
page_start('Applicant portal');
topbar('apply');
?>
<main class="wrap">
    <section class="card application-card">
        <div class="eyebrow">CANDIDATE INTAKE</div>
        <h1>Make your next move count.</h1>
        <p class="intro">
            Share your experience, strengths, and technical background.
            Your profile will be scored against the role requirements.
        </p>

        <?php if ($message): ?>
            <div class="notice" role="status"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="notice" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-section">
                <h2>Role information</h2>
                <div class="field">
                    <label for="job_id">Position *</label>
                    <select name="job_id" id="job_id" required onchange="updateJobDetails()">
                        <?php foreach ($jobs as $job): ?>
                            <option value="<?= e($job['id']) ?>" <?= $selected === (int) $job['id'] ? 'selected' : '' ?>>
                                <?= e($job['title']) ?> (<?= e($job['department']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="job-details" id="job-details" aria-live="polite">
                    <h3 id="job-title"></h3>
                    <p id="job-description"></p>
                    <div class="job-meta">
                        <span id="job-department"></span>
                        <span id="job-type"></span>
                        <span id="job-experience"></span>
                        <span id="job-skills"></span>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Your details</h2>
                <div class="grid two">
                    <div class="field">
                        <label for="full_name">Full name *</label>
                        <input name="full_name" id="full_name" required>
                    </div>

                    <div class="field">
                        <label for="email">Email address *</label>
                        <input name="email" id="email" type="email" required>
                    </div>

                    <div class="field">
                        <label for="experience_years">Relevant experience (years)</label>
                        <input name="experience_years" type="number" min="0" step="0.1" value="0">
                    </div>

                    <div class="field">
                        <label for="education">Highest education</label>
                        <select name="education" id="education" required></select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Assessment scores</h2>
                <div class="grid two">
                    <div class="field">
                        <label for="assessment_score">Technical assessment (0 - 100)</label>
                        <input name="assessment_score" id="assessment_score" type="number" min="0" max="100" value="0">
                    </div>

                    <div class="field">
                        <label for="skills_score">Core skills (0 - 100)</label>
                        <input name="skills_score" id="skills_score" type="number" min="0" max="100" value="0">
                    </div>
                </div>
            </div>

            <button class="button" type="submit">SUBMIT APPLICATION</button>
        </form>
    </section>
</main>
<script>
const jobs = <?= json_encode(array_reduce($jobs, function (array $result, array $job): array {
    $result[(string) $job['id']] = [
        'title' => $job['title'],
        'department' => $job['department'],
        'employment_type' => $job['employment_type'],
        'description' => $job['description'],
        'minimum_experience' => $job['minimum_experience'],
        'required_skills' => $job['required_skills'],
        'eligible_education' => $job['eligible_education'],
    ];
    return $result;
}, []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const jobDetails = document.getElementById('job-details');
function updateEducation() {
    const education = document.getElementById('education');
    const job = jobs[document.getElementById('job_id').value];
    const options = (job ? job.eligible_education : '').split('|').filter(Boolean);
    education.replaceChildren(...options.map(degree => {
        const option = document.createElement('option');
        option.textContent = degree;
        return option;
    }));
}
function updateJobDetails() {
    const job = jobs[document.getElementById('job_id').value];
    if (!job) {
        jobDetails.hidden = true;
        updateEducation();
        return;
    }
    jobDetails.hidden = false;
    document.getElementById('job-title').textContent = job.title;
    document.getElementById('job-description').textContent = job.description;
    document.getElementById('job-department').textContent = `Department: ${job.department}`;
    document.getElementById('job-type').textContent = `Type: ${job.employment_type}`;
    document.getElementById('job-experience').textContent = `Minimum experience: ${job.minimum_experience} year(s)`;
    document.getElementById('job-skills').textContent = `Required skills: ${job.required_skills || 'Not specified'}`;
    updateEducation();
}
updateJobDetails();
</script>
<?php page_end(); ?>
