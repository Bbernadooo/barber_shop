<?php
class CustomerAuthController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(): void
    {
        if (isset($_SESSION['customer_id'])) {
            header("Location: /Barber_shop/public/booking.html");
            exit();
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']);
            $password = trim($_POST['password']);

            if (!empty($email) && !empty($password)) {
                $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $customer = $stmt->fetch();

                if ($customer && password_verify($password, $customer['password'])) {
                    $_SESSION['customer_id']   = $customer['id'];
                    $_SESSION['customer_name'] = $customer['name'];
                    header("Location: /Barber_shop/public/booking.html");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Please fill in all fields.";
            }
        }

        require_once __DIR__ . '/../Views/customer/login.php';
    }

    public function signup(): void
    {
        if (isset($_SESSION['customer_id'])) {
            header("Location: /Barber_shop/public/booking.html");
            exit();
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name']);
            $email    = trim($_POST['email']);
            $phone    = trim($_POST['phone']);
            $password = trim($_POST['password']);
            $confirm  = trim($_POST['confirm_password']);

            if (empty($name) || empty($email) || empty($password)) {
                $error = "Please fill in all required fields.";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                // Check if email already exists
                $stmt = $this->pdo->prepare("SELECT id FROM customers WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "An account with this email already exists.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt   = $this->pdo->prepare(
                        "INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([$name, $email, $phone, $hashed]);
                    $_SESSION['customer_id']   = $this->pdo->lastInsertId();
                    $_SESSION['customer_name'] = $name;
                    header("Location: /Barber_shop/public/booking.html");
                    exit();
                }
            }
        }

        require_once __DIR__ . '/../Views/customer/signup.php';
    }

    public function logout(): void
    {
        unset($_SESSION['customer_id'], $_SESSION['customer_name']);
        header("Location: /Barber_shop/public/booking.html");
        exit();
    }

    /**
     * Get smart suggestions for a customer based on booking history.
     * Returns preferred barber and preferred time slot.
     */
    public static function getSuggestions(PDO $pdo, int $customer_id): array
    {
        // Most booked barber
        $stmt = $pdo->prepare("
            SELECT staff_id, COUNT(*) as count
            FROM appointments
            WHERE customer_id = ? AND status != 'cancelled'
            GROUP BY staff_id
            ORDER BY count DESC
            LIMIT 1
        ");
        $stmt->execute([$customer_id]);
        $barberRow = $stmt->fetch();

        $suggestedBarber = null;
        if ($barberRow) {
            $stmt = $pdo->prepare("SELECT id, name FROM staff WHERE id = ?");
            $stmt->execute([$barberRow['staff_id']]);
            $suggestedBarber = $stmt->fetch();
        }

        // Most frequent time slot (hour of day)
        $stmt = $pdo->prepare("
            SELECT HOUR(start_time) as hour, COUNT(*) as count
            FROM appointments
            WHERE customer_id = ? AND status != 'cancelled'
            GROUP BY HOUR(start_time)
            ORDER BY count DESC
            LIMIT 1
        ");
        $stmt->execute([$customer_id]);
        $timeRow = $stmt->fetch();

        $suggestedTime = null;
        if ($timeRow) {
            $hour = (int)$timeRow['hour'];
            $suggestedTime = sprintf('%02d:00', $hour);
        }

        return [
            'barber' => $suggestedBarber,
            'time'   => $suggestedTime,
        ];
    }
}