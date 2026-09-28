<?php
// ============================================================
//  Foreign Links Campus – Email Mailer
//  /config/mailer.php
//  Requires PHPMailer — install via:
//  composer require phpmailer/phpmailer
//  OR place PHPMailer src/ folder inside /config/PHPMailer/
// ============================================================

require_once dirname(__DIR__) . '/config/config.php';

// Try Composer autoload first, then fall back to manual
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
} else {
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email
 *
 * @param string $toEmail    Recipient email address
 * @param string $toName     Recipient display name
 * @param string $subject    Email subject
 * @param string $htmlBody   HTML email body
 * @param string $plainBody  Plain text fallback
 * @return array ['success' => bool, 'error' => string]
 */
function sendMail(string $toEmail, string $toName,
                  string $subject, string $htmlBody,
                  string $plainBody = ''): array
{
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // From / To
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SITE_EMAIL, SITE_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = emailTemplate($subject, $htmlBody);
        $mail->AltBody = $plainBody ?: strip_tags($htmlBody);

        $mail->send();
        return ['success' => true, 'error' => ''];

    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

// ── Reusable Email HTML Template ─────────────────────────────
function emailTemplate(string $title, string $content): string
{
    $siteName = SITE_NAME;
    $year     = date('Y');
    $phone    = SITE_PHONE;
    $email    = SITE_EMAIL;

    return <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; }
        .wrap { max-width:600px; margin:30px auto; background:#ffffff; border-radius:8px; overflow:hidden; }
        .header { background:#1a472a; padding:24px 32px; text-align:center; }
        .header h1 { color:#ffffff; margin:0; font-size:20px; }
        .header p  { color:#a8d5b5; margin:4px 0 0; font-size:13px; }
        .body { padding:32px; color:#333333; line-height:1.7; }
        .body h2  { color:#1a472a; font-size:17px; margin-bottom:16px; }
        .footer   { background:#f4f4f4; padding:16px 32px; text-align:center;
                    font-size:12px; color:#888888; }
        .btn { display:inline-block; background:#1a472a; color:#ffffff !important;
               padding:12px 28px; border-radius:6px; text-decoration:none;
               font-weight:bold; margin:16px 0; }
        .divider { border:none; border-top:1px solid #eeeeee; margin:20px 0; }
      </style>
    </head>
    <body>
      <div class="wrap">
        <div class="header">
          <h1>{$siteName}</h1>
          <p>Moro, Ife-North, Osun State · Official Admission Portal</p>
        </div>
        <div class="body">
          <h2>{$title}</h2>
          {$content}
          <hr class="divider">
          <p style="font-size:13px;color:#666;">
            For support, contact us at
            <a href="mailto:{$email}">{$email}</a>
            or call {$phone}.
          </p>
        </div>
        <div class="footer">
          &copy; {$year} {$siteName}. This is an automated message — please do not reply directly.
        </div>
      </div>
    </body>
    </html>
    HTML;
}

// ── Pre-built Email Templates ─────────────────────────────────

function mailWelcome(string $toEmail, string $name, string $refNumber): array
{
    $html = "<p>Dear <strong>{$name}</strong>,</p>
             <p>Your account has been created on the {$_SERVER['SERVER_NAME']} Admission Portal.</p>
             <p><strong>Your Application Reference Number:</strong> {$refNumber}</p>
             <p>Please keep this number safe — you will need it to track your application.</p>
             <a class='btn' href='" . BASE_URL . "/login.php'>Login to Your Dashboard</a>
             <p>We wish you the best in your application.</p>";

    return sendMail($toEmail, $name, 'Welcome to ' . SITE_NAME . ' – Account Created', $html);
}

function mailAdmissionStatus(string $toEmail, string $name,
                              string $status, string $programme): array
{
    $statusMsg = match($status) {
        'Admitted'    => "Congratulations! You have been <strong>offered admission</strong> into <strong>{$programme}</strong>. Please login to download your admission letter and pay your acceptance fee.",
        'Shortlisted' => "Your application for <strong>{$programme}</strong> has been <strong>shortlisted</strong>. Please check your dashboard for next steps.",
        'Rejected'    => "We regret to inform you that your application for <strong>{$programme}</strong> was <strong>not successful</strong> at this time.",
        default       => "Your application status has been updated to <strong>{$status}</strong>. Please login to your dashboard for details.",
    };

    $html = "<p>Dear <strong>{$name}</strong>,</p>
             <p>{$statusMsg}</p>
             <a class='btn' href='" . BASE_URL . "/dashboard.php'>View Your Dashboard</a>";

    return sendMail($toEmail, $name, SITE_NAME . ' – Application Update: ' . $status, $html);
}

function mailPaymentConfirmed(string $toEmail, string $name,
                               string $payType, string $amount): array
{
    $html = "<p>Dear <strong>{$name}</strong>,</p>
             <p>Your <strong>{$payType}</strong> of <strong>{$amount}</strong> has been
             received and verified.</p>
             <a class='btn' href='" . BASE_URL . "/dashboard.php'>View Dashboard</a>";

    return sendMail($toEmail, $name, SITE_NAME . ' – Payment Confirmed', $html);
}
