<?php
// ============================================================
//  Foreign Links Campus – Helper Functions
//  /includes/functions.php
// ============================================================

require_once __DIR__ . '/db.php';

// ────────────────────────────────────────────────────────────
//  STRING & SECURITY HELPERS
// ────────────────────────────────────────────────────────────

// Sanitize output to prevent XSS
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Clean user input
function clean(string $input): string
{
    return trim(strip_tags($input));
}

// Hash a password
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify a password
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

// ────────────────────────────────────────────────────────────
//  REFERENCE NUMBER GENERATOR
// ────────────────────────────────────────────────────────────
// Format: FLC-2025-000001
function generateRefNumber(PDO $pdo): string
{
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM applicants");
    $count = (int) $stmt->fetchColumn();
    $sequence = str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    return REF_PREFIX . '-' . $year . '-' . $sequence;
}

// ────────────────────────────────────────────────────────────
//  FLASH MESSAGES
// ────────────────────────────────────────────────────────────
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Returns HTML alert div and clears the flash
function getFlash(): string
{
    if (empty($_SESSION['flash'])) return '';

    $flash   = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type    = $flash['type'];   // success | error | warning | info
    $message = e($flash['message']);

    $icons = [
        'success' => '✓',
        'error'   => '✕',
        'warning' => '⚠',
        'info'    => 'ℹ',
    ];
    $icon = $icons[$type] ?? 'ℹ';

    return "<div class=\"alert alert-{$type}\">
                <span class=\"alert-icon\">{$icon}</span>
                <span>{$message}</span>
            </div>";
}

// ────────────────────────────────────────────────────────────
//  DATE & TIME
// ────────────────────────────────────────────────────────────
function formatDate(string $date): string
{
    return date('d M Y', strtotime($date));
}

function formatDateTime(string $datetime): string
{
    return date('d M Y, g:i A', strtotime($datetime));
}

function calculateAge(string $dob): int
{
    return (int) date_diff(date_create($dob), date_create('today'))->y;
}

// ────────────────────────────────────────────────────────────
//  MONEY FORMATTING
// ────────────────────────────────────────────────────────────
function formatMoney(float $amount): string
{
    return '₦' . number_format($amount, 2);
}

// ────────────────────────────────────────────────────────────
//  FILE UPLOAD HANDLER
// ────────────────────────────────────────────────────────────
function uploadFile(array $file, string $subfolder, array $allowedTypes): array
{
    // Returns ['success' => bool, 'path' => string, 'error' => string]

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error. Please try again.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size must not exceed 5MB.'];
    }

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: PDF, JPG, PNG.'];
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('flc_', true) . '.' . strtolower($ext);
    $dir      = UPLOAD_PATH . $subfolder . '/';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $destination = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Could not save file. Check folder permissions.'];
    }

    return [
        'success'   => true,
        'path'      => $subfolder . '/' . $filename,
        'mime_type' => $mimeType,
        'size'      => $file['size'],
        'original'  => $file['name'],
    ];
}

// ────────────────────────────────────────────────────────────
//  APPLICATION HELPERS
// ────────────────────────────────────────────────────────────

// Get all active colleges
function getColleges(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM colleges WHERE is_active = 1 ORDER BY id")
               ->fetchAll();
}

// Get programmes by college
function getProgrammesByCollege(PDO $pdo, int $collegeId): array
{
    $stmt = $pdo->prepare("
        SELECT p.*, d.name AS department_name
        FROM   programmes p
        JOIN   departments d ON p.department_id = d.id
        WHERE  p.college_id = ? AND p.is_active = 1
        ORDER  BY d.name, p.name
    ");
    $stmt->execute([$collegeId]);
    return $stmt->fetchAll();
}

// Get current academic session
function getCurrentSession(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM academic_sessions WHERE is_current = 1 LIMIT 1")
               ->fetch();
}

// Get applicant's active application
function getActiveApplication(PDO $pdo, int $applicantId): array|false
{
    $stmt = $pdo->prepare("
        SELECT a.*, p.name AS programme_name, p.qualification,
               c.name AS college_name, c.code AS college_code,
               s.session AS academic_session
        FROM   applications a
        JOIN   programmes p        ON a.programme_id = p.id
        JOIN   colleges c          ON a.college_id   = c.id
        JOIN   academic_sessions s ON a.session_id   = s.id
        WHERE  a.applicant_id = ?
        ORDER  BY a.created_at DESC
        LIMIT  1
    ");
    $stmt->execute([$applicantId]);
    return $stmt->fetch();
}

// Get application completion percentage
function getApplicationProgress(array $application): int
{
    $steps = [
        $application['step_personal'],
        $application['step_academic'],
        $application['step_documents'],
        $application['step_payment'],
        $application['step_submitted'],
    ];
    $completed = array_sum($steps);
    return (int) round(($completed / 5) * 100);
}

// ────────────────────────────────────────────────────────────
//  STATUS BADGE HTML
// ────────────────────────────────────────────────────────────
function statusBadge(string $status): string
{
    $map = [
        'Pending'      => 'badge-warning',
        'Under Review' => 'badge-info',
        'Shortlisted'  => 'badge-primary',
        'Admitted'     => 'badge-success',
        'Rejected'     => 'badge-danger',
        'Deferred'     => 'badge-secondary',
    ];
    $class = $map[$status] ?? 'badge-secondary';
    return "<span class=\"badge {$class}\">" . e($status) . "</span>";
}

// ────────────────────────────────────────────────────────────
//  AUDIT LOGGER
// ────────────────────────────────────────────────────────────
function auditLog(PDO $pdo, string $userType, int $userId,
                  string $action, string $description = ''): void
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (user_type, user_id, action, description, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userType, $userId, $action, $description, $ip]);
}

// ────────────────────────────────────────────────────────────
//  NOTIFICATIONS
// ────────────────────────────────────────────────────────────
function sendPortalNotification(PDO $pdo, int $applicantId,
                                 string $subject, string $message): void
{
    $stmt = $pdo->prepare("
        INSERT INTO notifications (applicant_id, channel, subject, message)
        VALUES (?, 'Portal', ?, ?)
    ");
    $stmt->execute([$applicantId, $subject, $message]);
}

function getUnreadNotifications(PDO $pdo, int $applicantId): array
{
    $stmt = $pdo->prepare("
        SELECT * FROM notifications
        WHERE  applicant_id = ? AND is_read = 0
        ORDER  BY sent_at DESC
    ");
    $stmt->execute([$applicantId]);
    return $stmt->fetchAll();
}

function countUnreadNotifications(PDO $pdo, int $applicantId): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM notifications
        WHERE  applicant_id = ? AND is_read = 0
    ");
    $stmt->execute([$applicantId]);
    return (int) $stmt->fetchColumn();
}

// ────────────────────────────────────────────────────────────
//  NIGERIAN STATES LIST
// ────────────────────────────────────────────────────────────
function getNigerianStates(): array
{
    return [
        'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa',
        'Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti',
        'Enugu','FCT – Abuja','Gombe','Imo','Jigawa','Kaduna','Kano',
        'Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger',
        'Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto',
        'Taraba','Yobe','Zamfara'
    ];
}

// ────────────────────────────────────────────────────────────
//  PAGINATION HELPER
// ────────────────────────────────────────────────────────────
function paginate(PDO $pdo, string $countSql, array $params,
                  int $perPage = RECORDS_PER_PAGE): array
{
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total    = (int) $stmt->fetchColumn();
    $page     = max(1, (int) ($_GET['page'] ?? 1));
    $offset   = ($page - 1) * $perPage;
    $pages    = (int) ceil($total / $perPage);

    return [
        'total'    => $total,
        'page'     => $page,
        'pages'    => $pages,
        'per_page' => $perPage,
        'offset'   => $offset,
    ];
}
