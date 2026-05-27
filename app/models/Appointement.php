<?php
class Appointement
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function isSlotBooked(int $staff_id, string $start, string $end, int $duration): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT id FROM appointments
            WHERE staff_id = ?
              AND status != 'cancelled'
              AND start_time < ?
              AND (start_time + INTERVAL ? MINUTE) > ?
        ");
        $stmt->execute([$staff_id, $end, $duration, $start]);
        return (bool) $stmt->fetch();
    }

    public function book(
        string $client_name,
        string $client_email,
        int $service_id,
        int $staff_id,
        string $start_dt_str,
        int $duration
    ): bool {
        $start_dt = new DateTime($start_dt_str);
        $end_dt   = (clone $start_dt)->modify("+{$duration} minutes");

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO appointments (client_name, client_email, service_id, staff_id, start_time)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $client_name,
                $client_email,
                $service_id,
                $staff_id,
                $start_dt->format('Y-m-d H:i:s'),
            ]);
            $this->pdo->commit();

            $logFile = __DIR__ . '/../../api/emails.log';
            $logMsg  = "[" . date('Y-m-d H:i:s') . "] TO: $client_email\n"
                     . "SUBJECT: ✅ Appointment Confirmed\n\n"
                     . "Hi $client_name,\nYour appointment is confirmed for "
                     . $start_dt->format('l, F j, Y \a\t g:i A') . ".\nThank you!\n"
                     . str_repeat("=", 60) . "\n\n";
            file_put_contents($logFile, $logMsg, FILE_APPEND);

            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Booking failed: " . $e->getMessage());
            return false;
        }
    }

    public function getAvailableSlots(string $date, int $staff_id, int $duration): array
    {
        $work_start = new DateTime("$date 09:00");
        $work_end   = new DateTime("$date 17:00");
        $available  = [];

        $current = clone $work_start;
        while ($current < $work_end) {
            $slot_end = (clone $current)->modify("+{$duration} minutes");
            if ($slot_end > $work_end) {
                $current->modify('+15 minutes');
                continue;
            }

            $stmt = $this->pdo->prepare("
                SELECT id FROM appointments
                WHERE staff_id = ? AND status != 'cancelled'
                  AND start_time < ?
                  AND (start_time + INTERVAL ? MINUTE) > ?
            ");
            $stmt->execute([
                $staff_id,
                $slot_end->format('Y-m-d H:i:s'),
                $duration,
                $current->format('Y-m-d H:i:s'),
            ]);
            if ($stmt->fetch()) { $current->modify('+15 minutes'); continue; }

            $stmt = $this->pdo->prepare("
                SELECT id FROM staff_unavailable
                WHERE staff_id = ? AND start_time < ? AND end_time > ?
            ");
            $stmt->execute([
                $staff_id,
                $slot_end->format('Y-m-d H:i:s'),
                $current->format('Y-m-d H:i:s'),
            ]);
            if ($stmt->fetch()) { $current->modify('+15 minutes'); continue; }

            $available[] = $current->format('H:i');
            $current->modify('+15 minutes');
        }

        return $available;
    }
}