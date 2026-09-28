<?php
// ============================================================
//  Foreign Links Campus – Register / Create Account
//  /process/register.php   (POST handler)
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/apply.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request token. Please try again.');
    header('Location: ' . BASE_URL . '/apply.php');
    exit;
}

// ── Collect & sanitize inputs ────────────────────────────────
$surname    = clean($_POST['surname']    ?? '');
$firstname  = clean($_POST['firstname']  ?? '');
$middlename = clean($_POST['middlename'] ?? '');
$email      = strtolower(clean($_POST['email']    ?? ''));
$phone      = clean($_POST['phone']      ?? '');
$gender     = clean($_POST['gender']     ?? '');
$dob        = clean($_POST['dob']        ?? '');
$state      = clean($_POST['state_of_origin'] ?? '');
$lga        = clean($_POST['lga']        ?? '');
$address    = clean($_POST['home_address'] ?? '');
$password   = $_POST['password']         ?? '';
$password2  = $_POST['password2']        ?? '';

$errors = [];

// ── Validation ───────────────────────────────────────────────
if (!$surname)   $errors[] = 'Surname is required.';
if (!$firstname) $errors[] = 'First name is required.';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'A valid email address is required.';
if (!preg_match('/^[0-9+\-\s]{10,15}$/', $phone))
    $errors[] = 'Enter a valid Nigerian phone number.';
if (!in_array($gender, ['Male','Female','Other']))
    $errors[] = 'Please select your gender.';
if (!$dob)       $errors[] = 'Date of birth is required.';
if (!$state)     $errors[] = 'State of origin is required.';
if (!$lga)       $errors[] = 'LGA is required.';
if (!$address)   $errors[] = 'Home address is required.';

if (strlen($password) < 8)
    $errors[] = 'Password must be at least 8 characters.';
if (!preg_match('/[A-Z]/', $password))
    $errors[] = 'Password must contain at least one uppercase letter.';
if (!preg_match('/[0-9]/', $password))
    $errors[] = 'Password must contain at least one number.';
if ($password !== $password2)
    $errors[] = 'Passwords do not match.';

// ── Check duplicate email ────────────────────────────────────
if (!$errors) {
    $chk = $pdo->prepare("SELECT id FROM applicants WHERE email = ? LIMIT 1");
    $chk->execute([$email]);
    if ($chk->fetch()) {
        $errors[] = 'An account with this email already exists. Please login.';
    }
}

if ($errors) {
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old']    = compact('surname','firstname','middlename',
                                       'email','phone','gender','dob',
                                       'state','lga','address');
    header('Location: ' . BASE_URL . '/apply.php');
    exit;
}

// ── Create applicant account ─────────────────────────────────
$refNumber = generateRefNumber($pdo);
$hash      = hashPassword($password);

$stmt = $pdo->prepare("
    INSERT INTO applicants
        (ref_number, surname, firstname, middlename, email, phone,
         password_hash, gender, date_of_birth, state_of_origin,
         lga, home_address, is_verified)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1)
");
$stmt->execute([
    $refNumber, $surname, $firstname, $middlename,
    $email, $phone, $hash, $gender, $dob,
    $state, $lga, $address
]);

$applicantId = (int) $pdo->lastInsertId();

// ── Auto-login the new applicant ─────────────────────────────
$newApplicant = $pdo->prepare("SELECT * FROM applicants WHERE id = ? LIMIT 1");
$newApplicant->execute([$applicantId]);
loginApplicant($newApplicant->fetch());

// ── Log & notify ─────────────────────────────────────────────
auditLog($pdo, 'Applicant', $applicantId, 'Register',
         'New applicant registered. Ref: ' . $refNumber);

sendPortalNotification($pdo, $applicantId,
    'Welcome to ' . SITE_NAME,
    "Dear {$firstname}, your account has been created. Your reference number is <strong>{$refNumber}</strong>. Keep it safe.");

// Optional email (won't break if SMTP not configured)
@require_once dirname(__DIR__) . '/config/mailer.php';
if (function_exists('mailWelcome')) {
    mailWelcome($email, $firstname . ' ' . $surname, $refNumber);
}

setFlash('success',
    "Account created! Your reference number is {$refNumber}. Now complete your application.");
header('Location: ' . BASE_URL . '/apply.php?step=2');
exit;
