<?php
require_once __DIR__ . '/../app/config/config.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit(json_encode(['error' => 'Invalid method']));

$data         = json_decode(file_get_contents('php://input'), true);
$client_name  = trim($data['name']);
$client_email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$service_id   = (int)$data['service_id'];
$staff_id     = (int)$data['staff_id'];
$date         = $data['date'];
$time         = $data['time'];

// Link to customer account if logged in
$customer_id = $_SESSION['customer_id'] ?? null;

if (!$client_name || !$client_email || !$service_id || !$staff_id || !$date || !$time) {
    exit(json_encode(['error' => 'Missing or invalid fields']));
}

$stmt = $pdo->prepare("SELECT duration_minutes FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$duration = $stmt->fetchColumn();
if (!$duration) exit(json_encode(['error' => 'Invalid service']));

$start_dt = new DateTime("$date $time");
$end_dt   = (clone $start_dt)->modify("+{$duration} minutes");

$stmt = $pdo->prepare("SELECT id FROM appointments WHERE staff_id = ? AND status != 'cancelled' AND start_time < ? AND (start_time + INTERVAL ? MINUTE) > ?");
$stmt->execute([$staff_id, $end_dt->format('Y-m-d H:i:s'), $duration, $start_dt->format('Y-m-d H:i:s')]);
if ($stmt->fetch()) exit(json_encode(['error' => 'Slot no longer available']));

$stmt = $pdo->prepare("SELECT id FROM staff_unavailable WHERE staff_id = ? AND start_time < ? AND end_time > ?");
$stmt->execute([$staff_id, $end_dt->format('Y-m-d H:i:s'), $start_dt->format('Y-m-d H:i:s')]);
if ($stmt->fetch()) exit(json_encode(['error' => 'Staff unavailable']));

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("
        INSERT INTO appointments (client_name, client_email, service_id, staff_id, start_time, customer_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $client_name,
        $client_email,
        $service_id,
        $staff_id,
        $start_dt->format('Y-m-d H:i:s'),
        $customer_id
    ]);
    $pdo->commit();

    $logFile = __DIR__ . '/emails.log';
    $logMsg  = "[" . date('Y-m-d H:i:s') . "] TO: $client_email\nSUBJECT: Appointment Confirmed\n\nHi $client_name,\nYour appointment is confirmed for " . $start_dt->format('l, F j, Y \a\t g:i A') . ".\nThank you!\n" . str_repeat("=", 60) . "\n\n";
    file_put_contents($logFile, $logMsg, FILE_APPEND);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Failed to save booking']);
}