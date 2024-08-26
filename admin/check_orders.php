<?php
include '../components/connect.php';

session_start();

$latest_check_time = isset($_SESSION['latest_check_time']) ? $_SESSION['latest_check_time'] : 0;

// Cek pesanan baru
$select_new_orders = $conn->prepare("SELECT COUNT(*) as count FROM `orders` WHERE placed_on > FROM_UNIXTIME(?)");
$select_new_orders->execute([$latest_check_time]);
$new_orders_count = $select_new_orders->fetch(PDO::FETCH_ASSOC)['count'];

// Cek pesanan pending
$select_pending_orders = $conn->prepare("SELECT COUNT(*) as count, SUM(total_price) as total FROM `orders` WHERE payment_status = 'pending'");
$select_pending_orders->execute();
$pending_orders = $select_pending_orders->fetch(PDO::FETCH_ASSOC);

$_SESSION['latest_check_time'] = time();

echo json_encode([
    'newOrders' => $new_orders_count,
    'pendingOrders' => $pending_orders['count'],
    'pendingTotal' => $pending_orders['total'] ?? 0
]);