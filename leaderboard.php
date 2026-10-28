<?php
require_once __DIR__ . '/partials.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    verify_csrf();
    $status = $_POST['status'] ?? '';
    $validStatuses = ['Pending', 'Under Review', 'Qualified', 'Rejected', 'Hired'];
    if (in_array($status, $validStatuses, true)) {
        $stmt = db()->prepare('UPDATE applicants SET status = ? WHERE id = ?');
        $stmt->execute([$status, (int) ($_POST['applicant_id'] ?? 0)]);
    }
    redirect('leaderboard.php?job=' . (int) ($_POST['job_id'] ?? 0));
}
$jobs = db()->query('SELECT * FROM jobs ORDER BY title')->fetchAll();
$jobId = (int)($_GET['job'] ?? ($jobs[0]['id'] ?? 0));
$job = null; foreach($jobs as $candidateJob) if ((int)$candidateJob['id'] === $jobId) $job = $candidateJob;
$rows = [];
if ($job) {
    $stmt = db()->prepare('SELECT * FROM applicants WHERE job_id=? ORDER BY created_at DESC');
    $stmt->execute([$jobId]);
    foreach ($stmt as $applicant) { $applicant['score'] = score_applicant($applicant, $job); $rows[] = $applicant; }
    usort($rows, fn($a,$b) => $b['score'] <=> $a['score']);
}
$qualified = count(array_filter($rows, fn($r) => $r['score'] >= 70));
$average = $rows ? round(array_sum(array_column($rows, 'score')) / count($rows), 2) : 0;
page_start('Applicant leaderboard');
topbar();
?>
<main class="wrap"><div class="split hero"><div><div class="eyebrow"><?= !empty($_SESSION['admin_id']) ? 'HR REVIEW' : 'SHORTLIST OVERVIEW' ?></div><h1>Applicant leaderboard</h1><p><?= !empty($_SESSION['admin_id']) ? 'Review candidates, update their status, and prioritize the strongest applicants.' : 'A clear view of candidate rankings for this role.' ?></p></div><span class="button"><?= count($rows) ?> candidate<?= count($rows) === 1 ? '' : 's' ?></span></div>
<div class="grid" style="margin:30px 0"><div class="card stat"><small>Total applicants</small><strong><?= count($rows) ?></strong></div><div class="card stat"><small>Qualified</small><strong><?= $qualified ?></strong></div><div class="card stat"><small>Average score</small><strong><?= e($average) ?></strong></div></div>
<section class="card"><form method="get" class="grid two"><div class="field"><label>Position</label><select name="job" onchange="this.form.submit()"><?php foreach($jobs as $item): ?><option value="<?= e($item['id']) ?>" <?= $jobId === (int)$item['id'] ? 'selected' : '' ?>><?= e($item['title']) ?></option><?php endforeach; ?></select></div></form><div class="table-wrap" style="margin-top:20px"><table class="table"><thead><tr><th>Rank</th><th>Candidate</th><th>Experience</th><th>Education</th><th>Assessment</th><th>Composite score</th><?php if (!empty($_SESSION['admin_id'])): ?><th>Status</th><?php endif; ?></tr></thead><tbody><?php foreach($rows as $index => $row): ?><tr><td>#<?= $index+1 ?></td><td><strong><?= e($row['full_name']) ?></strong><?php if (!empty($_SESSION['admin_id'])): ?><br><small><?= e($row['email']) ?></small><?php endif; ?></td><td><?= e($row['experience_years']) ?> yrs</td><td><?= e($row['education']) ?></td><td><?= e($row['assessment_score']) ?>%</td><td><span class="pill"><?= e(number_format((float)$row['score'],2)) ?></span></td><?php if (!empty($_SESSION['admin_id'])): ?><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="applicant_id" value="<?= e($row['id']) ?>"><input type="hidden" name="job_id" value="<?= e($jobId) ?>"><select name="status" onchange="this.form.submit()"><?php foreach (['Pending', 'Under Review', 'Qualified', 'Rejected', 'Hired'] as $status): ?><option <?= $row['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></form></td><?php endif; ?></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="<?= !empty($_SESSION['admin_id']) ? '7' : '6' ?>">No applicants yet for this position.</td></tr><?php endif; ?></tbody></table></div></section></main>
<?php page_end(); ?>
