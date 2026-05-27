<?php
require_once __DIR__ . '/../app/config/config.php';

$stmt = $pdo->query("
    SELECT id, client_name, client_email, start_time
    FROM appointments
    WHERE status = 'confirmed'
      AND reminder_sent = 0
      AND start_time BETWEEN NOW() AND NOW() + INTERVAL 24 HOUR
");

$logFile = __DIR__ . '/../api/emails.log';
$count   = 0;

while ($app = $stmt->fetch()) {
    $subject = '⏰ Reminder: Appointment Tomorrow';
    $body    = "Hi " . $app['client_name'] . ",\n\nJust a reminder that your appointment is scheduled for "
             . date('l, F j, Y \a\t g:i A', strtotime($app['start_time'])) . ".\n\nSee you soon!";

    $logMsg = "[" . date('Y-m-d H:i:s') . "] TO: " . $app['client_email'] . "\nSUBJECT: $subject\n\n$body\n" . str_repeat("=", 60) . "\n\n";
    file_put_contents($logFile, $logMsg, FILE_APPEND);

    $pdo->prepare("UPDATE appointments SET reminder_sent = 1 WHERE id = ?")->execute([$app['id']]);
    $count++;
}

echo "✅ Processed $count reminder(s). Check api/emails.log";