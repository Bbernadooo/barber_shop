<?php
require_once __DIR__ . '/../app/config/config.php';
header('Content-Type: application/json');

$date     = $_GET['date'] ?? '';
$staff    = (int)($_GET['staff_id'] ?? 0);
$duration = (int)($_GET['duration'] ?? 30);

if (!$date || !$staff) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$work_start = new DateTime("$date 09:00");
$work_end   = new DateTime("$date 17:00");
$available  = [];

$current = clone $work_start;
while ($current < $work_end) {
    $slot_end = (clone $current)->modify("+{$duration} minutes");
    if ($slot_end > $work_end) { $current->modify('+15 minutes'); continue; }

    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE staff_id = ? AND status != 'cancelled' AND start_time < ? AND (start_time + INTERVAL ? MINUTE) > ?");
    $stmt->execute([$staff, $slot_end->format('Y-m-d H:i:s'), $duration, $current->format('Y-m-d H:i:s')]);
    if ($stmt->fetch()) { $current->modify('+15 minutes'); continue; }

    $stmt = $pdo->prepare("SELECT id FROM staff_unavailable WHERE staff_id = ? AND start_time < ? AND end_time > ?");
    $stmt->execute([$staff, $slot_end->format('Y-m-d H:i:s'), $current->format('Y-m-d H:i:s')]);
    if ($stmt->fetch()) { $current->modify('+15 minutes'); continue; }

    $available[] = $current->format('H:i');
    $current->modify('+15 minutes');
}

echo json_encode($available);