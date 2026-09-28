<?php
// ============================================================
//  Foreign Links Campus – Applicant Dashboard
//  /dashboard.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireApplicantLogin();

$applicantId = (int)$_SESSION['applicant_id'];

// Load applicant data
$apStmt = $pdo->prepare("SELECT * FROM applicants WHERE id = ? LIMIT 1");
$apStmt->execute([$applicantId]);
$applicant = $apStmt->fetch();

// Load active application
$application = getActiveApplication($pdo, $applicantId);

// Load payments
$payments = [];
if ($application) {
    $payStmt = $pdo->prepare("SELECT * FROM payments WHERE application_id = ? ORDER BY created_at DESC");
    $payStmt->execute([$application['id']]);
    $payments = $payStmt->fetchAll();
}

// Load documents
$documents = [];
if ($application) {
    $docStmt = $pdo->prepare("SELECT * FROM documents WHERE application_id = ? ORDER BY uploaded_at DESC");
    $docStmt->execute([$application['id']]);
    $documents = $docStmt->fetchAll();
}

// Load notifications
$notifications = getUnreadNotifications($pdo, $applicantId);
$allNotifsStmt = $pdo->prepare("SELECT * FROM notifications WHERE applicant_id = ? ORDER BY sent_at DESC LIMIT 10");
$allNotifsStmt->execute([$applicantId]);
$allNotifs = $allNotifsStmt->fetchAll();

// Mark notifications as read
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE applicant_id=?")->execute([$applicantId]);

// Application progress
$progress = $application ? getApplicationProgress($application) : 0;

// College fee info
$collegeFees = [];
if ($application) {
    $collegeFees = FEES[$application['college_code']] ?? FEES['COE'];
}

$pageTitle = 'My Dashboard';
$bodyClass = 'page-dashboard';
require_once 'includes/header.php';
?>

<section class="dashboard-section">
<div class="container">

    <!-- Dashboard Header -->
    <div class="dash-header">
        <div class="dash-welcome">
            <div class="dash-avatar">
                <?php if (!empty($applicant['passport_photo'])): ?>
                <img src="<?= UPLOAD_URL . e($applicant['passport_photo']) ?>"
                     alt="Passport" class="avatar-img">
                <?php else: ?>
                <span class="avatar-initials">
                    <?= strtoupper(substr($applicant['firstname'],0,1) . substr($applicant['surname'],0,1)) ?>
                </span>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="dash-name">
                    Welcome, <?= e($applicant['firstname']) ?>!
                </h1>
                <p class="dash-ref">
                    Ref: <strong><?= e($applicant['ref_number']) ?></strong>
                    &nbsp;|&nbsp; <?= SITE_SESSION ?> Session
                </p>
            </div>
        </div>
        <div class="dash-actions">
            <?php if ($application && $application['step_submitted']): ?>
            <a href="<?= BASE_URL ?>/print-slip.php" class="btn btn-outline btn-sm">
                🖨 Print Slip
            </a>
            <?php endif; ?>
            <?php if (!$application): ?>
            <a href="<?= BASE_URL ?>/apply.php" class="btn btn-primary btn-sm">
                Start Application →
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?= getFlash() ?>

    <!-- Status Alert Banner -->
    <?php if ($application): ?>
    <div class="status-banner status-<?= strtolower(str_replace(' ','-',$application['status'])) ?>">
        <div class="status-banner-left">
            <span class="status-banner-icon">
                <?php
                $icons = [
                    'Pending'      => '⏳',
                    'Under Review' => '🔍',
                    'Shortlisted'  => '📋',
                    'Admitted'     => '🎓',
                    'Rejected'     => '❌',
                    'Deferred'     => '📅',
                ];
                echo $icons[$application['status']] ?? '📄';
                ?>
            </span>
            <div>
                <strong>Application Status: <?= e($application['status']) ?></strong>
                <p><?= e($application['programme_name']) ?> — <?= e($application['college_name']) ?></p>
            </div>
        </div>
        <?= statusBadge($application['status']) ?>
    </div>
    <?php endif; ?>

    <div class="dash-grid">

        <!-- LEFT COLUMN -->
        <div class="dash-main">

            <!-- Application Progress Card -->
            <?php if ($application): ?>
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Application Progress</h3>
                    <span class="progress-pct"><?= $progress ?>% Complete</span>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar" style="width:<?= $progress ?>%"></div>
                </div>

                <div class="checklist">
                    <div class="checklist-item <?= $application['step_personal']  ? 'done' : '' ?>">
                        <span class="cl-icon"><?= $application['step_personal']  ? '✓' : '○' ?></span>
                        <span>Programme Selected</span>
                        <?php if (!$application['step_personal']): ?>
                        <a href="<?= BASE_URL ?>/apply.php?step=2" class="cl-action">Complete →</a>
                        <?php endif; ?>
                    </div>
                    <div class="checklist-item <?= $application['step_academic']  ? 'done' : '' ?>">
                        <span class="cl-icon"><?= $application['step_academic']  ? '✓' : '○' ?></span>
                        <span>Personal Details</span>
                        <?php if (!$application['step_academic']): ?>
                        <a href="<?= BASE_URL ?>/apply.php?step=3" class="cl-action">Complete →</a>
                        <?php endif; ?>
                    </div>
                    <div class="checklist-item <?= $application['step_documents'] ? 'done' : '' ?>">
                        <span class="cl-icon"><?= $application['step_documents'] ? '✓' : '○' ?></span>
                        <span>Academic Details &amp; O'Level</span>
                        <?php if (!$application['step_documents']): ?>
                        <a href="<?= BASE_URL ?>/apply.php?step=4" class="cl-action">Complete →</a>
                        <?php endif; ?>
                    </div>
                    <div class="checklist-item <?= $application['step_payment']   ? 'done' : '' ?>">
                        <span class="cl-icon"><?= $application['step_payment']   ? '✓' : '○' ?></span>
                        <span>Application Fee Payment</span>
                        <?php if (!$application['step_payment']): ?>
                        <a href="<?= BASE_URL ?>/payment.php" class="cl-action">Pay Now →</a>
                        <?php endif; ?>
                    </div>
                    <div class="checklist-item <?= $application['step_submitted'] ? 'done' : '' ?>">
                        <span class="cl-icon"><?= $application['step_submitted'] ? '✓' : '○' ?></span>
                        <span>Application Submitted</span>
                        <?php if (!$application['step_submitted']): ?>
                        <a href="<?= BASE_URL ?>/apply.php?step=5" class="cl-action">Review &amp; Submit →</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Programme Info Card -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Programme Information</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span>College</span>
                        <strong><?= e($application['college_name']) ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Programme</span>
                        <strong><?= e($application['programme_name']) ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Qualification</span>
                        <strong><?= e($application['qualification']) ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Entry Mode</span>
                        <strong><?= e($application['entry_mode']) ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Session</span>
                        <strong><?= e($application['academic_session']) ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Status</span>
                        <strong><?= statusBadge($application['status']) ?></strong>
                    </div>
                </div>
                <?php if ($application['admin_remark']): ?>
                <div class="admin-remark">
                    <span>📝 Admin Remark:</span>
                    <?= e($application['admin_remark']) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Admission Letter (if admitted) -->
            <?php if ($application['status'] === 'Admitted'): ?>
            <div class="dash-card dash-card-admitted">
                <div class="admitted-banner">
                    <span class="admitted-icon">🎓</span>
                    <div>
                        <h3>Congratulations! You have been admitted.</h3>
                        <p>Download your provisional admission letter and print your admission slip.</p>
                    </div>
                </div>
                <div class="admitted-actions">
                    <a href="<?= BASE_URL ?>/print-slip.php" class="btn btn-primary">
                        🖨 Print Admission Slip
                    </a>
                    <a href="<?= BASE_URL ?>/payment.php" class="btn btn-outline">
                        Pay Acceptance Fee
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documents -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Uploaded Documents</h3>
                    <a href="<?= BASE_URL ?>/documents.php" class="btn btn-sm btn-outline">
                        + Upload
                    </a>
                </div>
                <?php if ($documents): ?>
                <div class="doc-list">
                    <?php foreach ($documents as $doc): ?>
                    <div class="doc-item">
                        <span class="doc-type-icon">📄</span>
                        <div class="doc-info">
                            <strong><?= e($doc['document_type']) ?></strong>
                            <small><?= e($doc['file_name']) ?> &bull; <?= formatDateTime($doc['uploaded_at']) ?></small>
                        </div>
                        <span class="doc-status <?= $doc['is_verified'] ? 'verified' : 'pending' ?>">
                            <?= $doc['is_verified'] ? '✓ Verified' : 'Pending' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <span>📂</span>
                    <p>No documents uploaded yet.</p>
                    <a href="<?= BASE_URL ?>/documents.php" class="btn btn-sm btn-primary">Upload Documents</a>
                </div>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <!-- No Application Yet -->
            <div class="dash-card">
                <div class="empty-state large">
                    <span>📝</span>
                    <h3>No Application Yet</h3>
                    <p>You have not started an application for the <?= SITE_SESSION ?> session.</p>
                    <a href="<?= BASE_URL ?>/apply.php" class="btn btn-primary btn-lg">
                        Start Application Now →
                    </a>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /dash-main -->

        <!-- RIGHT COLUMN -->
        <div class="dash-sidebar">

            <!-- Quick Links -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Quick Actions</h3></div>
                <div class="quick-links">
                    <a href="<?= BASE_URL ?>/payment.php" class="quick-link">
                        <span class="ql-icon">💳</span>
                        <span>Make Payment</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <a href="<?= BASE_URL ?>/documents.php" class="quick-link">
                        <span class="ql-icon">📁</span>
                        <span>Upload Documents</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <a href="<?= BASE_URL ?>/status.php" class="quick-link">
                        <span class="ql-icon">🔍</span>
                        <span>Check Status</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <?php if ($application && $application['step_submitted']): ?>
                    <a href="<?= BASE_URL ?>/print-slip.php" class="quick-link">
                        <span class="ql-icon">🖨</span>
                        <span>Print Slip</span>
                        <span class="ql-arrow">→</span>
                    </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/process/logout.php" class="quick-link danger">
                        <span class="ql-icon">🚪</span>
                        <span>Logout</span>
                        <span class="ql-arrow">→</span>
                    </a>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Payment Summary</h3></div>
                <?php if ($payments): ?>
                <div class="pay-list">
                    <?php foreach ($payments as $pay): ?>
                    <div class="pay-item">
                        <div class="pay-left">
                            <strong><?= e($pay['payment_type']) ?></strong>
                            <small><?= formatDate($pay['created_at']) ?></small>
                        </div>
                        <div class="pay-right">
                            <span class="pay-amount"><?= formatMoney($pay['amount']) ?></span>
                            <span class="pay-status pay-<?= strtolower($pay['status']) ?>">
                                <?= e($pay['status']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <span>💳</span>
                    <p>No payments recorded yet.</p>
                    <?php if ($application && $collegeFees): ?>
                    <small>Application fee:
                        <strong><?= formatMoney($collegeFees['application']) ?></strong>
                    </small>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/payment.php" class="btn btn-sm btn-primary mt-1">
                        Pay Now
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Notifications</h3>
                    <?php if (count($allNotifs)): ?>
                    <span class="notif-count"><?= count($allNotifs) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($allNotifs): ?>
                <div class="notif-list">
                    <?php foreach ($allNotifs as $n): ?>
                    <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                        <div class="notif-subject"><?= e($n['subject']) ?></div>
                        <div class="notif-msg"><?= $n['message'] ?></div>
                        <div class="notif-time"><?= formatDateTime($n['sent_at']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <span>🔔</span>
                    <p>No notifications yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Applicant Info -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>My Information</h3></div>
                <div class="info-list">
                    <div class="il-item"><span>Name</span><strong><?= e($applicant['surname'].', '.$applicant['firstname']) ?></strong></div>
                    <div class="il-item"><span>Ref No.</span><strong><?= e($applicant['ref_number']) ?></strong></div>
                    <div class="il-item"><span>Email</span><strong><?= e($applicant['email']) ?></strong></div>
                    <div class="il-item"><span>Phone</span><strong><?= e($applicant['phone']) ?></strong></div>
                    <div class="il-item"><span>State</span><strong><?= e($applicant['state_of_origin']) ?></strong></div>
                    <div class="il-item"><span>Gender</span><strong><?= e($applicant['gender']) ?></strong></div>
                </div>
            </div>

        </div><!-- /dash-sidebar -->
    </div><!-- /dash-grid -->
</div>
</section>

<?php require_once 'includes/footer.php'; ?>
