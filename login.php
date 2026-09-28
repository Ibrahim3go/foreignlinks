<?php
// ============================================================
//  Foreign Links Campus – Applicant Login
//  /login.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Already logged in → go to dashboard
if (isApplicantLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
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
            $errors[] = 'Please enter your email and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM applicants WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $applicant = $stmt->fetch();

            if ($applicant && verifyPassword($password, $applicant['password_hash'])) {
                // Update last login
                $pdo->prepare("UPDATE applicants SET last_login = NOW() WHERE id = ?")
                    ->execute([$applicant['id']]);

                loginApplicant($applicant);
                auditLog($pdo, 'Applicant', $applicant['id'], 'Login', 'Successful login');

                $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/dashboard.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $errors[] = 'Invalid email or password. Please try again.';
                auditLog($pdo, 'Applicant', 0, 'Failed Login', 'Email: ' . $email);
            }
        }
    }
}

$pageTitle = 'Applicant Login';
$bodyClass = 'page-auth';
require_once 'includes/header.php';
?>

<section class="auth-section">
    <div class="auth-container">

        <div class="auth-brand">
            <a href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/assets/images/logo.png"
                     alt="<?= SITE_NAME ?>"
                     onerror="this.style.display='none'">
            </a>
            <h1><?= SITE_NAME ?></h1>
            <p>Admission Portal — <?= SITE_SESSION ?></p>
        </div>

        <div class="auth-card">
            <div class="auth-card-header">
                <h2>Welcome Back</h2>
                <p>Sign in to continue your application</p>
            </div>

            <?php if ($errors): ?>
            <div class="alert alert-error">
                <span class="alert-icon">✕</span>
                <span><?= e(implode(' ', $errors)) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" novalidate>
                <?= csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="email">
                        Email Address <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           class="form-control"
                           value="<?= e($email) ?>"
                           placeholder="your@email.com"
                           required autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        Password <span class="required">*</span>
                    </label>
                    <div class="input-password-wrap">
                        <input type="password" id="password" name="password"
                               class="form-control"
                               placeholder="Enter your password"
                               required autocomplete="current-password">
                        <button type="button" class="toggle-password"
                                onclick="togglePassword('password')"
                                title="Show/hide password">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full btn-lg">
                    Sign In →
                </button>
            </form>

            <div class="auth-divider"><span>or</span></div>

            <div class="auth-links">
                <p>Don't have an account?
                    <a href="<?= BASE_URL ?>/apply.php">Apply &amp; Register</a>
                </p>
                <p>Want to check your status without login?
                    <a href="<?= BASE_URL ?>/status.php">Check Status</a>
                </p>
            </div>
        </div>

        <div class="auth-footer-note">
            <p>Having trouble? Contact us: <a href="tel:+2348033705261"><?= SITE_PHONE ?></a></p>
        </div>
    </div>
</section>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

<?php require_once 'includes/footer.php'; ?>
