<?php
/**
 * Handles POST submissions from send-your-referrals.html and emails the
 * completed referral to the address configured in mail/config.php
 * (info@assistoncallprof.com), sent through your HIPAA-compliant SMTP relay.
 *
 * This form carries protected health information (diagnosis, DOB, insurance,
 * address). Do not weaken the checks below (HTTPS-only, no logging of PHI)
 * without checking with whoever owns HIPAA compliance for this site.
 */

declare(strict_types=1);

require __DIR__ . '/mail/smtp-mailer.php';
$config = require __DIR__ . '/mail/config.php';

// --- Require HTTPS -----------------------------------------------------
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (!$isHttps) {
    http_response_code(400);
    exit('This form must be submitted over HTTPS.');
}

// --- Require POST --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// --- Honeypot spam trap ---------------------------------------------------
// Add a hidden field named "website" to the form (see send-your-referrals.html)
// that real users never fill in; bots that auto-fill every field will.
if (!empty($_POST['website'])) {
    // Silently pretend success so bots don't learn anything.
    header('Location: ' . $config['success_redirect']);
    exit;
}

/**
 * Pull a POST field, trim it, and cap its length so nobody can send a
 * multi-megabyte "notes" field through the relay.
 */
function field(array $post, string $key, int $maxLength = 500): string
{
    $value = $post[$key] ?? '';
    $value = is_string($value) ? trim($value) : '';
    return mb_substr($value, 0, $maxLength);
}

$referrer_name     = field($_POST, 'referrer_name', 200);
$referrer_title    = field($_POST, 'referrer_title', 200);
$referrer_org      = field($_POST, 'referrer_org', 200);
$relationship      = field($_POST, 'relationship', 100);
$referrer_email    = field($_POST, 'referrer_email', 200);
$referrer_phone    = field($_POST, 'referrer_phone', 50);
$referrer_fax      = field($_POST, 'referrer_fax', 50);
$referrer_besttime = field($_POST, 'referrer_besttime', 200);

$patient_first   = field($_POST, 'patient_first', 200);
$patient_last    = field($_POST, 'patient_last', 200);
$patient_dob     = field($_POST, 'patient_dob', 20);
$patient_phone   = field($_POST, 'patient_phone', 50);
$patient_address = field($_POST, 'patient_address', 300);
$patient_city    = field($_POST, 'patient_city', 200);
$patient_zip     = field($_POST, 'patient_zip', 20);

$service         = field($_POST, 'service', 300);
$diagnosis       = field($_POST, 'diagnosis', 500);
$physician       = field($_POST, 'physician', 200);
$physician_phone = field($_POST, 'physician_phone', 50);
$insurance       = field($_POST, 'insurance', 200);
$start_date      = field($_POST, 'start_date', 20);

$emergency_name  = field($_POST, 'emergency_name', 200);
$emergency_phone = field($_POST, 'emergency_phone', 50);
$notes           = field($_POST, 'notes', 3000);
$auth            = !empty($_POST['auth']);

// --- Server-side validation of required fields ----------------------------
// (Mirrors the "required" attributes in the HTML - never trust client-side
// validation alone.)
$errors = [];

if ($referrer_name === '') {
    $errors[] = 'Referring party name is required.';
}
if ($referrer_email === '' || !filter_var($referrer_email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid referrer email is required.';
}
if ($referrer_phone === '') {
    $errors[] = 'Referrer phone is required.';
}
if ($patient_first === '') {
    $errors[] = 'Patient first name is required.';
}
if ($patient_last === '') {
    $errors[] = 'Patient last name is required.';
}
if ($service === '') {
    $errors[] = 'Service requested is required.';
}
if (!$auth) {
    $errors[] = 'Authorisation checkbox must be confirmed.';
}

if (!empty($errors)) {
    http_response_code(422);
    // Redirect back with a generic error flag; details are not put in the
    // URL since this form carries PHI.
    header('Location: ' . $config['error_redirect']);
    exit;
}

// --- Build the email body --------------------------------------------------
$lines = [];
$lines[] = 'New patient referral submitted from the Assist on Call website.';
$lines[] = str_repeat('-', 60);
$lines[] = '';
$lines[] = 'REFERRING PARTY';
$lines[] = "Name: {$referrer_name}";
$lines[] = "Title / role: {$referrer_title}";
$lines[] = "Facility / organisation: {$referrer_org}";
$lines[] = "Relationship to patient: {$relationship}";
$lines[] = "Email: {$referrer_email}";
$lines[] = "Phone: {$referrer_phone}";
$lines[] = "Fax: {$referrer_fax}";
$lines[] = "Best time to reach: {$referrer_besttime}";
$lines[] = '';
$lines[] = 'PATIENT DETAILS';
$lines[] = "Name: {$patient_first} {$patient_last}";
$lines[] = "Date of birth: {$patient_dob}";
$lines[] = "Phone: {$patient_phone}";
$lines[] = "Address: {$patient_address}";
$lines[] = "City: {$patient_city}";
$lines[] = "ZIP: {$patient_zip}";
$lines[] = '';
$lines[] = 'CARE REQUEST';
$lines[] = "Service requested: {$service}";
$lines[] = "Primary diagnosis: {$diagnosis}";
$lines[] = "Referring physician: {$physician}";
$lines[] = "Physician phone: {$physician_phone}";
$lines[] = "Insurance / payer: {$insurance}";
$lines[] = "Requested start of care: {$start_date}";
$lines[] = '';
$lines[] = 'EMERGENCY CONTACT';
$lines[] = "Name: {$emergency_name}";
$lines[] = "Phone: {$emergency_phone}";
$lines[] = '';
$lines[] = 'ADDITIONAL NOTES';
$lines[] = ($notes !== '' ? $notes : '(none)');
$lines[] = '';
$lines[] = str_repeat('-', 60);
$lines[] = 'Referrer confirmed authorisation to share this patient information: Yes';
$lines[] = 'Submitted: ' . date('Y-m-d H:i:s T');

$body = implode("\n", $lines);
$subject = "New referral: {$patient_first} {$patient_last} ({$service})";

// --- Send ------------------------------------------------------------------
try {
    $mailer = new SimpleSmtpMailer(
        $config['smtp_host'],
        $config['smtp_port'],
        $config['smtp_encryption'],
        $config['smtp_username'],
        $config['smtp_password']
    );

    $mailer->send(
        $config['from_email'],
        $config['from_name'],
        $config['to_email'],
        $config['to_name'],
        $subject,
        $body,
        $referrer_email // so clicking "Reply" in the inbox goes to the referrer
    );
} catch (Throwable $e) {
    // Do not echo $e->getMessage() to the browser or write PHI into logs.
    // Log only that a failure occurred, with no submitted field values.
    error_log('[submit-referral] SMTP send failed: ' . $e->getMessage());
    http_response_code(502);
    header('Location: ' . $config['error_redirect']);
    exit;
}

header('Location: ' . $config['success_redirect']);
exit;
