<?php
require_once __DIR__ . '/partials.php';
start_session();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([trim($_POST['email'] ?? '')]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        redirect('jobs.php');
    }
    $error = 'Invalid email or password.';
}
page_start('Admin sign in');
topbar();
?>
<main class="wrap"><section class="card login"><div class="eyebrow" style="text-align:center">HR ACCESS</div><h1>Sign in</h1>
<?php if ($error): ?><div class="notice"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required autofocus></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required></div><button class="button" type="submit">SIGN IN</button></form>
<p style="font-size:12px;color:var(--muted);text-align:center">Demo: admin@talentdesk.test / password</p></section></main>
<?php page_end(); ?>
