<?php
/**
 * Shared helper functions.
 */

/**
 * Generate the next registration number for the current year.
 * Format: GIA-YYYY-XXXX  (e.g. GIA-2026-0001)
 */
function generateRegNumber(PDO $pdo): string
{
    $year = date('Y');
    $prefix = "GIA-{$year}-";

    $stmt = $pdo->prepare(
        "SELECT reg_number FROM students
         WHERE reg_number LIKE :prefix
         ORDER BY student_id DESC LIMIT 1"
    );
    $stmt->execute(['prefix' => $prefix . '%']);
    $last = $stmt->fetchColumn();

    if ($last) {
        // Extract the numeric suffix and increment it
        $lastNumber = (int) substr($last, -4);
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

/**
 * Create an in-admin-panel notification.
 */
function createNotification(PDO $pdo, string $type, ?int $studentId, string $message): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (type, student_id, message)
         VALUES (:type, :student_id, :message)"
    );
    $stmt->execute([
        'type'       => $type,
        'student_id' => $studentId,
        'message'    => $message,
    ]);
}

/**
 * Log a message (email or SMS) to message_log, and attempt to send it.
 * SMS sending requires a gateway account (e.g. Africa's Talking, Twilio) —
 * see the sendSms() stub below for where to plug in real credentials.
 */
function logAndSendMessage(PDO $pdo, int $studentId, string $channel, string $subject, string $body, string $toEmail = '', string $toPhone = ''): void
{
    $status = 'pending';

    if ($channel === 'email') {
        $status = sendEmail($toEmail, $subject, $body) ? 'sent' : 'failed';
    } elseif ($channel === 'sms') {
        $status = sendSms($toPhone, $body) ? 'sent' : 'failed';
    }

    $stmt = $pdo->prepare(
        "INSERT INTO message_log (student_id, channel, subject, message_body, status, sent_at)
         VALUES (:student_id, :channel, :subject, :body, :status, :sent_at)"
    );
    $stmt->execute([
        'student_id' => $studentId,
        'channel'    => $channel,
        'subject'    => $subject,
        'body'       => $body,
        'status'     => $status,
        'sent_at'    => $status === 'sent' ? date('Y-m-d H:i:s') : null,
    ]);
}

/**
 * Send an email. Uses PHP's mail() as a basic default —
 * for reliable delivery, swap this for PHPMailer + SMTP (Gmail, etc.)
 * once you're ready; happy to wire that up.
 */
function sendEmail(string $to, string $subject, string $body): bool
{
    if (empty($to)) return false;

    $headers = "From: God Is Able ICT Training Centre <no-reply@godisableict.com>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($to, $subject, $body, $headers);
}

/**
 * Send an SMS. This is a placeholder — plug in a real gateway
 * (e.g. Africa's Talking, which is popular and affordable in Uganda)
 * with your account credentials when ready.
 */
function sendSms(string $to, string $message): bool
{
    if (empty($to)) return false;

    // Example structure for Africa's Talking (uncomment and fill in when ready):
    //
    // $username = 'YOUR_USERNAME';
    // $apiKey   = 'YOUR_API_KEY';
    // $data = http_build_query([
    //     'username' => $username,
    //     'to'       => $to,
    //     'message'  => $message,
    // ]);
    // $ch = curl_init('https://api.africastalking.com/version1/messaging');
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //     'apiKey: ' . $apiKey,
    //     'Content-Type: application/x-www-form-urlencoded',
    //     'Accept: application/json',
    // ]);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // $response = curl_exec($ch);
    // curl_close($ch);
    // return $response !== false;

    // Until a gateway is connected, we log it as pending rather than claim success.
    return false;
}
