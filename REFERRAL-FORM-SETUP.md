# Wiring up the referral form

New files added:

- `submit-referral.php` — receives the form POST, validates it, sends the email.
- `mail/smtp-mailer.php` — small dependency-free SMTP client (no Composer/library needed).
- `mail/config.php` — **you need to edit this** with your real SMTP credentials.
- `mail/.htaccess` — blocks direct web access to the `mail/` folder.
- `referral-thank-you.html` — shown after a successful submission.
- `send-your-referrals.html` — updated: form now posts to `/submit-referral.php`, plus a hidden honeypot field and an error banner.

## What you need to fill in

Open `mail/config.php` and replace these four values with the SMTP relay details from your
HIPAA-compliant email provider's dashboard (ask whoever set up that mailbox if you don't have
them — look for "SMTP settings," "API/Integration," or "outbound relay"):

- `smtp_host`
- `smtp_port` (587 for STARTTLS, 465 for implicit SSL — most providers use 587)
- `smtp_username`
- `smtp_password`

`to_email` is already set to `info@assistoncallprof.com`. Leave `from_email` matching
`smtp_username` unless your provider tells you otherwise — many reject mail where those don't match.

## Deploying to cPanel

1. Upload the whole `assist-on-call-website/` folder to your `public_html/` (or the domain's
   document root) via cPanel's File Manager or FTP.
2. Confirm PHP is enabled for the domain (cPanel → "MultiPHP Manager" — PHP 7.4+ is fine).
3. Confirm the site has an SSL certificate active (cPanel → "SSL/TLS Status," or use their free
   AutoSSL) — `submit-referral.php` refuses to run over plain HTTP on purpose, since this form
   carries patient health information.
4. Fill in `mail/config.php` as above.
5. Test: open `send-your-referrals.html` on the live domain, submit the form with test data, and
   confirm the email arrives at info@assistoncallprof.com. Check that `mail/config.php` is NOT
   viewable by visiting `https://yourdomain.com/mail/config.php` directly in a browser — it should
   return a 403 Forbidden (confirming the `.htaccess` is working).

## If sending fails

`submit-referral.php` never prints the SMTP error to the browser or logs any patient data. It logs
one line to PHP's error log (cPanel → "Errors" or your hosting's error log viewer) like:

```
[submit-referral] SMTP send failed: Unexpected SMTP response, expected 235, got: 535 ...
```

A `535` response usually means the SMTP username/password is wrong. A connection timeout usually
means the host/port is wrong or the port is blocked by the host's firewall — ask your hosting
provider if outbound SMTP on 587/465 is allowed (some shared hosts block it by default and require
a support ticket to open it).
