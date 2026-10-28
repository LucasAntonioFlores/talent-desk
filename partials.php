<?php
require_once __DIR__ . '/config.php';

function page_start(string $title, bool $admin = false): void
{
    start_session();
    if ($admin) {
        require_admin();
    }
    ?><!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · Talent Desk</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
    <?php
}

function topbar(string $active = ''): void
{
    ?>
    <header class="topbar">
        <a class="brand" href="index.php">Talent Desk</a>
        <nav>
            <a class="<?= $active === 'jobs' ? 'active' : '' ?>" href="jobs.php">Manage jobs</a>
            <a class="<?= $active === 'apply' ? 'active' : '' ?>" href="apply.php">Applicant portal</a>
            <a href="leaderboard.php">Leaderboard</a>
            <?php if (!empty($_SESSION['admin_id'])): ?><a href="jobs.php">Admin dashboard</a><?php endif; ?>
            <?php if (!empty($_SESSION['admin_id'])): ?><a href="logout.php">Sign out</a><?php endif; ?>
        </nav>
    </header>
    <?php
}

function page_end(): void
{
    ?></body></html><?php
}
