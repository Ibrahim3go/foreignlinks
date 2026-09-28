<?php
// ============================================================
//  Foreign Links Campus – Admin: Update Application Status
//  /process/update-status.php  (POST handler)
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/applicants.php'); exit;
}
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request token.');
    header('Location: ' . BASE_URL . '/admin/applicants.php'); exit;
}

$appId     = (int)($_POST['app_id']        ?? 0);
$newStatus = clean($_POST['status']        ?? '');
$remark    = clean($_POST['admin_remark']  ?? '');

$validStatuses = ['Pending','Under Review','Shortlisted','Admitted','Rejected','Deferred'];

if (!$appId || !in_array($newStatus, $validStatuses)) {
    setFlash('error', 'Invalid request.');
    header('Location: ' . BASE_URL . '/admin/applicants.php'); exit;
}

// Update
$pdo->prepare("UPDATE applications SET status=?, admin_remark=?,
    reviewed_by=?, reviewed_at=NOW() WHERE id=?")
    ->execute([$newStatus, $remark, $_SESSION['admin_id'], $appId]);

// Get applicant id for notification
$apRes = $pdo->prepare("SELECT applicant_id FROM applications WHERE id=?");
$apRes->execute([$appId]);
$applicantId = (int)$apRes->fetchColumn();

// Notify applicant
$msgs = [
    'Admitted'     => 'Congratulations! You have been offered admission. Login to download your admission slip.',
    'Rejected'     => 'We regret that your application was unsuccessful. Contact the admissions office for details.',
    'Shortlisted'  => 'Your application has been shortlisted. Await further instructions from the admissions office.',
    'Under Review' => 'Your application is currently under review. You will be notified of updates.',
    'Deferred'     => 'Your application has been deferred. Contact the admissions office for more information.',
];
if (isset($msgs[$newStatus])) {
    sendPortalNotification($pdo, $applicantId,
        'Application Status Update: ' . $newStatus,
        $msgs[$newStatus] . ($remark ? '<br><strong>Remark:</strong> ' . e($remark) : ''));
}

auditLog($pdo, 'Admin', $_SESSION['admin_id'], 'Status Update',
         "App #{$appId} changed to {$newStatus}");

setFlash('success', "Application #$appId status updated to: $newStatus");
header('Location: ' . BASE_URL . '/admin/review.php?id=' . $appId);
exit;
