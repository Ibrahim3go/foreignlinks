<?php
// ============================================================
//  Foreign Links Campus – Payment Page
//  /payment.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireApplicantLogin();

$applicantId = (int)$_SESSION['applicant_id'];
$application = getActiveApplication($pdo, $applicantId);

if (!$application) {
    setFlash('info','Please complete your application form first.');
    header('Location: ' . BASE_URL . '/apply.php'); exit;
}

// Load existing payments
$payStmt = $pdo->prepare("SELECT * FROM payments WHERE application_id=? ORDER BY created_at DESC");
$payStmt->execute([$application['id']]);
$payments = $payStmt->fetchAll();

$collegeFees  = FEES[$application['college_code']] ?? FEES['COE'];
$paymentTypes = ['Application Fee','Acceptance Fee','Tuition Fee','Entrance Exam Fee'];

$pageTitle = 'Payment';
$bodyClass = 'page-payment';
require_once 'includes/header.php';
?>

<section class="inner-section">
<div class="container">

    <div class="inner-header">
        <a href="<?= BASE_URL ?>/dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Payment Portal</h1>
        <p><?= e($application['programme_name']) ?> — <?= e($application['college_name']) ?></p>
    </div>

    <?= getFlash() ?>

    <div class="payment-grid">

        <!-- LEFT: Submit Payment -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3>Submit Payment Proof</h3>
            </div>

            <div class="bank-info-box">
                <h4>💳 Bank Transfer Details</h4>
                <div class="bank-details">
                    <div class="bd-row"><span>Bank</span><strong><?= BANK_NAME ?></strong></div>
                    <div class="bd-row"><span>Account Name</span><strong><?= ACCOUNT_NAME ?></strong></div>
                    <div class="bd-row"><span>Account Number</span>
                        <strong class="acct-num"><?= ACCOUNT_NUMBER ?></strong>
                        <button class="copy-btn" onclick="copyText('<?= ACCOUNT_NUMBER ?>')">Copy</button>
                    </div>
                </div>
                <p class="bank-note">
                    Make your payment via bank transfer, then complete the form below
                    with your transaction details and upload proof of payment.
                </p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/process/payment-verify.php"
                  enctype="multipart/form-data" class="apply-form" novalidate>
                <?= csrfField() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Payment Type <span class="required">*</span></label>
                        <select name="payment_type" class="form-control" required
                                onchange="updateAmount(this.value)">
                            <option value="">— Select —</option>
                            <?php foreach ($paymentTypes as $pt): ?>
                            <option value="<?= $pt ?>"><?= $pt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount Paid (₦) <span class="required">*</span></label>
                        <input type="number" name="amount" id="amountField"
                               class="form-control" min="0" step="0.01"
                               placeholder="e.g. 5000" required>
                        <span class="form-hint" id="feeHint"></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Payment Method <span class="required">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Online Payment">Online Payment (Paystack/Flutterwave)</option>
                            <option value="Cash">Cash at School</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control"
                               placeholder="e.g. First Bank, GTBank">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Transaction Reference <span class="required">*</span></label>
                        <input type="text" name="transaction_ref" class="form-control"
                               placeholder="Teller No. / Paystack Ref" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Date <span class="required">*</span></label>
                        <input type="date" name="payment_date" class="form-control"
                               max="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Proof of Payment <span class="required">*</span></label>
                    <div class="upload-box" onclick="document.getElementById('proof_file').click()">
                        <div class="upload-preview" id="proofPreview">
                            <span class="upload-icon">📎</span>
                            <span>Click to upload teller / payment screenshot</span>
                            <small>JPG, PNG or PDF · Max 5MB</small>
                        </div>
                        <input type="file" id="proof_file" name="proof_file"
                               accept="image/jpeg,image/png,application/pdf"
                               style="display:none"
                               onchange="previewProof(this)">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Submit Payment for Verification →
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT: Fee Schedule + History -->
        <div class="dash-sidebar">

            <!-- Fee Schedule -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Fee Schedule</h3></div>
                <div class="fee-schedule">
                    <div class="fs-item">
                        <span>Application Fee</span>
                        <strong><?= formatMoney($collegeFees['application']) ?></strong>
                        <span class="fs-status <?= $application['form_fee_paid'] ? 'paid' : 'unpaid' ?>">
                            <?= $application['form_fee_paid'] ? '✓ Paid' : 'Unpaid' ?>
                        </span>
                    </div>
                    <div class="fs-item">
                        <span>Acceptance Fee</span>
                        <strong><?= formatMoney($collegeFees['acceptance']) ?></strong>
                        <span class="fs-status <?= $application['acceptance_paid'] ? 'paid' : 'unpaid' ?>">
                            <?= $application['acceptance_paid'] ? '✓ Paid' : 'Unpaid' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Payment History</h3></div>
                <?php if ($payments): ?>
                <div class="pay-history">
                    <?php foreach ($payments as $pay): ?>
                    <div class="ph-item">
                        <div class="ph-top">
                            <strong><?= e($pay['payment_type']) ?></strong>
                            <span class="pay-status pay-<?= strtolower($pay['status']) ?>">
                                <?= e($pay['status']) ?>
                            </span>
                        </div>
                        <div class="ph-detail">
                            <span><?= formatMoney($pay['amount']) ?></span>
                            <span><?= e($pay['bank_name'] ?: $pay['payment_method']) ?></span>
                            <span><?= $pay['payment_date'] ? formatDate($pay['payment_date']) : '' ?></span>
                        </div>
                        <?php if ($pay['transaction_ref']): ?>
                        <div class="ph-ref">Ref: <?= e($pay['transaction_ref']) ?></div>
                        <?php endif; ?>
                        <?php if ($pay['status'] === 'Rejected' && $pay['remark']): ?>
                        <div class="ph-remark">❌ <?= e($pay['remark']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <span>💳</span><p>No payment records yet.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div><!-- /payment-grid -->
</div>
</section>

<script>
const fees = {
    'Application Fee':   <?= $collegeFees['application'] ?>,
    'Acceptance Fee':    <?= $collegeFees['acceptance'] ?>,
    'Tuition Fee':       0,
    'Entrance Exam Fee': 7000,
};
function updateAmount(type) {
    const field   = document.getElementById('amountField');
    const hint    = document.getElementById('feeHint');
    const amount  = fees[type] || 0;
    if (amount) {
        field.value = amount;
        hint.textContent = 'Expected: ₦' + amount.toLocaleString();
    } else {
        hint.textContent = '';
    }
}
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Account number copied: ' + text);
    });
}
function previewProof(input) {
    const preview = document.getElementById('proofPreview');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type === 'application/pdf') {
            preview.innerHTML = `<span class="upload-icon">📄</span><span>${file.name}</span><small>${(file.size/1024).toFixed(0)} KB</small>`;
        } else {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `<img src="${e.target.result}" class="preview-img" alt="Proof">`;
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
