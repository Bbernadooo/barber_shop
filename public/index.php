<?php
session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/StaffController.php';
require_once __DIR__ . '/../app/controllers/CustomerAuthController.php';
require_once __DIR__ . '/../app/models/Appointement.php';
require_once __DIR__ . '/../app/models/Staff.php';

$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'login':
        (new AuthController($pdo))->login();
        break;

    case 'logout':
        (new AuthController($pdo))->logout();
        break;

    case 'staff':
        (new StaffController($pdo))->dashboard();
        break;

    case 'customer-login':
        (new CustomerAuthController($pdo))->login();
        break;

    case 'customer-signup':
        (new CustomerAuthController($pdo))->signup();
        break;

    case 'customer-logout':
        (new CustomerAuthController($pdo))->logout();
        break;

    default:
        header("Location: /Barber_shop/public/booking.html");
        exit();
}