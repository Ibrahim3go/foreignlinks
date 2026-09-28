<?php
// ============================================================
//  Foreign Links Campus – Auth / Session Guard
//  /includes/auth.php
// ============================================================

require_once dirname(__DIR__) . '/config/config.php';

// ── Require Applicant Login ──────────────────────────────────
// Call this at the top of any applicant-only page
function requireApplicantLogin(): void
{
    if (empty($_SESSION['applicant_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/login.php?msg=login_required');
        exit;
    }
}

// ── Require Admin Login ──────────────────────────────────────
// Call this at the top of any admin-only page
function requireAdminLogin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login.php?msg=login_required');
        exit;
    }
}

// ── Require Specific Admin Role ──────────────────────────────
function requireRole(string $role): void
{
    requireAdminLogin();
    if ($_SESSION['admin_role'] !== $role && $_SESSION['admin_role'] !== 'Super Admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php?msg=access_denied');
        exit;
    }
}

// ── Check if Applicant is Logged In ─────────────────────────
function isApplicantLoggedIn(): bool
{
    return !empty($_SESSION['applicant_id']);
}

// ── Check if Admin is Logged In ─────────────────────────────
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

// ── Log In Applicant ─────────────────────────────────────────
function loginApplicant(array $applicant): void
{
    session_regenerate_id(true);
    $_SESSION['applicant_id']        = $applicant['id'];
    $_SESSION['applicant_ref']       = $applicant['ref_number'];
    $_SESSION['applicant_name']      = $applicant['firstname'] . ' ' . $applicant['surname'];
    $_SESSION['applicant_email']     = $applicant['email'];
}

// ── Log In Admin ─────────────────────────────────────────────
function loginAdmin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id']        = $admin['id'];
    $_SESSION['admin_name']      = $admin['fullname'];
    $_SESSION['admin_email']     = $admin['email'];
    $_SESSION['admin_role']      = $admin['role'];
    $_SESSION['admin_college']   = $admin['college_id'];
}

// ── Log Out ──────────────────────────────────────────────────
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ── CSRF Token ───────────────────────────────────────────────
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(generateCsrfToken()) . '">';
}
