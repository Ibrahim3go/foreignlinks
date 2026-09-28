<?php
// ============================================================
//  Foreign Links Campus – Check Application Status
//  /status.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$pageTitle = 'Check Application Status';
$bodyClass = 'page-status';

$result  = null;
$error   = '';
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched  = true;
    $refNumber = strtoupper(clean($_POST['ref_number'] ?? ''));
    $surname   = strtoupper(clean($_POST['surname']    ?? ''));

    if (!$refNumber || !$surname) {
        $error = 'Please enter both your reference number and surname.';
    } else {
        $stmt = $pdo->prepare("
            SELECT a.*, ap.surname, ap.firstname, ap.middlename,
                   ap.email, ap.phone, ap.passport_photo,
                   p.name AS programme_name, p.qualification,
                   c.name AS college_name, c.code AS college_code,
                   s.session AS academic_session
            FROM   applicants ap
            JOIN   applications a  ON ap.id = a.applicant_id
            JOIN   programmes p    ON a.programme_id = p.id
            JOIN   colleges c      ON a.college_id = c.id
            JOIN   academic_sessions s ON a.session_id = s.id
            WHERE  ap.ref_number = ?
            AND    UPPER(ap.surname) = ?
            ORDER  BY a.created_at DESC
            LIMIT  1
        ");
        $stmt->execute([$refNumber, $surname]);
        $result = $stmt->fetch();

        if (!$result) {
            $error = 'No application found. Check your reference number and surname.';
        } else {
            // Log status check
            auditLog($pdo, 'Applicant', $result['applicant_id'],
                     'Status Check', 'Ref: ' . $refNumber);
        }
    }
}

require_once 'includes/header.php';
?>

<section class="status-section section">
<div class="container">

    <div class="status-header text-center">
        <span class="section-label">Application Tracker</span>
        <h1 class="section-title">Check Your Application Status</h1>
        <p class="section-desc">
            Enter your reference number and surname to view your application status.
        </p>
    </div>

    <!-- Search Form -->
    <div class="status-search-card">
        <form method="POST" action="" novalidate>
            <?= csrfField() ?>
            <div class="status-form-row">
                <div class="form-group">
                    <label class="form-label">
                        Application Reference Number <span class="required">*</span>
                    </label>
                    <input type="text" name="ref_number" class="form-control"
                           placeholder="e.g. FLC-2025-000001"
                           value="<?= e($_POST['ref_number'] ?? '') ?>"
                           style="text-transform:uppercase" required>
                    <span class="form-hint">Provided when you registered on this portal.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Surname <span class="required">*</span>
                    </label>
                    <input type="text" name="surname" class="form-control"
                           placeholder="Your surname as registered"
                           value="<?= e($_POST['surname'] ?? '') ?>"
                           style="text-transform:uppercase" required>
                </div>
                <div class="form-group status-btn-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-full">
                        🔍 Check Status
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Error -->
    <?php if ($error): ?>
    <div class="alert alert-error" style="max-width:700px;margin:20px auto">
        <span class="alert-icon">✕</span>
        <span><?= e($error) ?></span>
    </div>
    <?php endif; ?>

    <!-- Result -->
    <?php if ($result): ?>
    <div class="status-result">

        <div class="status-result-header">
            <div class="status-result-photo">
                <?php if ($result['passport_photo']): ?>
                <img src="<?= UPLOAD_URL . e($result['passport_photo']) ?>" alt="Passport">
                <?php else: ?>
                <div class="photo-placeholder">
                    <?= strtoupper(substr($result['firstname'],0,1).substr($result['surname'],0,1)) ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="status-result-bio">
                <h2><?= e($result['surname'].', '.$result['firstname'].' '.($result['middlename']??'')) ?></h2>
                <p>Ref: <strong><?= e($result['ref_number'] ?? '') ?></strong></p>
                <p><?= e($result['academic_session']) ?> Session</p>
            </div>
            <div class="status-result-badge">
                <?= statusBadge($result['status']) ?>
            </div>
        </div>

        <!-- Status Timeline -->
        <div class="status-timeline">
            <h3>Application Timeline</h3>
            <div class="timeline">
                <div class="tl-item done">
                    <div class="tl-dot">✓</div>
                    <div class="tl-body">
                        <strong>Application Received</strong>
                        <small><?= $result['created_at'] ? formatDateTime($result['created_at']) : '' ?></small>
                    </div>
                </div>
                <div class="tl-item <?= $result['step_submitted'] ? 'done' : 'pending' ?>">
                    <div class="tl-dot"><?= $result['step_submitted'] ? '✓' : '2' ?></div>
                    <div class="tl-body">
                        <strong>Application Submitted</strong>
                        <small><?= $result['submitted_at'] ? formatDateTime($result['submitted_at']) : 'Pending' ?></small>
                    </div>
                </div>
                <div class="tl-item <?= in_array($result['status'],['Under Review','Shortlisted','Admitted']) ? 'done' : 'pending' ?>">
                    <div class="tl-dot"><?= in_array($result['status'],['Under Review','Shortlisted','Admitted']) ? '✓' : '3' ?></div>
                    <div class="tl-body">
                        <strong>Under Review</strong>
                        <small><?= $result['reviewed_at'] ? formatDateTime($result['reviewed_at']) : 'Awaiting review' ?></small>
                    </div>
                </div>
                <div class="tl-item <?= in_array($result['status'],['Shortlisted','Admitted']) ? 'done' : 'pending' ?>">
                    <div class="tl-dot"><?= in_array($result['status'],['Shortlisted','Admitted']) ? '✓' : '4' ?></div>
                    <div class="tl-body">
                        <strong>Shortlisted</strong>
                        <small><?= $result['status'] === 'Shortlisted' ? 'You have been shortlisted' : ($result['status'] === 'Admitted' ? 'Completed' : 'Awaiting') ?></small>
                    </div>
                </div>
                <div class="tl-item <?= $result['status'] === 'Admitted' ? 'done' : ($result['status'] === 'Rejected' ? 'rejected' : 'pending') ?>">
                    <div class="tl-dot"><?= $result['status'] === 'Admitted' ? '🎓' : '5' ?></div>
                    <div class="tl-body">
                        <strong>
                            <?= $result['status'] === 'Rejected' ? 'Not Admitted' : 'Admission Offered' ?>
                        </strong>
                        <small>
                            <?php if ($result['status'] === 'Admitted'): ?>
                                Congratulations! Proceed to pay acceptance fee.
                            <?php elseif ($result['status'] === 'Rejected'): ?>
                                <?= e($result['admin_remark'] ?: 'Your application was unsuccessful.') ?>
                            <?php else: ?>
                                Awaiting decision
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programme Details -->
        <div class="status-details-grid">
            <div class="sd-block">
                <h4>Programme Details</h4>
                <div class="sd-rows">
                    <div class="sd-row"><span>College</span><strong><?= e($result['college_name']) ?></strong></div>
                    <div class="sd-row"><span>Programme</span><strong><?= e($result['programme_name']) ?></strong></div>
                    <div class="sd-row"><span>Qualification</span><strong><?= e($result['qualification']) ?></strong></div>
                    <div class="sd-row"><span>Entry Mode</span><strong><?= e($result['entry_mode']) ?></strong></div>
                </div>
            </div>
            <div class="sd-block">
                <h4>Payment Status</h4>
                <div class="sd-rows">
                    <div class="sd-row">
                        <span>Application Fee</span>
                        <strong><?= $result['form_fee_paid'] ? '<span class="text-success">✓ Paid</span>' : '<span class="text-warning">Unpaid</span>' ?></strong>
                    </div>
                    <div class="sd-row">
                        <span>Acceptance Fee</span>
                        <strong><?= $result['acceptance_paid'] ? '<span class="text-success">✓ Paid</span>' : '<span class="text-warning">Unpaid</span>' ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="status-result-actions">
            <?php if (isApplicantLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary">Login to Dashboard</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/status.php" class="btn btn-outline">Check Another</a>
        </div>

    </div><!-- /status-result -->
    <?php endif; ?>

    <!-- Info Box (shown when no search yet) -->
    <?php if (!$searched): ?>
    <div class="status-info-row">
        <div class="status-info-card">
            <span class="sic-icon">📋</span>
            <h4>Reference Number</h4>
            <p>Your reference number was sent to your email when you registered. Format: FLC-YYYY-XXXXXX</p>
        </div>
        <div class="status-info-card">
            <span class="sic-icon">🔒</span>
            <h4>Secure Check</h4>
            <p>Your surname is required as a security measure to protect your application data.</p>
        </div>
        <div class="status-info-card">
            <span class="sic-icon">📞</span>
            <h4>Need Help?</h4>
            <p>Contact the admissions office on <a href="tel:+2348033705261"><?= SITE_PHONE ?></a> for assistance.</p>
        </div>
    </div>
    <?php endif; ?>

</div>
</section>

<?php require_once 'includes/footer.php'; ?>
