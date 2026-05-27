<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/CustomerAuthController.php';
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['logged_in' => false]);
    exit;
}

$suggestions = CustomerAuthController::getSuggestions($pdo, $_SESSION['customer_id']);
$suggestions['logged_in']     = true;
$suggestions['customer_name'] = $_SESSION['customer_name'];

echo json_encode($suggestions);