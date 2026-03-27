<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$id_user = $_SESSION['id_user'];
$query = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM keranjang WHERE iduser = $id_user");
$result = mysqli_fetch_assoc($query);
$count = $result['total'] ?? 0;

echo json_encode(['count' => (int)$count]);
