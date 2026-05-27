<?php
class StaffController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function dashboard(): void
    {
        // session already started in index.php — do NOT call session_start() here

        if (!isset($_SESSION['staff_id']) || empty($_SESSION['staff_id'])) {
            header("Location: /Barber_shop/public/index.php?page=login");
            exit();
        }

        $staff_id    = $_SESSION['staff_id'];
        $staff_name  = $_SESSION['staff_name'];
        $message     = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            [$message, $messageType] = $this->handlePost($_POST, $staff_id);
        }

        $date = $_GET['date'] ?? date('Y-m-d');

        $appointments = $this->getAppointments($staff_id, $date);
        $blockedTimes = $this->getBlockedTimes($staff_id, $date);

        $todayStats = [
            'total'     => count($appointments),
            'confirmed' => count(array_filter($appointments, fn($a) => $a['status'] === 'confirmed')),
            'completed' => count(array_filter($appointments, fn($a) => $a['status'] === 'completed')),
            'blocked'   => count($blockedTimes),
        ];

        require_once __DIR__ . '/../Views/staff/dashboard.php';
    }

    private function handlePost(array $post, int $staff_id): array
    {
        switch ($post['action']) {
            case 'block_time':
                if (isset($post['block_start'], $post['block_end'])) {
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO staff_unavailable (staff_id, start_time, end_time) VALUES (?, ?, ?)"
                    );
                    $stmt->execute([$staff_id, $post['block_start'], $post['block_end']]);
                    return ['Time slot blocked successfully', 'success'];
                }
                break;

            case 'remove_block':
                if (isset($post['block_id'])) {
                    $stmt = $this->pdo->prepare(
                        "DELETE FROM staff_unavailable WHERE id = ? AND staff_id = ?"
                    );
                    $stmt->execute([$post['block_id'], $staff_id]);
                    return ['Block removed successfully', 'success'];
                }
                break;

            case 'complete_appointment':
                if (isset($post['appointment_id'])) {
                    $stmt = $this->pdo->prepare(
                        "UPDATE appointments SET status = 'completed' WHERE id = ? AND staff_id = ?"
                    );
                    $stmt->execute([$post['appointment_id'], $staff_id]);
                    return ['Appointment marked as completed', 'success'];
                }
                break;

            case 'cancel_appointment':
                if (isset($post['appointment_id'])) {
                    $stmt = $this->pdo->prepare(
                        "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND staff_id = ?"
                    );
                    $stmt->execute([$post['appointment_id'], $staff_id]);
                    return ['Appointment cancelled', 'success'];
                }
                break;
        }

        return ['', ''];
    }

    private function getAppointments(int $staff_id, string $date): array
    {
        $stmt = $this->pdo->prepare("
            SELECT a.id, a.start_time, a.status, s.name AS service,
                   a.client_name AS client, a.client_email
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            WHERE a.staff_id = ? AND DATE(a.start_time) = ?
            ORDER BY a.start_time
        ");
        $stmt->execute([$staff_id, $date]);
        return $stmt->fetchAll();
    }

    private function getBlockedTimes(int $staff_id, string $date): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, start_time, end_time
            FROM staff_unavailable
            WHERE staff_id = ? AND DATE(start_time) = ?
            ORDER BY start_time
        ");
        $stmt->execute([$staff_id, $date]);
        return $stmt->fetchAll();
    }
}