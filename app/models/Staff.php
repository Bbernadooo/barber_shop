<?php
class Staff
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM staff WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function isUnavailable(int $staff_id, string $start, string $end): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT id FROM staff_unavailable
            WHERE staff_id = ? AND start_time < ? AND end_time > ?
        ");
        $stmt->execute([$staff_id, $end, $start]);
        return (bool) $stmt->fetch();
    }

    public function blockTime(int $staff_id, string $start, string $end): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO staff_unavailable (staff_id, start_time, end_time) VALUES (?, ?, ?)"
        );
        $stmt->execute([$staff_id, $start, $end]);
    }

    public function removeBlock(int $block_id, int $staff_id): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM staff_unavailable WHERE id = ? AND staff_id = ?"
        );
        $stmt->execute([$block_id, $staff_id]);
    }

    public function getBlockedTimes(int $staff_id, string $date): array
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