<?php
// ============================================================
//  api/send_email.php  —  Reusable email helper
//  Usage: require_once 'send_email.php';
//         sendNotificationEmail($to, $subject, $htmlBody);
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/**
 * Send an HTML email notification using PHPMailer and Gmail SMTP.
 *
 * @param string $to       Recipient email address
 * @param string $subject  Email subject line
 * @param string $htmlBody HTML content of the email
 * @return bool            True on success, false on failure
 */
function sendNotificationEmail($to, $subject, $htmlBody) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'maparipurva27@gmail.com';
        // Remove spaces from the app password for proper authentication
        $mail->Password   = 'tzobfmufgdefpggi';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('maparipurva27@gmail.com', 'SkillSwap');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Build a styled HTML email template for SkillSwap notifications.
 *
 * @param string $title   Heading inside the email
 * @param string $body    Main message content (can include HTML)
 * @return string         Full HTML email string
 */
function buildEmailTemplate($title, $body) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin:0;padding:0;background:#0e0e0e;font-family:Arial,Helvetica,sans-serif;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#0e0e0e;padding:40px 20px;">
        <tr>
          <td align="center">
            <table width="560" cellpadding="0" cellspacing="0" style="background:#1a1a1a;border-radius:16px;border:1px solid #2a2a2a;overflow:hidden;">
              <!-- Header -->
              <tr>
                <td style="padding:28px 32px 20px;border-bottom:1px solid #2a2a2a;">
                  <span style="font-size:22px;font-weight:800;color:#f97316;letter-spacing:-0.02em;">⚡ SkillSwap</span>
                </td>
              </tr>
              <!-- Content -->
              <tr>
                <td style="padding:32px;">
                  <h1 style="margin:0 0 16px;font-size:24px;font-weight:700;color:#f5f0eb;">' . htmlspecialchars($title) . '</h1>
                  <div style="font-size:15px;line-height:1.7;color:#a8a29e;">
                    ' . $body . '
                  </div>
                </td>
              </tr>
              <!-- Footer -->
              <tr>
                <td style="padding:20px 32px;border-top:1px solid #2a2a2a;text-align:center;">
                  <span style="font-size:12px;color:#57534e;">© 2025 SkillSwap — Exchange skills, not money.</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>';
}
