<?php
// ============================================================
//  Foreign Links Campus – Admin: Review Application
//  /admin/review.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$appId = (int)($_GET['id'] ?? 0);

// ── Handle Status Update POST ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error','Invalid request token.');
        header('Location: ' . BASE_URL . '/admin/review.php?id='.$appId); exit;
    }

    $action = clean($_POST['action'] ?? '');

    // Update application status
    if ($action === 'update_status') {
        $newStatus = clean($_POST['status'] ?? '');
        $remark    = clean($_POST['admin_remark'] ?? '');
        $validStatuses = ['Pending','Under Review','Shortlisted','Admitted','Rejected','Deferred'];

        if (!in_array($newStatus, $validStatuses)) {
            setFlash('error','Invalid status selected.');
        } else {
            $pdo->prepare("UPDATE applications SET status=?, admin_remark=?,
                reviewed_by=?, reviewed_at=NOW() WHERE id=?")
                ->execute([$newStatus, $remark, $_SESSION['admin_id'], $appId]);

            // Notify applicant
            $apRes = $pdo->prepare("SELECT applicant_id FROM applications WHERE id=?");
            $apRes->execute([$appId]);
            $applicantId = (int)$apRes->fetchColumn();

            $msgs = [
                'Admitted'     => 'Congratulations! Your application has been approved. You have been offered admission. Please login to download your admission slip.',
                'Rejected'     => 'We regret to inform you that your application was unsuccessful. Contact the admissions office for more information.',
                'Shortlisted'  => 'Your application has been shortlisted. Please await further instructions from the admissions office.',
                'Under Review' => 'Your application is currently under review. You will be notified of any updates.',
                'Deferred'     => 'Your application has been deferred to a future session. Please contact the admissions office.',
            ];

            if (isset($msgs[$newStatus])) {
                sendPortalNotification($pdo, $applicantId,
                    'Application Status Update: '.$newStatus,
                    $msgs[$newStatus] . ($remark ? '<br><strong>Remark:</strong> '.$remark : ''));
            }

            auditLog($pdo,'Admin',$_SESSION['admin_id'],'Status Update',
                     "App #{$appId} → {$newStatus}");
            setFlash('success', "Application status updated to: {$newStatus}");
        }
        header('Location: ' . BASE_URL . '/admin/review.php?id='.$appId); exit;
    }

    // Verify payment
    if ($action === 'verify_payment') {
        $payId    = (int)($_POST['payment_id'] ?? 0);
        $decision = clean($_POST['decision'] ?? '');
        $remark   = clean($_POST['remark'] ?? '');

        if ($decision === 'approve') {
            $pdo->prepare("UPDATE payments SET status='Verified', verified_by=?, verified_at=NOW(), remark=? WHERE id=?")
                ->execute([$_SESSION['admin_id'], $remark, $payId]);

            // Get payment type and update application flags
            $payInfo = $pdo->prepare("SELECT * FROM payments WHERE id=?");
            $payInfo->execute([$payId]);
            $pay = $payInfo->fetch();

            if ($pay) {
                if ($pay['payment_type'] === 'Application Fee') {
                    $pdo->prepare("UPDATE applications SET form_fee_paid=1, step_payment=1 WHERE id=?")
                        ->execute([$appId]);
                }
                if ($pay['payment_type'] === 'Acceptance Fee') {
                    $pdo->prepare("UPDATE applications SET acceptance_paid=1 WHERE id=?")
                        ->execute([$appId]);
                }
                sendPortalNotification($pdo, $pay['applicant_id'],
                    'Payment Verified',
                    formatMoney($pay['amount']).' '.$pay['payment_type'].' has been verified. ✓');
            }

            setFlash('success','Payment verified successfully.');
        } else {
            $pdo->prepare("UPDATE payments SET status='Rejected', verified_by=?, verified_at=NOW(), remark=? WHERE id=?")
                ->execute([$_SESSION['admin_id'], $remark, $payId]);
            setFlash('warning','Payment rejected.');
        }
        header('Location: ' . BASE_URL . '/admin/review.php?id='.$appId); exit;
    }
}

// ── Load application ──────────────────────────────────────────
if (!$appId) {
    header('Location: ' . BASE_URL . '/admin/applicants.php'); exit;
}

$appStmt = $pdo->prepare("
    SELECT a.*, ap.*, p.name AS programme_name, p.qualification,
           c.name AS college_name, c.code AS college_code,
           s.session AS academic_session
    FROM   applications a
    JOIN   applicants ap   ON a.applicant_id  = ap.id
    JOIN   programmes p    ON a.programme_id  = p.id
    JOIN   colleges c      ON a.college_id    = c.id
    JOIN   academic_sessions s ON a.session_id = s.id
    WHERE  a.id = ?
");
$appStmt->execute([$appId]);
$app = $appStmt->fetch();

if (!$app) {
    setFlash('error','Application not found.');
    header('Location: ' . BASE_URL . '/admin/applicants.php'); exit;
}

$olStmt = $pdo->prepare("SELECT * FROM olevel_results WHERE application_id=? ORDER BY sitting,subject");
$olStmt->execute([$appId]);
$olevels = $olStmt->fetchAll();

$docStmt = $pdo->prepare("SELECT * FROM documents WHERE application_id=? ORDER BY document_type");
$docStmt->execute([$appId]);
$documents = $docStmt->fetchAll();

$payStmt = $pdo->prepare("SELECT * FROM payments WHERE application_id=? ORDER BY created_at DESC");
$payStmt->execute([$appId]);
$payments = $payStmt->fetchAll();

$statuses = ['Pending','Under Review','Shortlisted','Admitted','Rejected','Deferred'];

$pageTitle = 'Review Application – ' . $app['ref_number'];
require_once dirname(__DIR__) . '/admin/partials/admin-header.php';
?>

<div class="admin-content">

    <div class="admin-page-header">
        <div>
            <a href="<?= BASE_URL ?>/admin/applicants.php" class="back-link">← Back to Applications</a>
            <h1>Review: <?= e($app['ref_number']) ?></h1>
            <p><?= e($app['programme_name']) ?> — <?= e($app['college_name']) ?></p>
        </div>
        <div class="admin-page-actions">
            <?= statusBadge($app['status']) ?>
            <a href="<?= BASE_URL ?>/print-slip.php?admin=1&id=<?= $appId ?>"
               class="btn btn-outline btn-sm" target="_blank">🖨 Print Slip</a>
        </div>
    </div>

    <?= getFlash() ?>

    <div class="review-layout">

        <!-- LEFT: Application Details -->
        <div class="review-main">

            <!-- Biodata -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Applicant Biodata</h3>
                </div>
                <div class="review-bio">
                    <div class="review-bio-photo">
                        <?php if ($app['passport_photo']): ?>
                        <img src="<?= UPLOAD_URL . e($app['passport_photo']) ?>" alt="Passport">
                        <?php else: ?>
                        <div class="bio-photo-ph">
                            <?= strtoupper(substr($app['firstname'],0,1).substr($app['surname'],0,1)) ?>
                        </div>
                        <?php endif; ?>
                        <div class="ref-tag"><?= e($app['ref_number']) ?></div>
                    </div>
                    <div class="review-bio-info">
                        <div class="review-grid-3">
                            <div class="rv"><span>Surname</span><strong><?= e($app['surname']) ?></strong></div>
                            <div class="rv"><span>First Name</span><strong><?= e($app['firstname']) ?></strong></div>
                            <div class="rv"><span>Middle Name</span><strong><?= e($app['middlename'] ?? '—') ?></strong></div>
                            <div class="rv"><span>Gender</span><strong><?= e($app['gender']) ?></strong></div>
                            <div class="rv"><span>Date of Birth</span><strong><?= formatDate($app['date_of_birth']) ?></strong></div>
                            <div class="rv"><span>Age</span><strong><?= calculateAge($app['date_of_birth']) ?> years</strong></div>
                            <div class="rv"><span>State of Origin</span><strong><?= e($app['state_of_origin']) ?></strong></div>
                            <div class="rv"><span>LGA</span><strong><?= e($app['lga']) ?></strong></div>
                            <div class="rv"><span>Nationality</span><strong><?= e($app['nationality']) ?></strong></div>
                            <div class="rv"><span>Phone</span><strong><?= e($app['phone']) ?></strong></div>
                            <div class="rv"><span>Email</span><strong><?= e($app['email']) ?></strong></div>
                            <div class="rv"><span>Religion</span><strong><?= e($app['religion'] ?? '—') ?></strong></div>
                        </div>
                        <div class="rv" style="margin-top:8px"><span>Address</span><strong><?= e($app['home_address']) ?></strong></div>
                    </div>
                </div>
            </div>

            <!-- Programme Details -->
            <div class="admin-card">
                <div class="admin-card-header"><h3>Programme Details</h3></div>
                <div class="review-grid-3">
                    <div class="rv"><span>College</span><strong><?= e($app['college_name']) ?> (<?= e($app['college_code']) ?>)</strong></div>
                    <div class="rv"><span>Programme</span><strong><?= e($app['programme_name']) ?></strong></div>
                    <div class="rv"><span>Qualification</span><strong><?= e($app['qualification']) ?></strong></div>
                    <div class="rv"><span>Session</span><strong><?= e($app['academic_session']) ?></strong></div>
                    <div class="rv"><span>Entry Mode</span><strong><?= e($app['entry_mode']) ?></strong></div>
                    <div class="rv"><span>App. Type</span><strong><?= e($app['application_type']) ?></strong></div>
                    <?php if ($app['jamb_reg_number']): ?>
                    <div class="rv"><span>JAMB Reg</span><strong><?= e($app['jamb_reg_number']) ?></strong></div>
                    <div class="rv"><span>JAMB Score</span><strong><?= e($app['jamb_score']) ?></strong></div>
                    <?php endif; ?>
                    <div class="rv"><span>Submitted</span><strong><?= $app['submitted_at'] ? formatDateTime($app['submitted_at']) : 'Not submitted' ?></strong></div>
                </div>
            </div>

            <!-- O'Level Results -->
            <?php if ($olevels): ?>
            <div class="admin-card">
                <div class="admin-card-header"><h3>O'Level Results (<?= count($olevels) ?> subjects)</h3></div>
                <table class="admin-table">
                    <thead><tr><th>Sitting</th><th>Exam</th><th>Year</th><th>Subject</th><th>Grade</th></tr></thead>
                    <tbody>
                        <?php foreach ($olevels as $r): ?>
                        <tr>
                            <td><?= e($r['sitting']) ?></td>
                            <td><?= e($r['exam_type']) ?></td>
                            <td><?= e($r['exam_year']) ?></td>
                            <td><?= e($r['subject']) ?></td>
                            <td><strong class="<?= in_array($r['grade'],['A1','B2','B3'])?'grade-good':'grade-pass' ?>"><?= e($r['grade']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Documents -->
            <div class="admin-card">
                <div class="admin-card-header"><h3>Uploaded Documents</h3></div>
                <?php if ($documents): ?>
                <div class="doc-review-grid">
                    <?php foreach ($documents as $doc): ?>
                    <div class="doc-review-item">
                        <div class="dri-type"><?= e($doc['document_type']) ?></div>
                        <div class="dri-file">
                            <?php
                            $isImage = in_array($doc['mime_type'],['image/jpeg','image/png','image/jpg']);
                            ?>
                            <?php if ($isImage): ?>
                            <a href="<?= UPLOAD_URL . e($doc['file_path']) ?>" target="_blank">
                                <img src="<?= UPLOAD_URL . e($doc['file_path']) ?>" alt="<?= e($doc['document_type']) ?>" class="doc-thumb">
                            </a>
                            <?php else: ?>
                            <a href="<?= UPLOAD_URL . e($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline">📄 View PDF</a>
                            <?php endif; ?>
                        </div>
                        <div class="dri-status">
                            <?php if ($doc['is_verified']): ?>
                            <span class="doc-status verified">✓ Verified</span>
                            <?php else: ?>
                            <form method="POST" action="" style="display:inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="verify_doc">
                                <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Verify</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="admin-empty">No documents uploaded.</div>
                <?php endif; ?>
            </div>

            <!-- Payments -->
            <div class="admin-card">
                <div class="admin-card-header"><h3>Payments</h3></div>
                <?php if ($payments): ?>
                <?php foreach ($payments as $pay): ?>
                <div class="pay-review-item">
                    <div class="pri-info">
                        <strong><?= e($pay['payment_type']) ?></strong> —
                        <?= formatMoney($pay['amount']) ?> via <?= e($pay['payment_method']) ?><br>
                        <small>Ref: <?= e($pay['transaction_ref']) ?> &nbsp;|&nbsp; <?= $pay['payment_date'] ? formatDate($pay['payment_date']) : '' ?> &nbsp;|&nbsp; Bank: <?= e($pay['bank_name'] ?: '—') ?></small>
                    </div>
                    <div class="pri-proof">
                        <?php if ($pay['proof_file']): ?>
                        <a href="<?= UPLOAD_URL . e($pay['proof_file']) ?>" target="_blank" class="btn btn-sm btn-outline">View Proof</a>
                        <?php endif; ?>
                    </div>
                    <div class="pri-action">
                        <?php if ($pay['status'] === 'Pending'): ?>
                        <form method="POST" action="" style="display:inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="verify_payment">
                            <input type="hidden" name="payment_id" value="<?= $pay['id'] ?>">
                            <input type="text" name="remark" class="form-control form-control-sm"
                                   placeholder="Remark (optional)" style="width:140px;display:inline-block">
                            <button type="submit" name="decision" value="approve"
                                    class="btn btn-sm btn-primary">✓ Approve</button>
                            <button type="submit" name="decision" value="reject"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Reject this payment?')">✕ Reject</button>
                        </form>
                        <?php else: ?>
                        <span class="pay-status pay-<?= strtolower($pay['status']) ?>"><?= e($pay['status']) ?></span>
                        <?php if ($pay['remark']): ?><br><small><?= e($pay['remark']) ?></small><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="admin-empty">No payment records.</div>
                <?php endif; ?>
            </div>

        </div><!-- /review-main -->

        <!-- RIGHT: Action Panel -->
        <div class="review-sidebar">

            <!-- Update Status -->
            <div class="admin-card">
                <div class="admin-card-header"><h3>Update Status</h3></div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_status">
                    <div class="form-group">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-control" required>
                            <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st ?>" <?= $app['status']===$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Remark</label>
                        <textarea name="admin_remark" class="form-control" rows="3"
                                  placeholder="Add a remark for the applicant..."><?= e($app['admin_remark'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full"
                            onclick="return confirm('Update application status?')">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Quick Info -->
            <div class="admin-card">
                <div class="admin-card-header"><h3>Quick Summary</h3></div>
                <div class="info-list">
                    <div class="il-item"><span>Status</span><?= statusBadge($app['status']) ?></div>
                    <div class="il-item"><span>College</span><strong><?= e($app['college_code']) ?></strong></div>
                    <div class="il-item"><span>App Fee</span><strong><?= $app['form_fee_paid']?'<span style="color:#1a6b4a">✓ Paid</span>':'<span style="color:#c9922a">Unpaid</span>' ?></strong></div>
                    <div class="il-item"><span>Accept Fee</span><strong><?= $app['acceptance_paid']?'<span style="color:#1a6b4a">✓ Paid</span>':'<span style="color:#c9922a">Unpaid</span>' ?></strong></div>
                    <div class="il-item"><span>Docs</span><strong><?= count($documents) ?> uploaded</strong></div>
                    <div class="il-item"><span>Submitted</span><strong><?= $app['step_submitted']?'<span style="color:#1a6b4a">✓ Yes</span>':'<span style="color:#c9922a">No</span>' ?></strong></div>
                    <div class="il-item"><span>Registered</span><strong><?= formatDate($app['created_at']) ?></strong></div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="admin-card">
                <div class="admin-card-header"><h3>Navigate</h3></div>
                <div class="quick-links">
                    <a href="<?= BASE_URL ?>/admin/applicants.php" class="quick-link">← All Applications</a>
                    <a href="<?= BASE_URL ?>/admin/applicants.php?status=Pending" class="quick-link">⏳ Pending</a>
                    <a href="<?= BASE_URL ?>/admin/applicants.php?status=Admitted" class="quick-link">🎓 Admitted</a>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="quick-link">📊 Dashboard</a>
                </div>
            </div>

        </div><!-- /review-sidebar -->
    </div><!-- /review-layout -->
</div>

<?php require_once dirname(__DIR__) . '/admin/partials/admin-footer.php'; ?>
