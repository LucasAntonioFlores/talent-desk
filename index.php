<?php
require_once __DIR__ . '/partials.php';
page_start('Home');
topbar();
?>
<main class="wrap">
    <section class="hero">
        <div class="eyebrow">CANDIDATE INTAKE</div>
        <h1>Make your next move count.</h1>
        <p>Share your experience, strengths, and technical background. Your profile will be scored against the role requirements.</p>
        <div class="actions" style="justify-content:flex-start">
            <a class="button" href="apply.php">Apply for a position</a>
            <a class="button secondary" href="leaderboard.php">View leaderboard</a>
        </div>
    </section>
    <section class="card" style="margin-top:35px">
        <div class="split"><div><div class="eyebrow">OPEN ROLES</div><h2>Find your fit.</h2></div><a href="login.php">Administrator sign in →</a></div>
        <div class="job-list" style="margin-top:22px">
            <?php foreach (db()->query('SELECT id,title,department,employment_type,description FROM jobs ORDER BY created_at DESC') as $job): ?>
                <a href="apply.php?job=<?= e($job['id']) ?>"><strong><?= e($job['title']) ?></strong><br><small><?= e($job['department']) ?> · <?= e($job['employment_type']) ?></small><p><?= e($job['description']) ?></p></a>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php page_end(); ?>
