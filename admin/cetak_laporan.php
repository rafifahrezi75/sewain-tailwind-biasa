<?php
    include 'auth_check.php';
    include '../config.php';
    
    $bulan = isset($_GET['bulan']) ? mysqli_real_escape_string($conn, $_GET['bulan']) : '';
    $tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($conn, $_GET['tahun']) : date('Y');

    $conditions = [];
    $conditions[] = "YEAR(s.tanggal_mulai) = '$tahun'";

    if ($bulan != "") {
        $conditions[] = "MONTH(s.tanggal_mulai) = '$bulan'";
        $bulan_indo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        $label_waktu = $bulan_indo[$bulan] . " " . $tahun;
    } else {
        $label_waktu = "Tahun " . $tahun;
    }

    $where_sql = "WHERE " . implode(" AND ", $conditions);

    $query = mysqli_query($conn, "
        SELECT s.*, u.nama as nama_pelanggan 
        FROM penyewaan s 
        JOIN user u ON s.iduser = u.id_user 
        $where_sql 
        ORDER BY s.tanggal_mulai ASC
    ");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi SewaIn</title>
    <link href="../src/output.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; padding: 0; }
            .print-area { border: none; box-shadow: none; max-width: 100%; margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-gray-100 p-4 md:p-10">

    <div class="no-print max-w-6xl mx-auto mb-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <a href="javascript:void(0)" onclick="history.back()" class="group flex items-center gap-2 mb-1 transition-all">
                    
                    <i class='bx bx-left-arrow-alt text-2xl text-brand-500 group-hover:-translate-x-1 transition-transform'></i>
                    
                    <h1 class="text-lg font-bold italic text-gray-800 uppercase tracking-tight">
                        Cetak Laporan Penjualan
                    </h1>
                </a>
            </div>

            <form method="GET" class="flex flex-wrap gap-2">
                <select name="bulan" class="rounded-xl border-gray-300 text-sm focus:ring-brand-500 font-medium">
                    <option value="">Semua Bulan</option>
                    <?php
                    $months = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                    foreach($months as $m => $nm) {
                        $sel = ($bulan == $m) ? 'selected' : '';
                        echo "<option value='$m' $sel>$nm</option>";
                    }
                    ?>
                </select>
                
                <select name="tahun" class="rounded-xl border-gray-300 text-sm focus:ring-brand-500 font-medium">
                    <?php 
                    $y = date('Y');
                    for($i=$y; $i>=$y-2; $i--) {
                        $sel = ($tahun == $i) ? 'selected' : '';
                        echo "<option value='$i' $sel>$i</option>";
                    }
                    ?>
                </select>

                <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-black transition flex items-center gap-2">
                    <i class='bx bx-search'></i> CARI DATA
                </button>
                
                <button type="button" onclick="window.print()" class="bg-brand-500 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-brand-600 shadow-lg flex items-center gap-2">
                    <i class='bx bx-printer'></i> CETAK PDF
                </button>
            </form>
        </div>
    </div>

    <div class="print-area max-w-6xl mx-auto bg-white p-12 shadow-xl border border-gray-200 min-h-[297mm]">
        
        <div class="flex justify-between items-start border-b-4 border-black pb-6 mb-8">
            <div>
                <h2 class="text-4xl font-black tracking-tighter uppercase">Sewa<span class="text-brand-500">In</span></h2>
                <p class="text-gray-500 font-bold text-sm">Laporan Peminjaman Alat Produksi</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-black uppercase italic"><?= $label_waktu ?></p>
                <p class="text-xs text-gray-400">Dicetak: <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>

        <table class="w-full mb-8">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-300">
                    <th class="p-3 text-left text-xs font-black uppercase">No</th>
                    <th class="p-3 text-left text-xs font-black uppercase">ID Sewa</th>
                    <th class="p-3 text-left text-xs font-black uppercase">Pelanggan</th>
                    <th class="p-3 text-left text-xs font-black uppercase">Tgl Sewa</th>
                    <th class="p-3 text-right text-xs font-black uppercase">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; $grand = 0;
                if(mysqli_num_rows($query) > 0): 
                    while($row = mysqli_fetch_assoc($query)): 
                        $grand += $row['total_biaya'];
                ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-3 text-sm text-gray-400"><?= $no++ ?></td>
                        <td class="p-3 text-sm font-bold">#<?= str_pad($row['idsewa'], 4, '0', STR_PAD_LEFT) ?></td>
                        <td class="p-3 text-sm italic"><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td class="p-3 text-sm"><?= date('d/m/Y', strtotime($row['tanggal_mulai'])) ?></td>
                        <td class="p-3 text-sm text-right font-bold">Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?></td>
                    </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <tr>
                        <td colspan="5" class="p-20 text-center border-b border-gray-200 bg-gray-50/50">
                            <div class="flex flex-col items-center justify-center">
                                <i class='bx bx-calendar-x text-6xl text-gray-300 mb-2'></i>
                                <p class="text-sm font-black text-gray-800 uppercase italic">Data Tidak Ditemukan</p>
                                <p class="text-xs text-gray-400">Tidak ada transaksi tercatat pada <span class="text-brand-500 font-bold"><?= $label_waktu ?></span></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-900 text-brand-500">
                    <td colspan="4" class="p-4 text-right font-black uppercase text-xs">Total Pendapatan</td>
                    <td class="p-4 text-right font-black text-lg">Rp <?= number_format($grand, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-20 flex justify-end text-center">
            <div class="w-64">
                <p class="text-sm font-bold mb-20">Mengetahui, Admin SewaIn</p>
                <p class="font-black border-b-2 border-black inline-block px-4"><?= $_SESSION['nama'] ?></p>
            </div>
        </div>
    </div>

</body>
</html>