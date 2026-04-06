<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses CRUD
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // TAMBAH PEMINJAMAN
    if($action == 'add') {
        $id_siswa = mysqli_real_escape_string($conn, $_POST['id_siswa']);
        $id_buku = mysqli_real_escape_string($conn, $_POST['id_buku']);
        $tanggal_peminjaman = mysqli_real_escape_string($conn, $_POST['tanggal_peminjaman']);
        
        $cek_stok = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = '$id_buku'");
        $stok = mysqli_fetch_assoc($cek_stok);
        
        if($stok['stok'] > 0) {
            mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");
            $query = "INSERT INTO peminjaman (id_siswa, id_buku, tanggal_peminjaman, status) 
                      VALUES ('$id_siswa', '$id_buku', '$tanggal_peminjaman', 'dipinjam')";
            mysqli_query($conn, $query);
            echo "<script>alert('Peminjaman berhasil ditambahkan'); window.location='peminjaman.php';</script>";
        } else {
            echo "<script>alert('Stok buku habis!'); window.location='peminjaman.php';</script>";
        }
    }
    
    // EDIT PEMINJAMAN
    elseif($action == 'edit') {
        $id = $_POST['id'];
        $id_siswa = mysqli_real_escape_string($conn, $_POST['id_siswa']);
        $id_buku = mysqli_real_escape_string($conn, $_POST['id_buku']);
        $tanggal_peminjaman = mysqli_real_escape_string($conn, $_POST['tanggal_peminjaman']);
        $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? "'".mysqli_real_escape_string($conn, $_POST['tanggal_kembali'])."'" : "NULL";
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // Jika status diubah jadi kembali, update stok
        $old_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status, id_buku FROM peminjaman WHERE id_peminjaman='$id'"));
        if($status == 'kembali' && $old_status['status'] != 'kembali') {
            mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '{$old_status['id_buku']}'");
        }
        // Jika dari kembali ke dipinjam, kurangi stok
        elseif($status != 'kembali' && $old_status['status'] == 'kembali') {
            mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '{$old_status['id_buku']}'");
        }
        
        $query = "UPDATE peminjaman SET 
                  id_siswa='$id_siswa', id_buku='$id_buku', 
                  tanggal_peminjaman='$tanggal_peminjaman', 
                  tanggal_kembali=$tanggal_kembali, status='$status' 
                  WHERE id_peminjaman='$id'";
        mysqli_query($conn, $query);
        echo "<script>alert('Peminjaman berhasil diupdate'); window.location='peminjaman.php';</script>";
    }
    
    // HAPUS PEMINJAMAN
    elseif($action == 'delete') {
        $id = $_POST['id'];
        $query_buku = mysqli_query($conn, "SELECT id_buku, status FROM peminjaman WHERE id_peminjaman='$id'");
        $data = mysqli_fetch_assoc($query_buku);
        
        if($data['status'] == 'dipinjam' || $data['status'] == 'telat') {
            mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '{$data['id_buku']}'");
        }
        
        mysqli_query($conn, "DELETE FROM peminjaman WHERE id_peminjaman='$id'");
        echo "<script>alert('Peminjaman berhasil dihapus'); window.location='peminjaman.php';</script>";
    }
    
    // KEMBALIKAN BUKU (dengan pilihan tanggal)
    elseif($action == 'kembali') {
        $id_peminjaman = $_POST['id_peminjaman'];
        $id_buku = $_POST['id_buku'];
        $tanggal_kembali = mysqli_real_escape_string($conn, $_POST['tanggal_kembali']);
        
        mysqli_query($conn, "UPDATE peminjaman SET tanggal_kembali = '$tanggal_kembali', status = 'kembali' WHERE id_peminjaman = '$id_peminjaman'");
        mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '$id_buku'");
        
        echo "<script>alert('Buku berhasil dikembalikan pada tanggal $tanggal_kembali'); window.location='peminjaman.php';</script>";
    }
}

// Ambil data untuk dropdown
$query_siswa = "SELECT id_anggota, nama_lengkap, kelas FROM anggota ORDER BY nama_lengkap";
$result_siswa = mysqli_query($conn, $query_siswa);

$query_buku = "SELECT b.id_buku, b.judul, b.stok, k.nama_kategori 
               FROM buku b 
               LEFT JOIN kategori k ON b.kategori = k.id_kategori 
               WHERE b.stok > 0 
               ORDER BY b.judul";
$result_buku = mysqli_query($conn, $query_buku);

// Ambil data peminjaman untuk tab
$query_semua = "SELECT p.*, a.nama_lengkap, a.kelas, b.judul, b.penulis 
                FROM peminjaman p 
                JOIN anggota a ON p.id_siswa = a.id_anggota 
                JOIN buku b ON p.id_buku = b.id_buku 
                ORDER BY p.tanggal_peminjaman DESC";
$result_semua = mysqli_query($conn, $query_semua);

$query_aktif = "SELECT p.*, a.nama_lengkap, a.kelas, b.judul, b.penulis 
                FROM peminjaman p 
                JOIN anggota a ON p.id_siswa = a.id_anggota 
                JOIN buku b ON p.id_buku = b.id_buku 
                WHERE p.status IN ('dipinjam', 'telat')
                ORDER BY p.tanggal_peminjaman DESC";
$result_aktif = mysqli_query($conn, $query_aktif);

$query_riwayat = "SELECT p.*, a.nama_lengkap, a.kelas, b.judul, b.penulis 
                  FROM peminjaman p 
                  JOIN anggota a ON p.id_siswa = a.id_anggota 
                  JOIN buku b ON p.id_buku = b.id_buku 
                  WHERE p.status = 'kembali'
                  ORDER BY p.tanggal_peminjaman DESC";
$result_riwayat = mysqli_query($conn, $query_riwayat);

// Ambil semua buku untuk dropdown edit
$query_all_buku = "SELECT id_buku, judul, stok FROM buku ORDER BY judul";
$result_all_buku = mysqli_query($conn, $query_all_buku);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman - Admin Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .navbar-custom .navbar-brand { color: white; font-weight: bold; font-size: 1.5rem; }
        .sidebar { background: white; min-height: 100vh; box-shadow: 2px 0 10px rgba(0,0,0,0.05); border-radius: 10px; }
        .sidebar .nav-link { color: #333; padding: 12px 20px; margin: 5px 0; border-radius: 10px; transition: all 0.3s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .card { border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .badge-dipinjam { background-color: #ffc107; color: #000; padding: 5px 12px; border-radius: 20px; }
        .badge-kembali { background-color: #28a745; color: #fff; padding: 5px 12px; border-radius: 20px; }
        .badge-telat { background-color: #dc3545; color: #fff; padding: 5px 12px; border-radius: 20px; }
        .btn-sm { border-radius: 20px; padding: 5px 15px; }
        .nav-tabs .nav-link { border-radius: 10px 10px 0 0; }
        .nav-tabs .nav-link.active { font-weight: bold; color: #667eea; }
    </style>
</head>
<body>

<nav class="navbar navbar-custom navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-book-open me-2"></i>Admin Perpustakaan</a>
        <div class="ms-auto">
            <span class="text-white me-3">Welcome, <?php echo $_SESSION['username']; ?></span>
            <a href="../logout.php" class="btn btn-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <div class="sidebar p-3">
                <h5 class="mb-3">Menu Admin</h5>
                <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="buku.php" class="nav-link"><i class="fas fa-book me-2"></i>Manajemen Buku</a>
                <a href="kategori.php" class="nav-link"><i class="fas fa-tags me-2"></i>Manajemen Kategori</a>
                <a href="siswa.php" class="nav-link"><i class="fas fa-users me-2"></i>Manajemen Siswa</a>
                <a href="peminjaman.php" class="nav-link active"><i class="fas fa-hand-peace me-2"></i>Manajemen Peminjaman</a>
                <a href="riwayat.php" class="nav-link"><i class="fas fa-history me-2"></i>Riwayat Peminjaman</a>
            </div>
        </div>
        
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-hand-peace me-2"></i>Manajemen Peminjaman</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Tambah Peminjaman
                </button>
            </div>
            
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#semua"><i class="fas fa-list me-2"></i>Semua Peminjaman</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#aktif"><i class="fas fa-book-open me-2"></i>Aktif</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#riwayat"><i class="fas fa-check-circle me-2"></i>Selesai</a></li>
            </ul>
            
            <div class="tab-content">
                <!-- Tab Semua -->
                <div class="tab-pane fade show active" id="semua">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr><th>No</th><th>Siswa</th><th>Kelas</th><th>Judul Buku</th><th>Penulis</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $no=1; while($p = mysqli_fetch_assoc($result_semua)): 
                                            $is_telat = ($p['status'] == 'dipinjam' && floor((time() - strtotime($p['tanggal_peminjaman']))/86400) > 7);
                                            if($is_telat) mysqli_query($conn, "UPDATE peminjaman SET status='telat' WHERE id_peminjaman='{$p['id_peminjaman']}'");
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $p['nama_lengkap']; ?></td>
                                            <td><?php echo $p['kelas']; ?></td>
                                            <td><?php echo $p['judul']; ?></td>
                                            <td><?php echo $p['penulis']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal_peminjaman'])); ?></td>
                                            <td><?php echo $p['tanggal_kembali'] ? date('d/m/Y H:i', strtotime($p['tanggal_kembali'])) : '-'; ?></td>
                                            <td><?php if($p['status']=='dipinjam') echo '<span class="badge-dipinjam"><i class="fas fa-book me-1"></i>Dipinjam</span>';
                                                elseif($p['status']=='telat') echo '<span class="badge-telat"><i class="fas fa-exclamation-triangle me-1"></i>Telat</span>';
                                                else echo '<span class="badge-kembali"><i class="fas fa-check me-1"></i>Kembali</span>'; ?>
                                            </td>
                                            <td>
                                                <?php if($p['status'] != 'kembali'): ?>
                                                <button class="btn btn-sm btn-success mb-1 btn-kembali" 
                                                    data-id="<?php echo $p['id_peminjaman']; ?>"
                                                    data-buku="<?php echo $p['id_buku']; ?>"
                                                    data-judul="<?php echo $p['judul']; ?>"
                                                    data-siswa="<?php echo $p['nama_lengkap']; ?>">
                                                    <i class="fas fa-undo-alt"></i> Kembalikan
                                                </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-warning mb-1 btn-edit" 
                                                    data-id="<?php echo $p['id_peminjaman']; ?>" 
                                                    data-siswa="<?php echo $p['id_siswa']; ?>" 
                                                    data-buku="<?php echo $p['id_buku']; ?>" 
                                                    data-tanggal_pinjam="<?php echo date('Y-m-d\TH:i', strtotime($p['tanggal_peminjaman'])); ?>" 
                                                    data-tanggal_kembali="<?php echo $p['tanggal_kembali'] ? date('Y-m-d\TH:i', strtotime($p['tanggal_kembali'])) : ''; ?>" 
                                                    data-status="<?php echo $p['status']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger mb-1 btn-delete" data-id="<?php echo $p['id_peminjaman']; ?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; if(mysqli_num_rows($result_semua)==0): ?><tr><td colspan="9" class="text-center py-5">Belum ada data peminjaman</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Aktif -->
                <div class="tab-pane fade" id="aktif">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr><th>No</th><th>Siswa</th><th>Kelas</th><th>Judul Buku</th><th>Penulis</th><th>Tgl Pinjam</th><th>Batas Kembali</th><th>Sisa Hari</th><th>Status</th><th>Aksi</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $no=1; while($p = mysqli_fetch_assoc($result_aktif)): 
                                            $batas_kembali = strtotime($p['tanggal_peminjaman'] . ' +7 days');
                                            $sisa_hari = floor(($batas_kembali - time()) / 86400);
                                            $batas_tgl = date('d/m/Y', $batas_kembali);
                                            if($sisa_hari < 0) { $warna = 'text-danger'; $teks = 'Telat '.abs($sisa_hari).' hari'; }
                                            elseif($sisa_hari == 0) { $warna = 'text-warning'; $teks = 'Hari terakhir!'; }
                                            else { $warna = 'text-success'; $teks = $sisa_hari.' hari'; }
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $p['nama_lengkap']; ?></td>
                                            <td><?php echo $p['kelas']; ?></td>
                                            <td><?php echo $p['judul']; ?></td>
                                            <td><?php echo $p['penulis']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal_peminjaman'])); ?></td>
                                            <td><?php echo $batas_tgl; ?></td>
                                            <td class="<?php echo $warna; ?>"><strong><?php echo $teks; ?></strong></td>
                                            <td><?php if($sisa_hari<0) echo '<span class="badge-telat"><i class="fas fa-exclamation-triangle me-1"></i>Telat</span>';
                                                else echo '<span class="badge-dipinjam"><i class="fas fa-book me-1"></i>Dipinjam</span>'; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success btn-kembali" 
                                                    data-id="<?php echo $p['id_peminjaman']; ?>"
                                                    data-buku="<?php echo $p['id_buku']; ?>"
                                                    data-judul="<?php echo $p['judul']; ?>"
                                                    data-siswa="<?php echo $p['nama_lengkap']; ?>">
                                                    <i class="fas fa-undo-alt"></i> Kembalikan
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; if(mysqli_num_rows($result_aktif)==0): ?><tr><td colspan="10" class="text-center py-5">Tidak ada peminjaman aktif</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Riwayat -->
                <div class="tab-pane fade" id="riwayat">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light"><tr><th>No</th><th>Siswa</th><th>Kelas</th><th>Judul Buku</th><th>Penulis</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
                                    <tbody>
                                        <?php $no=1; while($p = mysqli_fetch_assoc($result_riwayat)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $p['nama_lengkap']; ?></td>
                                            <td><?php echo $p['kelas']; ?></td>
                                            <td><?php echo $p['judul']; ?></td>
                                            <td><?php echo $p['penulis']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal_peminjaman'])); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal_kembali'])); ?></td>
                                            <td><span class="badge-kembali"><i class="fas fa-check me-1"></i>Selesai</span></td>
                                        </tr>
                                        <?php endwhile; if(mysqli_num_rows($result_riwayat)==0): ?><tr><td colspan="8" class="text-center py-5">Belum ada riwayat peminjaman</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Peminjaman</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label>Siswa</label><select name="id_siswa" class="form-control" required><option value="">-- Pilih Siswa --</option><?php mysqli_data_seek($result_siswa,0); while($s=mysqli_fetch_assoc($result_siswa)): ?><option value="<?php echo $s['id_anggota']; ?>"><?php echo $s['nama_lengkap']; ?> (<?php echo $s['kelas']; ?>)</option><?php endwhile; ?></select></div>
                    <div class="mb-3"><label>Buku</label><select name="id_buku" class="form-control" required><option value="">-- Pilih Buku --</option><?php mysqli_data_seek($result_buku,0); while($b=mysqli_fetch_assoc($result_buku)): ?><option value="<?php echo $b['id_buku']; ?>"><?php echo $b['judul']; ?> (Stok: <?php echo $b['stok']; ?>)</option><?php endwhile; ?></select></div>
                    <div class="mb-3"><label>Tanggal Peminjaman</label><input type="datetime-local" name="tanggal_peminjaman" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kembalikan Buku (Dengan Pilihan Tanggal) -->
<div class="modal fade" id="kembaliModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-undo-alt me-2"></i>Kembalikan Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="kembali">
                    <input type="hidden" name="id_peminjaman" id="kembali_id">
                    <input type="hidden" name="id_buku" id="kembali_buku">
                    
                    <div class="mb-3">
                        <label><i class="fas fa-user me-2"></i>Siswa</label>
                        <input type="text" id="kembali_siswa" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label><i class="fas fa-book me-2"></i>Buku</label>
                        <input type="text" id="kembali_judul" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label><i class="fas fa-calendar-alt me-2"></i>Tanggal Pengembalian</label>
                        <input type="date" name="tanggal_kembali" id="kembali_tanggal" class="form-control" required>
                        <small class="text-muted">Pilih tanggal buku dikembalikan (bisa tanggal hari ini atau sebelumnya)</small>
                    </div>
                    <div class="alert alert-info mt-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Catatan:</strong> Jika buku dikembalikan melewati batas waktu (7 hari), status akan otomatis menjadi "Telat".
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-2"></i>Konfirmasi Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-warning"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Peminjaman</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3"><label>Siswa</label><select name="id_siswa" id="edit_siswa" class="form-control" required><option value="">-- Pilih Siswa --</option><?php mysqli_data_seek($result_siswa,0); while($s=mysqli_fetch_assoc($result_siswa)): ?><option value="<?php echo $s['id_anggota']; ?>"><?php echo $s['nama_lengkap']; ?> (<?php echo $s['kelas']; ?>)</option><?php endwhile; ?></select></div>
                    <div class="mb-3"><label>Buku</label><select name="id_buku" id="edit_buku" class="form-control" required><option value="">-- Pilih Buku --</option><?php mysqli_data_seek($result_all_buku,0); while($b=mysqli_fetch_assoc($result_all_buku)): ?><option value="<?php echo $b['id_buku']; ?>"><?php echo $b['judul']; ?> (Stok: <?php echo $b['stok']; ?>)</option><?php endwhile; ?></select></div>
                    <div class="mb-3"><label>Tanggal Peminjaman</label><input type="datetime-local" name="tanggal_peminjaman" id="edit_tanggal_pinjam" class="form-control" required></div>
                    <div class="mb-3"><label>Tanggal Kembali (kosongkan jika belum)</label><input type="datetime-local" name="tanggal_kembali" id="edit_tanggal_kembali" class="form-control"></div>
                    <div class="mb-3"><label>Status</label><select name="status" id="edit_status" class="form-control" required><option value="dipinjam">Dipinjam</option><option value="kembali">Kembali</option><option value="telat">Telat</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-trash me-2"></i>Hapus Peminjaman</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Apakah Anda yakin ingin menghapus peminjaman ini?</p>
                    <p class="text-danger">Stok buku akan dikembalikan jika status masih dipinjam!</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Tombol Kembalikan dengan pilihan tanggal
    $('.btn-kembali').click(function() {
        $('#kembali_id').val($(this).data('id'));
        $('#kembali_buku').val($(this).data('buku'));
        $('#kembali_siswa').val($(this).data('siswa'));
        $('#kembali_judul').val($(this).data('judul'));
        // Set default tanggal hari ini
        $('#kembali_tanggal').val(new Date().toISOString().split('T')[0]);
        $('#kembaliModal').modal('show');
    });
    
    // Edit button
    $('.btn-edit').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_siswa').val($(this).data('siswa'));
        $('#edit_buku').val($(this).data('buku'));
        $('#edit_tanggal_pinjam').val($(this).data('tanggal_pinjam'));
        var tgl_kembali = $(this).data('tanggal_kembali');
        if(tgl_kembali) $('#edit_tanggal_kembali').val(tgl_kembali);
        else $('#edit_tanggal_kembali').val('');
        $('#edit_status').val($(this).data('status'));
        $('#editModal').modal('show');
    });
    
    // Delete button
    $('.btn-delete').click(function() {
        $('#delete_id').val($(this).data('id'));
        $('#deleteModal').modal('show');
    });
});
</script>
</body>
</html>