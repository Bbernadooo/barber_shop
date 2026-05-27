<?php
date_default_timezone_set('Africa/Nairobi');

$host    = '127.0.0.1';
$db      = 'barber_shop';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("DB Connection Failed: " . $e->getMessage());

    $isApi = (strpos($_SERVER['REQUEST_URI'], '/api/') !== false);

    if ($isApi) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    } else {
        http_response_code(500);
        die("
            <div style='font-family:sans-serif; background:#111; color:#fff; padding:30px; text-align:center; position:fixed; top:0; left:0; right:0; bottom:0; display:flex; flex-direction:column; justify-content:center; align-items:center;'>
                <h2 style='color:#ff2e2e; margin-bottom:10px;'>Database Connection Offline</h2>
                <p style='color:#888; max-width:400px; font-size:14px;'>The Barber Shop management platform cannot communicate with the database system right now.</p>
                <code style='background:#161616; padding:8px 12px; color:#aaa; font-size:12px; border:1px solid #222; margin-top:15px;'>Check that MySQL is running in your XAMPP Control Panel.</code>
            </div>
        ");
    }
}