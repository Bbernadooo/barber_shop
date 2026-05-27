<?php
class AuthController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(): void
    {
        if (isset($_SESSION['staff_id'])) {
            header("Location: /Barber_shop/public/index.php?page=staff");
            exit();
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            if (!empty($username) && !empty($password)) {
                // Look up staff by username in DB
                $stmt = $this->pdo->prepare("SELECT * FROM staff WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $staff = $stmt->fetch();

                if ($staff && password_verify($password, $staff['password'])) {
                    $_SESSION['staff_id']   = $staff['id'];
                    $_SESSION['staff_name'] = $staff['name'];
                    header("Location: /Barber_shop/public/index.php?page=staff");
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Please fill in all fields.";
            }
        }

        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function logout(): void
    {
        session_destroy();
        header("Location: /Barber_shop/public/booking.html");
        exit();
    }
}