<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit();
}

$id_siswa = $_SESSION['id_siswa'];

// Ambil data peminjaman aktif siswa
$query_peminjaman = "SELECT p.*, b.judul, b.penulis, b.penerbit 
                     FROM peminjaman p 
                     JOIN buku b ON p.id_buku = b.id_buku 
                     WHERE p.id_siswa = '$id_siswa' AND p.status IN ('dipinjam', 'telat') 
                     ORDER BY p.tanggal_peminjaman DESC";
$result_peminjaman = mysqli_query($conn, $query_peminjaman);

include '../includes/header.php';
?>

<div class="col-md-12">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-book me-2"></i>Peminjaman Aktif Saya</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        32
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tanggal Pinjam</th>
                            <th>Batas Pengembalian</th>
                            <th>Sisa Hari</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        while($pinjam = mysqli_fetch_assoc($result_peminjaman)):
                            $tanggal_pinjam = strtotime($pinjam['tanggal_peminjaman']);
                            $batas_kembali = strtotime($pinjam['tanggal_peminjaman'] . ' +7 days');
                            $tanggal_sekarang = time();
                            $selisih_hari = floor(($batas_kembali - $tanggal_sekarang) / (60 * 60 * 24));
                            
                            // Cek telat
                            if($selisih_hari < 0 && $pinjam['status'] == 'dipinjam') {
                                mysqli_query($conn, "UPDATE peminjaman SET status = 'telat' WHERE id_peminjaman = '{$pinjam['id_peminjaman']}'");
                                $pinjam['status'] = 'telat';
                                $selisih_hari = abs($selisih_hari);
                            }
                            
                            // Warna sisa hari
                            if($selisih_hari < 0) {
                                $warna = 'text-danger';
                                $teks = 'Terlambat ' . abs($selisih_hari) . ' hari';
                            } elseif($selisih_hari == 0) {
                                $warna = 'text-warning';
                                $teks = 'Hari terakhir!';
                            } elseif($selisih_hari <= 2) {
                                $warna = 'text-warning';
                                $teks = $selisih_hari . ' hari lagi';
                            } else {
                                $warna = 'text-success';
                                $teks = $selisih_hari . ' hari lagi';
                            }
                        ?>
                         <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $pinjam['judul']; ?></td>
                            <td><?php echo $pinjam['penulis']; ?></td>
                            <td><?php echo $pinjam['penerbit']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pinjam['tanggal_peminjaman'])); ?></td>
                            <td><?php echo date('d/m/Y', $batas_kembali); ?></td>
                            <td class="<?php echo $warna; ?>"><strong><?php echo $teks; ?></strong></td>
                            <td>
                                <?php if($pinjam['status'] == 'telat'): ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Telat
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-book me-1"></i>Dipinjam
                                    </span>
                                <?php endif; ?>
                            </td>
                         </tr>
                        <?php endwhile; ?>
                        
                        <?php if(mysqli_num_rows($result_peminjaman) == 0): ?>
                         <tr>
                            <td colspan="8" class="text-center">Tidak ada peminjaman aktif</td>
                         </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>