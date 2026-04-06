<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Hitung total buku
$query_buku = "SELECT COUNT(*) as total FROM buku";
$result_buku = mysqli_query($conn, $query_buku);
$total_buku = mysqli_fetch_assoc($result_buku)['total'];

// Hitung total kategori
$query_kategori = "SELECT COUNT(*) as total FROM kategori";
$result_kategori = mysqli_query($conn, $query_kategori);
$total_kategori = mysqli_fetch_assoc($result_kategori)['total'];

// Hitung total siswa
$query_siswa = "SELECT COUNT(*) as total FROM anggota";
$result_siswa = mysqli_query($conn, $query_siswa);
$total_siswa = mysqli_fetch_assoc($result_siswa)['total'];

// Hitung total siswa yang meminjam
$query_meminjam = "SELECT COUNT(DISTINCT id_siswa) as total FROM peminjaman WHERE status = 'dipinjam'";
$result_meminjam = mysqli_query($conn, $query_meminjam);
$total_meminjam = mysqli_fetch_assoc($result_meminjam)['total'];

// PEMINJAMAN HARI INI
$query_pinjam_hari_ini = "SELECT COUNT(*) as total FROM peminjaman WHERE DATE(tanggal_peminjaman) = CURDATE()";
$result_pinjam_hari_ini = mysqli_query($conn, $query_pinjam_hari_ini);
$pinjam_hari_ini = mysqli_fetch_assoc($result_pinjam_hari_ini)['total'];

// PENGEMBALIAN HARI INI
$query_kembali_hari_ini = "SELECT COUNT(*) as total FROM peminjaman WHERE DATE(tanggal_kembali) = CURDATE()";
$result_kembali_hari_ini = mysqli_query($conn, $query_kembali_hari_ini);
$kembali_hari_ini = mysqli_fetch_assoc($result_kembali_hari_ini)['total'];

// BUKU PALING POPULER (Top 5)
$query_populer = "SELECT b.judul, COUNT(p.id_peminjaman) as total_pinjam 
                  FROM buku b 
                  LEFT JOIN peminjaman p ON b.id_buku = p.id_buku 
                  GROUP BY b.id_buku 
                  ORDER BY total_pinjam DESC 
                  LIMIT 5";
$result_populer = mysqli_query($conn, $query_populer);

// PEMINJAMAN TERBARU (5 data terakhir)
$query_terbaru = "SELECT p.*, a.nama_lengkap, b.judul 
                  FROM peminjaman p 
                  JOIN anggota a ON p.id_siswa = a.id_anggota 
                  JOIN buku b ON p.id_buku = b.id_buku 
                  ORDER BY p.tanggal_peminjaman DESC 
                  LIMIT 5";
$result_terbaru = mysqli_query($conn, $query_terbaru);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .navbar-custom .navbar-brand { color: white; font-weight: bold; font-size: 1.5rem; }
        .sidebar { background: white; min-height: 100vh; box-shadow: 2px 0 10px rgba(0,0,0,0.05); border-radius: 10px; }
        .sidebar .nav-link { color: #333; padding: 12px 20px; margin: 5px 0; border-radius: 10px; transition: all 0.3s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .card-dashboard { border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .card-dashboard:hover { transform: translateY(-5px); }
        .card { border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: bold; }
        .stat-label { font-size: 0.9rem; color: #666; }
        .list-group-item { border: none; padding: 12px 0; border-bottom: 1px solid #eee; }
        .list-group-item:last-child { border-bottom: none; }
        .badge-pinjam { background-color: #ffc107; color: #000; }
        .badge-kembali { background-color: #28a745; color: #fff; }
        .badge-telat { background-color: #dc3545; color: #fff; }
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
                <a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="buku.php" class="nav-link"><i class="fas fa-book me-2"></i>Manajemen Buku</a>
                <a href="kategori.php" class="nav-link"><i class="fas fa-tags me-2"></i>Manajemen Kategori</a>
                <a href="siswa.php" class="nav-link"><i class="fas fa-users me-2"></i>Manajemen Siswa</a>
                <a href="peminjaman.php" class="nav-link"><i class="fas fa-hand-peace me-2"></i>Manajemen Peminjaman</a>
                <a href="riwayat.php" class="nav-link"><i class="fas fa-history me-2"></i>Riwayat Peminjaman</a>
            </div>
        </div>
        
        <div class="col-md-10 p-4">
            <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
            
            <!-- Stat Cards Row 1 -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card-dashboard bg-primary text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $total_buku; ?></div>
                                <div class="stat-label text-white-50">Total Buku</div>
                            </div>
                            <i class="fas fa-book fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-dashboard bg-success text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $total_kategori; ?></div>
                                <div class="stat-label text-white-50">Total Kategori</div>
                            </div>
                            <i class="fas fa-tags fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-dashboard bg-info text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $total_siswa; ?></div>
                                <div class="stat-label text-white-50">Total Siswa</div>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-dashboard bg-warning text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $total_meminjam; ?></div>
                                <div class="stat-label text-white-50">Siswa Meminjam</div>
                            </div>
                            <i class="fas fa-book-reader fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stat Cards Row 2 (Hari Ini) -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card-dashboard" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?php echo $pinjam_hari_ini; ?></div>
                                    <div class="stat-label text-white-50">Peminjaman Hari Ini</div>
                                </div>
                                <i class="fas fa-hand-peace fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card-dashboard" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number"><?php echo $kembali_hari_ini; ?></div>
                                    <div class="stat-label text-white-50">Pengembalian Hari Ini</div>
                                </div>
                                <i class="fas fa-undo-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Buku Populer & Peminjaman Terbaru -->
            <div class="row mb-4">
                <div class="col-md-5 mb-3">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-fire me-2 text-danger"></i>Buku Paling Populer</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php 
                                $no = 1;
                                while($populer = mysqli_fetch_assoc($result_populer)): 
                                    $persen = $populer['total_pinjam'] > 0 ? round(($populer['total_pinjam'] / 100) * 100) : 0;
                                ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2">#<?php echo $no++; ?></span>
                                            <strong><?php echo $populer['judul']; ?></strong>
                                        </div>
                                        <span class="badge bg-primary"><?php echo $populer['total_pinjam']; ?>x</span>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-danger" style="width: <?php echo $persen; ?>%"></div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php if(mysqli_num_rows($result_populer) == 0): ?>
                                <div class="list-group-item text-center text-muted">Belum ada data peminjaman</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7 mb-3">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-clock me-2 text-info"></i>Peminjaman Terbaru</h5>
                            <a href="peminjaman.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        32
                                            <th>No</th>
                                            <th>Siswa</th>
                                            <th>Buku</th>
                                            <th>Tanggal Pinjam</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        while($terbaru = mysqli_fetch_assoc($result_terbaru)):
                                            $status_class = $terbaru['status'] == 'dipinjam' ? 'badge-pinjam' : ($terbaru['status'] == 'telat' ? 'badge-telat' : 'badge-kembali');
                                            $status_icon = $terbaru['status'] == 'dipinjam' ? 'fa-book' : ($terbaru['status'] == 'telat' ? 'fa-exclamation-triangle' : 'fa-check');
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $terbaru['nama_lengkap']; ?></td>
                                            <td><?php echo $terbaru['judul']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($terbaru['tanggal_peminjaman'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                                    <?php echo ucfirst($terbaru['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php if(mysqli_num_rows($result_terbaru) == 0): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-2 d-block"></i>
                                                Belum ada data peminjaman
                                            </td>
                                        </tr>
                                        <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>