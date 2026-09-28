<?php
// ============================================================
//  Foreign Links Campus – Print Admission Slip
//  /print-slip.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireApplicantLogin();

$applicantId = (int)$_SESSION['applicant_id'];
$application = getActiveApplication($pdo, $applicantId);

if (!$application || !$application['step_submitted']) {
    setFlash('info', 'You must submit your application before printing a slip.');
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$apStmt = $pdo->prepare("SELECT * FROM applicants WHERE id = ? LIMIT 1");
$apStmt->execute([$applicantId]);
$applicant = $apStmt->fetch();

$olStmt = $pdo->prepare("SELECT * FROM olevel_results WHERE application_id=? ORDER BY sitting, subject");
$olStmt->execute([$application['id']]);
$olevels = $olStmt->fetchAll();

$payStmt = $pdo->prepare("SELECT * FROM payments WHERE application_id=? AND status='Verified' ORDER BY created_at");
$payStmt->execute([$application['id']]);
$payments = $payStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admission Slip – <?= e($applicant['ref_number']) ?> | <?= SITE_NAME ?></title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Times New Roman',serif;font-size:13px;color:#111;background:#f0f0f0}
  .print-bar{text-align:center;padding:14px;background:#0d3b2e;display:flex;justify-content:center;gap:12px;flex-wrap:wrap}
  .print-bar button{padding:9px 24px;border-radius:6px;font-size:14px;font-weight:700;cursor:pointer;border:2px solid #fff}
  .btn-print{background:#c9922a;color:#fff;border-color:#c9922a}
  .btn-back{background:transparent;color:#fff}
  .slip{max-width:760px;margin:20px auto;padding:24px 30px;background:#fff;border:2px solid #0d3b2e}

  /* Header */
  .slip-head{display:flex;align-items:center;gap:16px;border-bottom:3px double #0d3b2e;padding-bottom:14px;margin-bottom:14px}
  .slip-logo{width:72px;height:72px;object-fit:contain;flex-shrink:0}
  .slip-inst{flex:1;text-align:center}
  .slip-inst h1{font-size:17px;color:#0d3b2e;text-transform:uppercase;letter-spacing:1.5px;line-height:1.2}
  .slip-inst p{font-size:11px;color:#444;line-height:1.6;margin-top:3px}
  .slip-inst .accred{font-size:10px;color:#1a6b4a;font-weight:700;margin-top:4px;text-transform:uppercase;letter-spacing:1px}
  .slip-photo{width:88px;height:104px;border:2px solid #0d3b2e;object-fit:cover;flex-shrink:0}
  .slip-photo-ph{width:88px;height:104px;border:2px solid #0d3b2e;background:#e8f5ee;display:flex;align-items:center;justify-content:center;font-size:10px;color:#1a6b4a;text-align:center;flex-shrink:0}

  /* Title bar */
  .slip-title{text-align:center;background:#0d3b2e;color:#fff;padding:8px 12px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:14px}
  .slip-title.admitted{background:#1a6b4a}

  /* Status */
  .status-box{text-align:center;padding:9px;border:2px solid;border-radius:4px;margin-bottom:14px;font-size:14px;font-weight:700}
  .s-pending{border-color:#c9922a;color:#c9922a;background:#fffbf0}
  .s-admitted{border-color:#1a6b4a;color:#1a6b4a;background:#e8f5ee}
  .s-shortlisted{border-color:#2980b9;color:#2980b9;background:#ebf5fb}
  .s-under-review{border-color:#7d3c98;color:#7d3c98;background:#f5eef8}
  .s-rejected{border-color:#c0392b;color:#c0392b;background:#fdedec}

  /* Ref box */
  .ref-box{border:2px solid #0d3b2e;padding:10px 16px;text-align:center;margin-bottom:14px;background:#fffef5}
  .ref-number{font-size:22px;font-weight:700;color:#0d3b2e;letter-spacing:3px;font-family:monospace}
  .ref-label{font-size:10px;color:#666;margin-top:2px;text-transform:uppercase;letter-spacing:1px}

  /* Section title */
  .sec-title{background:#e8f5ee;color:#0d3b2e;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:1px;padding:5px 10px;border-left:4px solid #1a6b4a;margin-bottom:0}

  /* Info table */
  table.t-info{width:100%;border-collapse:collapse;margin-bottom:12px}
  table.t-info td{padding:6px 10px;border:1px solid #d5d5d5;font-size:12.5px;vertical-align:top}
  table.t-info td:first-child{font-weight:700;color:#0d3b2e;background:#fafafa;width:34%;white-space:nowrap}

  /* O'Level table */
  table.t-ol{width:100%;border-collapse:collapse;margin-bottom:12px;font-size:12px}
  table.t-ol th{background:#0d3b2e;color:#fff;padding:6px 8px;text-align:left;font-size:11px}
  table.t-ol td{padding:5px 8px;border-bottom:1px solid #e0e0e0}
  table.t-ol tr:nth-child(even) td{background:#fafafa}

  /* Declaration */
  .declaration{border:1px solid #ccc;padding:11px 14px;font-size:11.5px;color:#444;line-height:1.8;margin-bottom:14px;background:#fffef8}

  /* Signature */
  .sig-row{display:flex;justify-content:space-between;margin-top:18px;padding-top:14px;border-top:1px solid #bbb;flex-wrap:wrap;gap:12px}
  .sig-box{text-align:center;flex:1;min-width:140px}
  .sig-line{width:160px;border-bottom:1px solid #333;margin:0 auto 5px;height:28px}
  .sig-label{font-size:11px;color:#555}

  /* Footer */
  .slip-footer{text-align:center;margin-top:14px;padding-top:10px;border-top:2px solid #0d3b2e;font-size:10.5px;color:#666;line-height:1.8}

  @media print{
    body{background:#fff}
    .print-bar{display:none!important}
    .slip{margin:0;border:2px solid #0d3b2e;max-width:100%}
    @page{margin:10mm}
  }
</style>
</head>
<body>

<div class="print-bar">
    <button class="btn-print" onclick="window.print()">🖨 Print / Save as PDF</button>
    <button class="btn-back"  onclick="location.href='<?= BASE_URL ?>/dashboard.php'">← Dashboard</button>
</div>

<div class="slip">

    <!-- Header -->
    <div class="slip-head">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" class="slip-logo"
             onerror="this.style.display='none'">
        <div class="slip-inst">
            <h1><?= SITE_NAME ?></h1>
            <p>Osogbo Road, Moro, Ife-North LGA, Osun State, Nigeria</p>
            <p>Tel: <?= SITE_PHONE ?> &nbsp;|&nbsp; <?= SITE_EMAIL ?></p>
            <div class="accred">Accredited by NBTE &amp; NCCE &nbsp;|&nbsp; <?= SITE_SESSION ?> Session</div>
        </div>
        <?php if (!empty($applicant['passport_photo'])): ?>
        <img src="<?= UPLOAD_URL . e($applicant['passport_photo']) ?>" class="slip-photo" alt="Passport">
        <?php else: ?>
        <div class="slip-photo-ph">Passport<br>Photo</div>
        <?php endif; ?>
    </div>

    <!-- Title -->
    <div class="slip-title <?= $application['status'] === 'Admitted' ? 'admitted' : '' ?>">
        <?= $application['status'] === 'Admitted' ? 'Provisional Admission Letter' : 'Application Acknowledgement Slip' ?>
    </div>

    <!-- Status -->
    <?php
    $sClass = [
        'Pending'      => 's-pending',
        'Under Review' => 's-under-review',
        'Shortlisted'  => 's-shortlisted',
        'Admitted'     => 's-admitted',
        'Rejected'     => 's-rejected',
    ][$application['status']] ?? 's-pending';
    ?>
    <div class="status-box <?= $sClass ?>">
        Application Status: <?= e($application['status']) ?>
        <?= $application['status'] === 'Admitted' ? ' &nbsp; 🎓 &nbsp; Congratulations!' : '' ?>
    </div>

    <!-- Reference -->
    <div class="ref-box">
        <div class="ref-number"><?= e($applicant['ref_number']) ?></div>
        <div class="ref-label">Application Reference Number — Keep this safe</div>
    </div>

    <!-- Biodata -->
    <div class="sec-title">Applicant Biodata</div>
    <table class="t-info">
        <tr><td>Full Name</td><td><?= e(strtoupper($applicant['surname'].', '.$applicant['firstname'].' '.($applicant['middlename']??''))) ?></td></tr>
        <tr><td>Gender</td><td><?= e($applicant['gender']) ?></td></tr>
        <tr><td>Date of Birth</td><td><?= formatDate($applicant['date_of_birth']) ?></td></tr>
        <tr><td>State of Origin</td><td><?= e($applicant['state_of_origin']) ?> &nbsp;|&nbsp; LGA: <?= e($applicant['lga']) ?></td></tr>
        <tr><td>Nationality</td><td><?= e($applicant['nationality']) ?></td></tr>
        <tr><td>Phone / Email</td><td><?= e($applicant['phone']) ?> &nbsp;|&nbsp; <?= e($applicant['email']) ?></td></tr>
        <tr><td>Home Address</td><td><?= e($applicant['home_address']) ?></td></tr>
    </table>

    <!-- Programme -->
    <div class="sec-title">Programme Details</div>
    <table class="t-info">
        <tr><td>College</td><td><?= e($application['college_name']) ?> (<?= e($application['college_code']) ?>)</td></tr>
        <tr><td>Programme</td><td><?= e($application['programme_name']) ?></td></tr>
        <tr><td>Qualification</td><td><?= e($application['qualification']) ?></td></tr>
        <tr><td>Session</td><td><?= e($application['academic_session']) ?></td></tr>
        <tr><td>Entry Mode</td><td><?= e($application['entry_mode']) ?> &nbsp;|&nbsp; Type: <?= e($application['application_type']) ?></td></tr>
        <?php if ($application['jamb_reg_number']): ?>
        <tr><td>JAMB Reg. No.</td><td><?= e($application['jamb_reg_number']) ?> &nbsp;|&nbsp; Score: <?= e($application['jamb_score']) ?></td></tr>
        <?php endif; ?>
        <tr><td>Date Submitted</td><td><?= $application['submitted_at'] ? formatDateTime($application['submitted_at']) : 'N/A' ?></td></tr>
    </table>

    <!-- O'Level -->
    <?php if ($olevels): ?>
    <div class="sec-title">O'Level Results</div>
    <table class="t-ol">
        <thead><tr><th>Sitting</th><th>Exam</th><th>Year</th><th>Subject</th><th>Grade</th></tr></thead>
        <tbody>
        <?php foreach ($olevels as $r): ?>
        <tr><td><?= e($r['sitting']) ?></td><td><?= e($r['exam_type']) ?></td><td><?= e($r['exam_year']) ?></td><td><?= e($r['subject']) ?></td><td><strong><?= e($r['grade']) ?></strong></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Payments -->
    <?php if ($payments): ?>
    <div class="sec-title">Verified Payments</div>
    <table class="t-info">
        <?php foreach ($payments as $p): ?>
        <tr><td><?= e($p['payment_type']) ?></td><td><?= formatMoney($p['amount']) ?> &nbsp; ✓ Verified &nbsp;|&nbsp; Ref: <?= e($p['transaction_ref']) ?> &nbsp;|&nbsp; <?= formatDate($p['payment_date']) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- Declaration -->
    <div class="declaration">
        <strong>Declaration:</strong> I, <?= e(strtoupper($applicant['firstname'].' '.$applicant['surname'])) ?>, hereby declare
        that all information provided in this application is true, complete and accurate to the best of my knowledge.
        I understand that any misrepresentation may result in the cancellation of my admission or studentship.
        This slip was generated on <strong><?= date('d F Y \a\t g:i A') ?></strong> and is subject to confirmation
        by the Admissions Office of <?= SITE_NAME ?>.
    </div>

    <!-- Signatures -->
    <div class="sig-row">
        <div class="sig-box"><div class="sig-line"></div><div class="sig-label">Applicant's Signature &amp; Date</div></div>
        <div class="sig-box"><div class="sig-line"></div><div class="sig-label">Admissions Officer &amp; Date</div></div>
        <div class="sig-box"><div class="sig-line"></div><div class="sig-label">Provost / Registrar &amp; Stamp</div></div>
    </div>

    <!-- Footer -->
    <div class="slip-footer">
        <p><?= SITE_NAME ?> &nbsp;|&nbsp; Osogbo Road, Moro, Ife-North LGA, Osun State, Nigeria</p>
        <p><?= SITE_PHONE ?> &nbsp;|&nbsp; <?= SITE_EMAIL ?></p>
        <p><em>This is a computer-generated document. Visit the admissions office to verify authenticity.</em></p>
    </div>

</div>
</body>
</html>
