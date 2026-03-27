<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login kembali.']);
    exit();
}

$id_user = $_SESSION['id_user'];

// --- VALIDASI INPUT ---
$tanggal_mulai     = $_POST['tanggal_mulai'] ?? null;
$tanggal_selesai   = $_POST['tanggal_selesai'] ?? null;
$durasi            = (int)($_POST['durasi'] ?? 1);
$metode_pengiriman = $_POST['metode_pengiriman'] ?? 'Ambil Sendiri';
$alamat_sewa       = $_POST['alamat_sewa'] ?? '';
$lat_sewa          = $_POST['lat_sewa'] ?? '';
$lon_sewa          = $_POST['lon_sewa'] ?? '';
$ongkir            = (int)($_POST['ongkir'] ?? 0);
$total_biaya       = (int)($_POST['total_biaya'] ?? 0);

if (!$tanggal_mulai || !$tanggal_selesai || !$alamat_sewa) {
    echo json_encode(['success' => false, 'message' => 'Lengkapi data penyewaan.']);
    exit();
}

// --- FETCH CART ITEMS ---
$query_cart = mysqli_query($conn, "
    SELECT k.*, a.nama_alat, a.harga_sewa 
    FROM keranjang k 
    JOIN alat a ON k.idalat = a.idalat 
    WHERE k.iduser = $id_user
");

if (mysqli_num_rows($query_cart) == 0) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong.']);
    exit();
}

// --- HANDLE KTP UPLOAD ---
$nama_file_ktp = "";
if (isset($_FILES['gambar_ktp']) && $_FILES['gambar_ktp']['error'] == 0) {
    $target_dir = "../uploads/";
    $file_ext = strtolower(pathinfo($_FILES["gambar_ktp"]["name"], PATHINFO_EXTENSION));
    $nama_file_ktp = "ktp_" . $id_user . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $nama_file_ktp;
    
    // Check if file is image
    $check = getimagesize($_FILES["gambar_ktp"]["tmp_name"]);
    if($check !== false) {
        move_uploaded_file($_FILES["gambar_ktp"]["tmp_name"], $target_file);
    } else {
        echo json_encode(['success' => false, 'message' => 'File KTP harus berupa gambar.']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Harap upload foto KTP Anda.']);
    exit();
}

// --- START TRANSACTION (Implicit) ---
// Save to 'penyewaan' table
$sql_sewa = "INSERT INTO penyewaan (iduser, tanggal_mulai, tanggal_selesai, durasi, metode_pengiriman, alamat_sewa, lat_sewa, lon_sewa, ongkir, total_biaya, status) 
             VALUES ('$id_user', '$tanggal_mulai', '$tanggal_selesai', '$durasi', '$metode_pengiriman', '$alamat_sewa', '$lat_sewa', '$lon_sewa', '$ongkir', '$total_biaya', 'pending')";

if (mysqli_query($conn, $sql_sewa)) {
    $id_sewa = mysqli_insert_id($conn);
    
    // Save to 'penyewaan_detail' table
    while ($item = mysqli_fetch_assoc($query_cart)) {
        $id_alat = $item['idalat'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga_sewa'];
        $subtotal = $jumlah * $harga * $durasi;
        
        $sql_detail = "INSERT INTO penyewaan_detail (idsewa, idalat, jumlah, harga, subtotal, gambar_ktp) 
                       VALUES ('$id_sewa', '$id_alat', '$jumlah', '$harga', '$subtotal', '$nama_file_ktp')";
        mysqli_query($conn, $sql_detail);
    }
    
    // Clear keranjang
    mysqli_query($conn, "DELETE FROM keranjang WHERE iduser = $id_user");
    
    echo json_encode(['success' => true, 'idsewa' => $id_sewa]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>
