<?php
// ============================================================
//  Foreign Links Campus – Application POST Handler
//  /process/submit-application.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireApplicantLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/apply.php'); exit;
}
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error','Invalid request. Please try again.');
    header('Location: ' . BASE_URL . '/apply.php'); exit;
}

$applicantId = (int)$_SESSION['applicant_id'];
$formStep    = clean($_POST['form_step'] ?? '');
$session     = getCurrentSession($pdo);

// ── STEP 2: Save programme choice ───────────────────────────
if ($formStep === '2') {
    $collegeId   = (int)($_POST['college_id']       ?? 0);
    $programmeId = (int)($_POST['programme_id']     ?? 0);
    $appType     = clean($_POST['application_type'] ?? 'Fresh');
    $entryMode   = clean($_POST['entry_mode']       ?? 'UTME');
    $destCountry = clean($_POST['destination_country'] ?? '');
    $foreignUni  = clean($_POST['foreign_university']  ?? '');

    if (!$collegeId || !$programmeId) {
        setFlash('error','Please select both a college and a programme.');
        header('Location: ' . BASE_URL . '/apply.php?step=2'); exit;
    }

    // Check for existing application this session
    $chk = $pdo->prepare("SELECT id FROM applications WHERE applicant_id=? AND session_id=? LIMIT 1");
    $chk->execute([$applicantId, $session['id']]);
    $existing = $chk->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE applications SET college_id=?,programme_id=?,
            application_type=?,entry_mode=?,destination_country=?,
            foreign_university=?,step_personal=1 WHERE id=?")
            ->execute([$collegeId,$programmeId,$appType,$entryMode,
                       $destCountry,$foreignUni,$existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO applications
            (applicant_id,session_id,college_id,programme_id,
             application_type,entry_mode,destination_country,
             foreign_university,step_personal)
            VALUES (?,?,?,?,?,?,?,?,1)")
            ->execute([$applicantId,$session['id'],$collegeId,
                       $programmeId,$appType,$entryMode,$destCountry,$foreignUni]);
    }

    header('Location: ' . BASE_URL . '/apply.php?step=3'); exit;
}

// ── STEP 3: Save personal details & passport ────────────────
if ($formStep === '3') {
    $surname     = clean($_POST['surname']         ?? '');
    $firstname   = clean($_POST['firstname']       ?? '');
    $middlename  = clean($_POST['middlename']      ?? '');
    $gender      = clean($_POST['gender']          ?? '');
    $dob         = clean($_POST['dob']             ?? '');
    $religion    = clean($_POST['religion']        ?? '');
    $nationality = clean($_POST['nationality']     ?? 'Nigerian');
    $state       = clean($_POST['state_of_origin'] ?? '');
    $lga         = clean($_POST['lga']             ?? '');
    $address     = clean($_POST['home_address']    ?? '');
    $phone       = clean($_POST['phone']           ?? '');

    if (!$surname || !$firstname || !$gender || !$dob || !$state || !$address) {
        setFlash('error','Please fill all required personal fields.');
        header('Location: ' . BASE_URL . '/apply.php?step=3'); exit;
    }

    // Passport photo upload
    $passportPath = null;
    if (!empty($_FILES['passport_photo']['name'])) {
        $upload = uploadFile($_FILES['passport_photo'], 'passports', ALLOWED_IMAGE_TYPES);
        if (!$upload['success']) {
            setFlash('error', $upload['error']);
            header('Location: ' . BASE_URL . '/apply.php?step=3'); exit;
        }
        $passportPath = $upload['path'];
    }

    $sql = "UPDATE applicants SET surname=?,firstname=?,middlename=?,gender=?,
            date_of_birth=?,religion=?,nationality=?,state_of_origin=?,
            lga=?,home_address=?,phone=?" .
           ($passportPath ? ",passport_photo=?" : "") . " WHERE id=?";
    $params = [$surname,$firstname,$middlename,$gender,$dob,
               $religion,$nationality,$state,$lga,$address,$phone];
    if ($passportPath) $params[] = $passportPath;
    $params[] = $applicantId;
    $pdo->prepare($sql)->execute($params);

    // Mark step done on application
    $app = getActiveApplication($pdo, $applicantId);
    if ($app) {
        $pdo->prepare("UPDATE applications SET step_academic=1 WHERE id=?")->execute([$app['id']]);
    }

    header('Location: ' . BASE_URL . '/apply.php?step=4'); exit;
}

// ── STEP 4: Save academic / O'Level results ─────────────────
if ($formStep === '4') {
    $app = getActiveApplication($pdo, $applicantId);
    if (!$app) {
        setFlash('error','Application not found. Please start from Step 2.');
        header('Location: ' . BASE_URL . '/apply.php?step=2'); exit;
    }

    $jambReg   = clean($_POST['jamb_reg_number'] ?? '');
    $jambScore = !empty($_POST['jamb_score']) ? (int)$_POST['jamb_score'] : null;
    $school    = clean($_POST['olevel_school']  ?? '');
    $year1     = !empty($_POST['olevel_year1']) ? (int)$_POST['olevel_year1'] : null;
    $year2     = !empty($_POST['olevel_year2']) ? (int)$_POST['olevel_year2'] : null;

    $pdo->prepare("UPDATE applications SET jamb_reg_number=?,jamb_score=?,
        olevel_school=?,olevel_year1=?,olevel_year2=?,step_documents=1
        WHERE id=?")
        ->execute([$jambReg,$jambScore,$school,$year1,$year2,$app['id']]);

    // Delete old and re-insert O'Level rows
    $pdo->prepare("DELETE FROM olevel_results WHERE application_id=?")->execute([$app['id']]);

    $sittings = $_POST['sitting']   ?? [];
    $types    = $_POST['exam_type'] ?? [];
    $years    = $_POST['exam_year'] ?? [];
    $subjects = $_POST['subject']   ?? [];
    $grades   = $_POST['grade']     ?? [];

    $ins = $pdo->prepare("INSERT INTO olevel_results
        (application_id,sitting,exam_type,exam_year,subject,grade)
        VALUES (?,?,?,?,?,?)");

    for ($i = 0; $i < count($subjects); $i++) {
        $subj  = clean($subjects[$i] ?? '');
        $grade = clean($grades[$i]   ?? '');
        if ($subj && $grade) {
            $ins->execute([
                $app['id'],
                clean($sittings[$i] ?? 'First'),
                clean($types[$i]    ?? 'WAEC'),
                (int)($years[$i]    ?? date('Y')),
                $subj,
                $grade,
            ]);
        }
    }

    header('Location: ' . BASE_URL . '/apply.php?step=5'); exit;
}

// ── FINAL SUBMIT ─────────────────────────────────────────────
if ($formStep === 'submit') {
    $app = getActiveApplication($pdo, $applicantId);
    if (!$app) {
        setFlash('error','Application not found.');
        header('Location: ' . BASE_URL . '/apply.php?step=2'); exit;
    }

    if ($app['step_submitted']) {
        setFlash('warning','Your application has already been submitted.');
        header('Location: ' . BASE_URL . '/dashboard.php'); exit;
    }

    $pdo->prepare("UPDATE applications SET
        step_submitted=1, step_payment=0,
        status='Pending', submitted_at=NOW() WHERE id=?")
        ->execute([$app['id']]);

    sendPortalNotification($pdo, $applicantId,
        'Application Submitted Successfully',
        "Your application for <strong>{$app['programme_name']}</strong> ({$app['college_code']}) has been submitted for the {$app['academic_session']} session. Reference: <strong>{$_SESSION['applicant_ref']}</strong>. Kindly proceed to make your application fee payment.");

    auditLog($pdo, 'Applicant', $applicantId, 'Submit Application',
             'App ID: ' . $app['id'] . ' | Programme: ' . $app['programme_name']);

    setFlash('success', 'Application submitted successfully! Please proceed to make your payment.');
    header('Location: ' . BASE_URL . '/dashboard.php'); exit;
}

// Fallback
setFlash('error','Unknown form step.');
header('Location: ' . BASE_URL . '/apply.php');
exit;
