<?php
require_once __DIR__ . '/partials.php';
require_admin();
$catalog = JOB_CATALOG;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $department = $_POST['department'] ?? '';
    $title = $_POST['title'] ?? '';
    $eligibleEducation = job_requirements($department, $title);
    $experienceWeight = filter_var($_POST['experience_weight'] ?? 0, FILTER_VALIDATE_FLOAT);
    $skillsWeight = filter_var($_POST['skills_weight'] ?? 0, FILTER_VALIDATE_FLOAT);
    $assessmentWeight = filter_var($_POST['assessment_weight'] ?? 0, FILTER_VALIDATE_FLOAT);
    $minimumExperience = filter_var($_POST['minimum_experience'] ?? 0, FILTER_VALIDATE_FLOAT);
    $weightTotal = $experienceWeight + $skillsWeight + $assessmentWeight;
    if (!$eligibleEducation || $experienceWeight === false || $skillsWeight === false || $assessmentWeight === false || $minimumExperience === false || $minimumExperience < 0 || $experienceWeight < 0 || $skillsWeight < 0 || $assessmentWeight < 0 || $weightTotal <= 0 || abs($weightTotal - 100) > 0.0001) {
        exit('Please provide a valid job and scoring model. The three weighting values must total 100%.');
    }
    $stmt = db()->prepare('INSERT INTO jobs (title,department,eligible_education,employment_type,description,minimum_experience,required_skills,experience_weight,skills_weight,assessment_weight) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$title, $department, implode('|', $eligibleEducation), $_POST['employment_type'], trim($_POST['description']), $minimumExperience, trim($_POST['required_skills']), $experienceWeight, $skillsWeight, $assessmentWeight]);
    redirect('jobs.php');
}
$jobs = db()->query('SELECT j.*, COUNT(a.id) applicant_count FROM jobs j LEFT JOIN applicants a ON a.job_id=j.id GROUP BY j.id ORDER BY j.created_at DESC')->fetchAll();
$stats = db()->query("SELECT COUNT(*) jobs, (SELECT COUNT(*) FROM applicants) applicants, (SELECT COUNT(*) FROM applicants WHERE status = 'Qualified') qualified FROM jobs")->fetch();
page_start('Manage positions', true);
topbar('jobs');
?>
<main class="wrap"><div class="split"><div class="hero"><div class="eyebrow">HR WORKSPACE</div><h1>Admin dashboard.</h1><p>Create roles, monitor applications, and review candidates using a consistent scoring model.</p></div><a class="button" href="#create">Create job</a></div>
<div class="grid" style="margin:30px 0"><div class="card stat"><small>Open positions</small><strong><?= e($stats['jobs']) ?></strong></div><div class="card stat"><small>Total applicants</small><strong><?= e($stats['applicants']) ?></strong></div><div class="card stat"><small>Qualified candidates</small><strong><?= e($stats['qualified']) ?></strong></div></div>
<section class="card" id="create"><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="grid two"><div class="field"><label>Job category</label><select name="department" id="department" required onchange="updateRoles()"><?php foreach ($catalog as $department => $roles): ?><option value="<?= e($department) ?>"><?= e($department) ?></option><?php endforeach; ?></select></div><div class="field"><label>Job title</label><select name="title" id="title" required onchange="updateEducation()"></select></div></div><div class="field"><label>Eligible education</label><input id="education_preview" readonly></div><div class="grid two"><div class="field"><label>Minimum experience</label><input name="minimum_experience" type="number" min="0" step="1" value="0"></div><div class="field"><label>Employment type</label><select name="employment_type"><option>Full-time</option><option>Part-time</option><option>Contract</option></select></div></div><div class="field"><label>Description</label><textarea name="description" required></textarea></div><div class="field"><label>Required skills (comma-separated)</label><input name="required_skills" id="required_skills" placeholder="PHP, MySQL, Git"></div><div class="weight-block"><div class="grid"><div class="field"><label>Experience weight</label><input name="experience_weight" id="experience_weight" type="number" min="0" max="100" step="1" value="40"></div><div class="field"><label>Skills weight</label><input name="skills_weight" id="skills_weight" type="number" min="0" max="100" step="1" value="35"></div><div class="field"><label>Assessment weight</label><input name="assessment_weight" id="assessment_weight" type="number" min="0" max="100" step="1" value="25"></div></div><div class="weight-total" id="weight_total">Total: 100%</div></div><button class="button" type="submit">CREATE POSITION</button></form></section>
<section class="card" style="margin-top:28px"><h2>Available positions</h2><div class="table-wrap"><table class="table"><thead><tr><th>Job title</th><th>Department</th><th>Type</th><th>Applicants</th><th></th></tr></thead><tbody><?php foreach($jobs as $job): ?><tr><td><strong><?= e($job['title']) ?></strong></td><td><?= e($job['department']) ?></td><td><?= e($job['employment_type']) ?></td><td><?= e($job['applicant_count']) ?></td><td><a href="leaderboard.php?job=<?= e($job['id']) ?>">View applicants</a></td></tr><?php endforeach; ?></tbody></table></div></section></main>
<?php page_end(); ?>
<script>
const catalog = <?= json_encode($catalog, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const skillExamples = {
    'Information Technology': 'PHP, MySQL, Git',
    'Engineering': 'AutoCAD, technical drawing, project management',
    'Healthcare': 'Patient care, clinical assessment, medical records',
    'Business': 'Business planning, market analysis, financial forecasting',
    'Food Service': 'Food safety, customer service, order accuracy'
};
function updateRoles() {
    const department = document.getElementById('department').value;
    const title = document.getElementById('title');
    title.innerHTML = Object.keys(catalog[department]).map(role => `<option>${role}</option>`).join('');
    document.getElementById('required_skills').placeholder = skillExamples[department] || '';
    updateEducation();
}
function updateEducation() {
    const department = document.getElementById('department').value;
    const title = document.getElementById('title').value;
    document.getElementById('education_preview').value = catalog[department][title].join(', ');
}
function updateWeightTotal() {
    const values = ['experience_weight', 'skills_weight', 'assessment_weight']
        .map(id => Number(document.getElementById(id).value || 0))
        .filter(value => !Number.isNaN(value));
    const total = values.reduce((sum, value) => sum + value, 0);
    const weightTotal = document.getElementById('weight_total');
    weightTotal.textContent = `Total: ${total}%`;
    weightTotal.style.color = total === 100 ? 'var(--green)' : '#b65b31';
}
['experience_weight', 'skills_weight', 'assessment_weight'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateWeightTotal);
});
updateRoles();
updateWeightTotal();
</script>
