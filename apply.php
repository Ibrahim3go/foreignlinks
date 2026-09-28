<?php
// ============================================================
//  Foreign Links Campus – Multi-Step Application Form
//  /apply.php
// ============================================================
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$pageTitle = 'Apply Now';
$bodyClass = 'page-apply';

$step = (int)($_GET['step'] ?? 1);
if ($step < 1 || $step > 5) $step = 1;

$application = null;
if (isApplicantLoggedIn()) {
    $application = getActiveApplication($pdo, $_SESSION['applicant_id']);
    if ($application && $step === 1) $step = 2;
}

if (!isApplicantLoggedIn() && $step > 1) {
    setFlash('info', 'Please create an account or login first.');
    header('Location: ' . BASE_URL . '/apply.php?step=1');
    exit;
}

$colleges   = getColleges($pdo);
$session    = getCurrentSession($pdo);
$states     = getNigerianStates();

$old    = $_SESSION['reg_old']    ?? [];
$errors = $_SESSION['reg_errors'] ?? [];
unset($_SESSION['reg_old'], $_SESSION['reg_errors']);

$stepLabels = [
    1 => 'Account',
    2 => 'Programme',
    3 => 'Personal Details',
    4 => 'Academic',
    5 => 'Review & Submit',
];

require_once 'includes/header.php';
?>

<section class="apply-section">
<div class="container">

    <div class="apply-header">
        <h1 class="apply-title"><?= SITE_SESSION ?> Admission Application</h1>
        <p class="apply-subtitle"><?= SITE_NAME ?> &mdash; Moro, Ife-North, Osun State</p>
    </div>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <?php foreach ($stepLabels as $num => $label): ?>
        <div class="step-ind-item <?= $num < $step ? 'done' : ($num === $step ? 'active' : '') ?>">
            <div class="step-ind-circle"><?= $num < $step ? '✓' : $num ?></div>
            <span class="step-ind-label"><?= $label ?></span>
        </div>
        <?php if ($num < count($stepLabels)): ?>
        <div class="step-ind-line <?= $num < $step ? 'done' : '' ?>"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?= getFlash() ?>

<!-- ══════════════════════════════════════════════════════════
     STEP 1 – CREATE ACCOUNT
══════════════════════════════════════════════════════════ -->
<?php if ($step === 1): ?>
<div class="apply-card">
    <div class="apply-card-header">
        <span class="step-tag">Step 1 of 5</span>
        <h2>Create Your Account</h2>
        <p>Fill in your details to register and begin your application.</p>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-error">
        <span class="alert-icon">✕</span>
        <div><?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>

    <?php if (isApplicantLoggedIn()): ?>
    <div class="alert alert-info">
        <span class="alert-icon">ℹ</span>
        <span>You are logged in as <strong><?= e($_SESSION['applicant_name']) ?></strong>.
            <a href="<?= BASE_URL ?>/apply.php?step=2">Continue your application →</a>
        </span>
    </div>
    <?php else: ?>

    <form method="POST" action="<?= BASE_URL ?>/process/register.php" class="apply-form" novalidate>
        <?= csrfField() ?>

        <div class="form-section-title">Personal Information</div>
        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label">Surname <span class="required">*</span></label>
                <input type="text" name="surname" class="form-control"
                       value="<?= e($old['surname'] ?? '') ?>" placeholder="e.g. Adeyemi" required>
            </div>
            <div class="form-group">
                <label class="form-label">First Name <span class="required">*</span></label>
                <input type="text" name="firstname" class="form-control"
                       value="<?= e($old['firstname'] ?? '') ?>" placeholder="e.g. Fatimah" required>
            </div>
            <div class="form-group">
                <label class="form-label">Middle Name</label>
                <input type="text" name="middlename" class="form-control"
                       value="<?= e($old['middlename'] ?? '') ?>" placeholder="Optional">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Gender <span class="required">*</span></label>
                <select name="gender" class="form-control" required>
                    <option value="">— Select —</option>
                    <?php foreach (['Male','Female','Other'] as $g): ?>
                    <option value="<?= $g ?>" <?= ($old['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth <span class="required">*</span></label>
                <input type="date" name="dob" class="form-control"
                       value="<?= e($old['dob'] ?? '') ?>"
                       max="<?= date('Y-m-d', strtotime('-15 years')) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">State of Origin <span class="required">*</span></label>
                <select name="state_of_origin" class="form-control" required>
                    <option value="">— Select State —</option>
                    <?php foreach ($states as $st): ?>
                    <option value="<?= e($st) ?>" <?= ($old['state'] ?? '') === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">LGA <span class="required">*</span></label>
                <input type="text" name="lga" class="form-control"
                       value="<?= e($old['lga'] ?? '') ?>" placeholder="Local Government Area" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Home Address <span class="required">*</span></label>
            <textarea name="home_address" class="form-control" rows="2"
                      placeholder="Full residential address" required><?= e($old['address'] ?? '') ?></textarea>
        </div>

        <div class="form-section-title">Contact &amp; Login Details</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="<?= e($old['email'] ?? '') ?>" placeholder="your@email.com" required>
                <span class="form-hint">This will be your login email.</span>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number <span class="required">*</span></label>
                <input type="tel" name="phone" class="form-control"
                       value="<?= e($old['phone'] ?? '') ?>" placeholder="08012345678" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <div class="input-password-wrap">
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Min 8 chars, 1 uppercase, 1 number" required>
                    <button type="button" class="toggle-password" onclick="togglePw('password')">👁</button>
                </div>
                <div id="pwStrength" class="password-strength"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password <span class="required">*</span></label>
                <div class="input-password-wrap">
                    <input type="password" id="password2" name="password2" class="form-control"
                           placeholder="Repeat your password" required>
                    <button type="button" class="toggle-password" onclick="togglePw('password2')">👁</button>
                </div>
                <div id="pwMatch" class="form-error" style="display:none">Passwords do not match.</div>
            </div>
        </div>

        <div class="form-group">
            <label class="check-label">
                <input type="checkbox" name="agree" required>
                <span>I confirm all information is accurate and I agree to the terms and conditions of <?= SITE_NAME ?>.</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Create Account &amp; Continue →</button>
            <span class="form-action-note">Already have an account? <a href="<?= BASE_URL ?>/login.php">Login here</a></span>
        </div>
    </form>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     STEP 2 – CHOOSE PROGRAMME
══════════════════════════════════════════════════════════ -->
<?php elseif ($step === 2): ?>
<div class="apply-card">
    <div class="apply-card-header">
        <span class="step-tag">Step 2 of 5</span>
        <h2>Choose Your Programme</h2>
        <p>Select the college and programme you wish to apply for.</p>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/process/submit-application.php" class="apply-form" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="form_step" value="2">

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">College / School <span class="required">*</span></label>
                <select id="college_id" name="college_id" class="form-control" required
                        onchange="loadProgrammes(this.value, this)">
                    <option value="">— Select College —</option>
                    <?php foreach ($colleges as $col): ?>
                    <option value="<?= $col['id'] ?>" data-code="<?= e($col['code']) ?>"
                        <?= ($application['college_id'] ?? '') == $col['id'] ? 'selected' : '' ?>>
                        <?= e($col['name']) ?> (<?= e($col['code']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Programme <span class="required">*</span></label>
                <select id="programme_id" name="programme_id" class="form-control" required>
                    <option value="">— Select College First —</option>
                </select>
                <span class="form-hint" id="progFeeHint"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Application Type <span class="required">*</span></label>
                <select name="application_type" class="form-control" required>
                    <option value="Fresh">Fresh Applicant (UTME)</option>
                    <option value="Direct Entry">Direct Entry</option>
                    <option value="Transfer">Transfer Student</option>
                    <option value="International">International Applicant</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Entry Mode <span class="required">*</span></label>
                <select name="entry_mode" class="form-control" required>
                    <option value="UTME">UTME</option>
                    <option value="Direct Entry">Direct Entry</option>
                    <option value="HND">HND</option>
                    <option value="Transfer">Transfer</option>
                    <option value="Entrance Exam">Entrance Exam (Nursing)</option>
                    <option value="International">International</option>
                </select>
            </div>
        </div>

        <!-- Foreign University fields – shown only for FUL -->
        <div id="foreignFields" style="display:none">
            <div class="form-section-title">Foreign University Details</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Destination Country <span class="required">*</span></label>
                    <select name="destination_country" class="form-control">
                        <option value="">— Select Country —</option>
                        <?php foreach (['Ghana','Kenya','South Africa','Poland','United Kingdom','Germany','Canada','United States','Australia'] as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Preferred University</label>
                    <input type="text" name="foreign_university" class="form-control"
                           placeholder="e.g. University of Ghana">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>/apply.php?step=1" class="btn btn-outline">← Back</a>
            <button type="submit" class="btn btn-primary btn-lg">Save &amp; Continue →</button>
        </div>
    </form>
</div>

<!-- ══════════════════════════════════════════════════════════
     STEP 3 – PERSONAL DETAILS
══════════════════════════════════════════════════════════ -->
<?php elseif ($step === 3):
    if (!$application) {
        setFlash('info','Please complete Step 2 first.');
        header('Location: ' . BASE_URL . '/apply.php?step=2'); exit;
    }
    $ap = $pdo->prepare("SELECT * FROM applicants WHERE id = ? LIMIT 1");
    $ap->execute([$_SESSION['applicant_id']]);
    $apData = $ap->fetch();
?>
<div class="apply-card">
    <div class="apply-card-header">
        <span class="step-tag">Step 3 of 5</span>
        <h2>Personal Details</h2>
        <p>Review and complete your personal information, then upload your passport photo.</p>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/process/submit-application.php"
          class="apply-form" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="form_step" value="3">

        <div class="form-section-title">Passport Photograph</div>
        <div class="form-group">
            <label class="form-label">Upload Passport Photo <span class="required">*</span></label>
            <div class="upload-box" onclick="document.getElementById('passport_photo').click()">
                <div class="upload-preview" id="passportPreview">
                    <?php if (!empty($apData['passport_photo'])): ?>
                    <img src="<?= UPLOAD_URL . e($apData['passport_photo']) ?>"
                         alt="Passport" class="preview-img">
                    <?php else: ?>
                    <span class="upload-icon">📷</span>
                    <span>Click to upload passport photo</span>
                    <small>JPG or PNG · Max 2MB · White background preferred</small>
                    <?php endif; ?>
                </div>
                <input type="file" id="passport_photo" name="passport_photo"
                       accept="image/jpeg,image/png" style="display:none"
                       onchange="previewImage(this,'passportPreview')">
            </div>
        </div>

        <div class="form-section-title">Bio Data</div>
        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label">Surname <span class="required">*</span></label>
                <input type="text" name="surname" class="form-control"
                       value="<?= e($apData['surname']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">First Name <span class="required">*</span></label>
                <input type="text" name="firstname" class="form-control"
                       value="<?= e($apData['firstname']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Middle Name</label>
                <input type="text" name="middlename" class="form-control"
                       value="<?= e($apData['middlename'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Gender <span class="required">*</span></label>
                <select name="gender" class="form-control" required>
                    <?php foreach (['Male','Female','Other'] as $g): ?>
                    <option value="<?= $g ?>" <?= $apData['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth <span class="required">*</span></label>
                <input type="date" name="dob" class="form-control"
                       value="<?= e($apData['date_of_birth']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Religion</label>
                <select name="religion" class="form-control">
                    <option value="">— Select —</option>
                    <?php foreach (['Christianity','Islam','Traditional','Other'] as $r): ?>
                    <option value="<?= $r ?>" <?= ($apData['religion'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nationality <span class="required">*</span></label>
                <input type="text" name="nationality" class="form-control"
                       value="<?= e($apData['nationality']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">State of Origin <span class="required">*</span></label>
                <select name="state_of_origin" class="form-control" required>
                    <?php foreach ($states as $st): ?>
                    <option value="<?= e($st) ?>" <?= $apData['state_of_origin'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">LGA <span class="required">*</span></label>
                <input type="text" name="lga" class="form-control"
                       value="<?= e($apData['lga']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Home Address <span class="required">*</span></label>
            <textarea name="home_address" class="form-control" rows="2" required><?= e($apData['home_address']) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Phone Number <span class="required">*</span></label>
            <input type="tel" name="phone" class="form-control"
                   value="<?= e($apData['phone']) ?>" required>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>/apply.php?step=2" class="btn btn-outline">← Back</a>
            <button type="submit" class="btn btn-primary btn-lg">Save &amp; Continue →</button>
        </div>
    </form>
</div>

<!-- ══════════════════════════════════════════════════════════
     STEP 4 – ACADEMIC BACKGROUND
══════════════════════════════════════════════════════════ -->
<?php elseif ($step === 4):
    if (!$application) {
        setFlash('info','Please complete earlier steps first.');
        header('Location: ' . BASE_URL . '/apply.php?step=2'); exit;
    }
    $olStmt = $pdo->prepare("SELECT * FROM olevel_results WHERE application_id = ? ORDER BY sitting, subject");
    $olStmt->execute([$application['id']]);
    $olevels = $olStmt->fetchAll();
?>
<div class="apply-card">
    <div class="apply-card-header">
        <span class="step-tag">Step 4 of 5</span>
        <h2>Academic Background</h2>
        <p>Enter your JAMB details and O'Level results.</p>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/process/submit-application.php" class="apply-form" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="form_step" value="4">

        <div class="form-section-title">JAMB Details</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">JAMB Registration Number</label>
                <input type="text" name="jamb_reg_number" class="form-control"
                       value="<?= e($application['jamb_reg_number'] ?? '') ?>"
                       placeholder="e.g. 21234567AB">
                <span class="form-hint">Leave blank for Direct Entry / Entrance Exam applicants.</span>
            </div>
            <div class="form-group">
                <label class="form-label">JAMB Score</label>
                <input type="number" name="jamb_score" class="form-control"
                       min="0" max="400"
                       value="<?= e($application['jamb_score'] ?? '') ?>"
                       placeholder="e.g. 185">
            </div>
        </div>

        <div class="form-section-title">
            O'Level Results
            <button type="button" class="btn btn-sm btn-outline ml-2" onclick="addOlRow()">+ Add Subject</button>
        </div>

        <div class="olevel-table-wrap">
            <table class="olevel-table">
                <thead>
                    <tr><th>Sitting</th><th>Exam Type</th><th>Year</th><th>Subject</th><th>Grade</th><th></th></tr>
                </thead>
                <tbody id="olBody">
                    <?php
                    $rows = $olevels ?: array_fill(0, 5, null);
                    foreach ($rows as $r):
                    ?>
                    <tr class="ol-row">
                        <td>
                            <select name="sitting[]" class="form-control form-control-sm">
                                <option <?= ($r['sitting'] ?? '') === 'First'  ? 'selected' : '' ?>>First</option>
                                <option <?= ($r['sitting'] ?? '') === 'Second' ? 'selected' : '' ?>>Second</option>
                            </select>
                        </td>
                        <td>
                            <select name="exam_type[]" class="form-control form-control-sm">
                                <?php foreach (['WAEC','NECO','NABTEB','GCE','Others'] as $et): ?>
                                <option <?= ($r['exam_type'] ?? 'WAEC') === $et ? 'selected' : '' ?>><?= $et ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="exam_year[]" class="form-control form-control-sm"
                                   value="<?= $r['exam_year'] ?? date('Y')-1 ?>"
                                   min="2000" max="<?= date('Y') ?>" style="width:80px">
                        </td>
                        <td>
                            <input type="text" name="subject[]" class="form-control form-control-sm"
                                   value="<?= e($r['subject'] ?? '') ?>" placeholder="e.g. Mathematics">
                        </td>
                        <td>
                            <input type="text" name="grade[]" class="form-control form-control-sm"
                                   value="<?= e($r['grade'] ?? '') ?>" placeholder="A1" style="width:60px">
                        </td>
                        <td><button type="button" class="btn-del-row" onclick="this.closest('tr').remove()">✕</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <span class="form-hint">Enter at least 5 subjects including English Language.</span>

        <div class="form-section-title" style="margin-top:24px">Previous School</div>
        <div class="form-group">
            <label class="form-label">School Name</label>
            <input type="text" name="olevel_school" class="form-control"
                   value="<?= e($application['olevel_school'] ?? '') ?>"
                   placeholder="Name of school where results were obtained">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Year (1st Sitting)</label>
                <input type="number" name="olevel_year1" class="form-control"
                       value="<?= e($application['olevel_year1'] ?? '') ?>"
                       min="2000" max="<?= date('Y') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Year (2nd Sitting)</label>
                <input type="number" name="olevel_year2" class="form-control"
                       value="<?= e($application['olevel_year2'] ?? '') ?>"
                       min="2000" max="<?= date('Y') ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>/apply.php?step=3" class="btn btn-outline">← Back</a>
            <button type="submit" class="btn btn-primary btn-lg">Save &amp; Continue →</button>
        </div>
    </form>
</div>

<!-- ══════════════════════════════════════════════════════════
     STEP 5 – REVIEW & SUBMIT
══════════════════════════════════════════════════════════ -->
<?php elseif ($step === 5):
    if (!$application) {
        setFlash('info','Please complete earlier steps first.');
        header('Location: ' . BASE_URL . '/apply.php?step=2'); exit;
    }
    $ap2 = $pdo->prepare("SELECT * FROM applicants WHERE id = ? LIMIT 1");
    $ap2->execute([$_SESSION['applicant_id']]);
    $apData2 = $ap2->fetch();

    $ol2 = $pdo->prepare("SELECT * FROM olevel_results WHERE application_id = ? ORDER BY sitting, subject");
    $ol2->execute([$application['id']]);
    $olevels2 = $ol2->fetchAll();
?>
<div class="apply-card">
    <div class="apply-card-header">
        <span class="step-tag">Step 5 of 5</span>
        <h2>Review &amp; Submit</h2>
        <p>Carefully review all information before submitting. You cannot edit after submission.</p>
    </div>

    <div class="review-sections">

        <div class="review-block">
            <div class="review-block-header">
                <h4>Programme Details</h4>
                <a href="<?= BASE_URL ?>/apply.php?step=2" class="btn btn-sm btn-outline">Edit</a>
            </div>
            <div class="review-grid">
                <div class="rv"><span>College</span><strong><?= e($application['college_name']) ?></strong></div>
                <div class="rv"><span>Programme</span><strong><?= e($application['programme_name']) ?></strong></div>
                <div class="rv"><span>Qualification</span><strong><?= e($application['qualification']) ?></strong></div>
                <div class="rv"><span>Session</span><strong><?= e($application['academic_session']) ?></strong></div>
                <div class="rv"><span>Entry Mode</span><strong><?= e($application['entry_mode']) ?></strong></div>
                <div class="rv"><span>App. Type</span><strong><?= e($application['application_type']) ?></strong></div>
            </div>
        </div>

        <div class="review-block">
            <div class="review-block-header">
                <h4>Personal Details</h4>
                <a href="<?= BASE_URL ?>/apply.php?step=3" class="btn btn-sm btn-outline">Edit</a>
            </div>
            <div class="review-grid">
                <div class="rv"><span>Full Name</span><strong><?= e($apData2['surname'].', '.$apData2['firstname'].' '.$apData2['middlename']) ?></strong></div>
                <div class="rv"><span>Gender</span><strong><?= e($apData2['gender']) ?></strong></div>
                <div class="rv"><span>Date of Birth</span><strong><?= formatDate($apData2['date_of_birth']) ?></strong></div>
                <div class="rv"><span>State of Origin</span><strong><?= e($apData2['state_of_origin']) ?></strong></div>
                <div class="rv"><span>Phone</span><strong><?= e($apData2['phone']) ?></strong></div>
                <div class="rv"><span>Email</span><strong><?= e($apData2['email']) ?></strong></div>
            </div>
        </div>

        <?php if ($olevels2): ?>
        <div class="review-block">
            <div class="review-block-header">
                <h4>O'Level Results (<?= count($olevels2) ?> subjects)</h4>
                <a href="<?= BASE_URL ?>/apply.php?step=4" class="btn btn-sm btn-outline">Edit</a>
            </div>
            <table class="review-table">
                <thead>
                    <tr><th>Sitting</th><th>Type</th><th>Year</th><th>Subject</th><th>Grade</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($olevels2 as $r): ?>
                    <tr>
                        <td><?= e($r['sitting']) ?></td>
                        <td><?= e($r['exam_type']) ?></td>
                        <td><?= e($r['exam_year']) ?></td>
                        <td><?= e($r['subject']) ?></td>
                        <td><strong><?= e($r['grade']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="review-block">
            <div class="review-block-header">
                <h4>JAMB Details</h4>
                <a href="<?= BASE_URL ?>/apply.php?step=4" class="btn btn-sm btn-outline">Edit</a>
            </div>
            <div class="review-grid">
                <div class="rv"><span>JAMB Reg No.</span><strong><?= e($application['jamb_reg_number'] ?: 'N/A') ?></strong></div>
                <div class="rv"><span>JAMB Score</span><strong><?= e($application['jamb_score'] ?: 'N/A') ?></strong></div>
                <div class="rv"><span>Ref. Number</span><strong><?= e($_SESSION['applicant_ref']) ?></strong></div>
            </div>
        </div>

    </div>

    <div class="review-declaration">
        <label class="check-label">
            <input type="checkbox" id="declareCheck">
            <span>I, <strong><?= e($apData2['firstname'].' '.$apData2['surname']) ?></strong>,
            declare that all information in this application is true and accurate.
            I understand that false information may lead to cancellation of admission.</span>
        </label>
    </div>

    <div class="form-actions">
        <a href="<?= BASE_URL ?>/apply.php?step=4" class="btn btn-outline">← Back</a>
        <form method="POST" action="<?= BASE_URL ?>/process/submit-application.php" style="display:inline">
            <?= csrfField() ?>
            <input type="hidden" name="form_step" value="submit">
            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg"
                    disabled onclick="return confirmSubmit()">
                🎓 Submit Application
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

</div><!-- /container -->
</section>

<script>
function togglePw(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

// Password strength
const pw  = document.getElementById('password');
const pw2 = document.getElementById('password2');
if (pw) {
    pw.addEventListener('input', function () {
        const v   = this.value;
        const bar = document.getElementById('pwStrength');
        if (!bar) return;
        let s = 0;
        if (v.length >= 8)          s++;
        if (/[A-Z]/.test(v))        s++;
        if (/[0-9]/.test(v))        s++;
        if (/[^a-zA-Z0-9]/.test(v)) s++;
        const labels = ['','Weak','Fair','Good','Strong'];
        const colors = ['','#c0392b','#e67e22','#f1c40f','#27ae60'];
        bar.textContent   = labels[s] || '';
        bar.style.color   = colors[s] || '';
        bar.style.fontWeight = '700';
        bar.style.fontSize   = '12px';
        bar.style.marginTop  = '4px';
    });
}
if (pw2) {
    pw2.addEventListener('input', function () {
        const m = document.getElementById('pwMatch');
        if (m) m.style.display = (this.value && this.value !== pw.value) ? 'block' : 'none';
    });
}

// Load programmes via AJAX
function loadProgrammes(collegeId, selectEl) {
    const progSel    = document.getElementById('programme_id');
    const feeHint    = document.getElementById('progFeeHint');
    const foreignDiv = document.getElementById('foreignFields');

    progSel.innerHTML = '<option value="">Loading...</option>';
    if (!collegeId) { progSel.innerHTML = '<option value="">— Select College First —</option>'; return; }

    const code = selectEl ? selectEl.options[selectEl.selectedIndex].dataset.code : '';
    if (foreignDiv) foreignDiv.style.display = (code === 'FUL') ? 'block' : 'none';

    fetch('<?= BASE_URL ?>/process/get-programmes.php?college_id=' + collegeId)
        .then(r => r.json())
        .then(data => {
            progSel.innerHTML = '<option value="">— Select Programme —</option>';
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name + ' (' + p.qualification + ')';
                opt.dataset.fee = p.application_fee;
                progSel.appendChild(opt);
            });
        })
        .catch(() => {
            progSel.innerHTML = '<option value="">Error loading. Refresh page.</option>';
        });
}

// Show fee hint when programme selected
const progSel = document.getElementById('programme_id');
if (progSel) {
    progSel.addEventListener('change', function () {
        const opt     = this.options[this.selectedIndex];
        const feeHint = document.getElementById('progFeeHint');
        if (feeHint) {
            feeHint.textContent = opt.dataset.fee
                ? 'Application fee: ₦' + Number(opt.dataset.fee).toLocaleString()
                : '';
        }
    });
}

// Add blank O'Level row
function addOlRow() {
    const tbody = document.getElementById('olBody');
    const tr    = document.createElement('tr');
    tr.className = 'ol-row';
    tr.innerHTML = `
        <td><select name="sitting[]" class="form-control form-control-sm"><option>First</option><option>Second</option></select></td>
        <td><select name="exam_type[]" class="form-control form-control-sm"><option>WAEC</option><option>NECO</option><option>NABTEB</option><option>GCE</option><option>Others</option></select></td>
        <td><input type="number" name="exam_year[]" class="form-control form-control-sm" value="<?= date('Y')-1 ?>" min="2000" max="<?= date('Y') ?>" style="width:80px"></td>
        <td><input type="text" name="subject[]" class="form-control form-control-sm" placeholder="e.g. Biology"></td>
        <td><input type="text" name="grade[]" class="form-control form-control-sm" placeholder="A1" style="width:60px"></td>
        <td><button type="button" class="btn-del-row" onclick="this.closest('tr').remove()">✕</button></td>`;
    tbody.appendChild(tr);
}

// Preview passport photo
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        preview.innerHTML = `<img src="${e.target.result}" class="preview-img" alt="Passport Preview">`;
    };
    reader.readAsDataURL(input.files[0]);
}

// Enable submit only after declaration checked
const declareCheck = document.getElementById('declareCheck');
const submitBtn    = document.getElementById('submitBtn');
if (declareCheck && submitBtn) {
    declareCheck.addEventListener('change', function () {
        submitBtn.disabled = !this.checked;
    });
}

function confirmSubmit() {
    return confirm('Submit your application now?\n\nYou will not be able to edit it after submission.');
}
</script>

<?php require_once 'includes/footer.php'; ?>
