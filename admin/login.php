<?php
// ============================================================
//  Foreign Links Campus – Admin Login
//  /admin/login.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit;
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email    = clean($_POST['email']    ?? '');
        $password = clean($_POST['password'] ?? '');

        if (!$email || !$password) {
            $errors[] = 'Email and password are required.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email=? AND is_active=1 LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && verifyPassword($password, $admin['password_hash'])) {
                $pdo->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")
                    ->execute([$admin['id']]);
                loginAdmin($admin);
                auditLog($pdo,'Admin',$admin['id'],'Admin Login','Successful');
                header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login | <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-login-body">

<div class="admin-login-wrap">
    <div class="admin-login-card">
        <div class="admin-login-header">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo"
                 onerror="this.style.display='none'" class="admin-logo">
            <h1><?= SITE_NAME ?></h1>
            <p>Admin Portal — <?= SITE_SESSION ?></p>
        </div>

        <?php if ($errors): ?>
        <div class="alert alert-error">
            <span class="alert-icon">✕</span>
            <span><?= e(implode(' ', $errors)) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label">Admin Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= e($email) ?>" placeholder="admin@foreignlinks.edu.ng"
                       required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-password-wrap">
                    <input type="password" id="pw" name="password" class="form-control"
                           placeholder="Enter password" required>
                    <button type="button" class="toggle-password"
                            onclick="const e=document.getElementById('pw');e.type=e.type==='password'?'text':'password'">👁</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px">
                Sign In to Admin Panel →
            </button>
        </form>

        <div style="text-align:center;margin-top:20px;font-size:12px;color:#888">
            <a href="<?= BASE_URL ?>/index.php" style="color:#1a6b4a">← Back to Public Portal</a>
        </div>
    </div>
    <div style="text-align:center;margin-top:16px;font-size:11px;color:#aaa">
        <?= SITE_NAME ?> &copy; <?= date('Y') ?> &nbsp;|&nbsp; Admin Access Only
    </div>
</div>
</body>
</html>
