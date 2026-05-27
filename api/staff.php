<?php
require_once __DIR__ . '/../app/config/config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$stmt = $pdo->query("SELECT id, name FROM staff ORDER BY name");
echo json_encode($stmt->fetchAll());