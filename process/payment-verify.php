<?php
// ============================================================
//  Foreign Links Campus – Payment Submission Handler
//  /process/payment-verify.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireApplicantLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/payment.php'); exit;
}
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error','Invalid request token.');
    header('Location: ' . BASE_URL . '/payment.php'); exit;
}

$applicantId  = (int)$_SESSION['applicant_id'];
$application  = getActiveApplication($pdo, $applicantId);

if (!$application) {
    setFlash('error','No application found.');
    header('Location: ' . BASE_URL . '/dashboard.php'); exit;
}

$paymentType  = clean($_POST['payment_type']   ?? '');
$amount       = (float)($_POST['amount']       ?? 0);
$method       = clean($_POST['payment_method'] ?? 'Bank Transfer');
$bankName     = clean($_POST['bank_name']      ?? '');
$txRef        = clean($_POST['transaction_ref']?? '');
$payDate      = clean($_POST['payment_date']   ?? '');

if (!$paymentType || $amount <= 0 || !$txRef || !$payDate) {
    setFlash('error','Please fill all required payment fields.');
    header('Location: ' . BASE_URL . '/payment.php'); exit;
}

// Upload proof file
$proofPath = null;
if (!empty($_FILES['proof_file']['name'])) {
    $allowed = array_merge(ALLOWED_IMAGE_TYPES, ['application/pdf']);
    $upload  = uploadFile($_FILES['proof_file'], 'payments', $allowed);
    if (!$upload['success']) {
        setFlash('error', $upload['error']);
        header('Location: ' . BASE_URL . '/payment.php'); exit;
    }
    $proofPath = $upload['path'];
}

// Check for duplicate transaction reference
$dupChk = $pdo->prepare("SELECT id FROM payments WHERE transaction_ref=? LIMIT 1");
$dupChk->execute([$txRef]);
if ($dupChk->fetch()) {
    setFlash('error','This transaction reference has already been submitted.');
    header('Location: ' . BASE_URL . '/payment.php'); exit;
}

$pdo->prepare("INSERT INTO payments
    (application_id,applicant_id,payment_type,amount,
     payment_method,transaction_ref,bank_name,proof_file,payment_date,status)
    VALUES (?,?,?,?,?,?,?,?,?,'Pending')")
    ->execute([$application['id'],$applicantId,$paymentType,
               $amount,$method,$txRef,$bankName,$proofPath,$payDate]);

sendPortalNotification($pdo, $applicantId,
    'Payment Submitted – Awaiting Verification',
    "Your <strong>{$paymentType}</strong> of <strong>" . formatMoney($amount) . "</strong> has been received and is awaiting verification by the admissions office. Ref: {$txRef}");

auditLog($pdo,'Applicant',$applicantId,'Payment Submit',
         "{$paymentType} | ₦{$amount} | Ref: {$txRef}");

setFlash('success','Payment submitted successfully! It will be verified within 24 hours.');
header('Location: ' . BASE_URL . '/payment.php');
exit;
