<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['id_user'];
    $id_alat = (int)$_POST['idalat'];
    $jumlah = (int)$_POST['jumlah'];

    // Ambil harga alat
    $query_alat = mysqli_query($conn, "SELECT harga_sewa FROM alat WHERE idalat = $id_alat");
    $alat = mysqli_fetch_assoc($query_alat);
    
    if (!$alat) {
        echo json_encode(['status' => 'error', 'message' => 'Alat tidak ditemukan']);
        exit();
    }

    $harga_sewa = $alat['harga_sewa'];

    // Cek apakah alat sudah ada di keranjang user ini
    $check = mysqli_query($conn, "SELECT idkeranjang, jumlah FROM keranjang WHERE iduser = $id_user AND idalat = $id_alat");
    
    if ($row = mysqli_fetch_assoc($check)) {
        // Update jumlah
        $new_qty = $row['jumlah'] + $jumlah;
        $id_keranjang = $row['idkeranjang'];
        $new_harga = $new_qty * $harga_sewa;
        mysqli_query($conn, "UPDATE keranjang SET jumlah = $new_qty, hargakeranjang = $new_harga WHERE idkeranjang = $id_keranjang");
    } else {
        // Insert baru
        $total_harga = $jumlah * $harga_sewa;
        mysqli_query($conn, "INSERT INTO keranjang (iduser, idalat, jumlah, hargakeranjang) VALUES ($id_user, $id_alat, $jumlah, $total_harga)");
    }

    // Hitung total item di keranjang untuk badge
    $query_total = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM keranjang WHERE iduser = $id_user");
    $total_res = mysqli_fetch_assoc($query_total);
    $total_items = $total_res['total'] ?? 0;

    echo json_encode(['status' => 'success', 'total_items' => $total_items]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
