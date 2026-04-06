<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Filter tanggal
$where = "";
if(isset($_GET['tanggal_awal']) && !empty($_GET['tanggal_awal'])) {
    $tanggal_awal = mysqli_real_escape_string($conn, $_GET['tanggal_awal']);
    $where .= " AND DATE(p.tanggal_peminjaman) >= '$tanggal_awal'";
}
if(isset($_GET['tanggal_akhir']) && !empty($_GET['tanggal_akhir'])) {
    $tanggal_akhir = mysqli_real_escape_string($conn, $_GET['tanggal_akhir']);
    $where .= " AND DATE(p.tanggal_peminjaman) <= '$tanggal_akhir'";
}

// Ambil data riwayat peminjaman
$query_riwayat = "SELECT p.*, a.nama_lengkap, a.kelas, b.judul, b.penulis 
                  FROM peminjaman p 
                  JOIN anggota a ON p.id_siswa = a.id_anggota 
                  JOIN buku b ON p.id_buku = b.id_buku 
                  WHERE p.status IN ('kembali', 'telat') $where 
                  ORDER BY p.tanggal_peminjaman DESC";
$result_riwayat = mysqli_query($conn, $query_riwayat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Admin Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sidebar {
            background: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            border-radius: 10px;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .badge-kembali {
            background-color: #28a745;
            color: white;
        }
        .badge-telat {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-custom navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="dashboard.php">
            <i class="fas fa-book-open me-2"></i>Admin Perpustakaan
        </a>
        <div class="ms-auto">
            <span class="text-white me-3">Welcome, <?php echo $_SESSION['username']; ?></span>
            <a href="../logout.php" class="btn btn-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <div class="sidebar p-3">
                <h5 class="mb-3">Menu Admin</h5>
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="buku.php" class="nav-link">
                    <i class="fas fa-book me-2"></i>Manajemen Buku
                </a>
                <a href="kategori.php" class="nav-link">
                    <i class="fas fa-tags me-2"></i>Manajemen Kategori
                </a>
                <a href="siswa.php" class="nav-link">
                    <i class="fas fa-users me-2"></i>Manajemen Siswa
                </a>
                <a href="peminjaman.php" class="nav-link">
                    <i class="fas fa-hand-peace me-2"></i>Manajemen Peminjaman
                </a>
                <a href="riwayat.php" class="nav-link active">
                    <i class="fas fa-history me-2"></i>Riwayat Peminjaman
                </a>
            </div>
        </div>
        
        <div class="col-md-10">
            <h2 class="mb-4">Riwayat Peminjaman</h2>
            
            <!-- Filter Tanggal -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row">
                        <div class="col-md-4">
                            <label>Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control" 
                                   value="<?php echo isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control" 
                                   value="<?php echo isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="riwayat.php" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                32<th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Judul Buku</th>
                                    <th>Penulis</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                while($riwayat = mysqli_fetch_assoc($result_riwayat)):
                                ?>
                                32
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $riwayat['nama_lengkap']; ?></td>
                                    <td><?php echo $riwayat['kelas']; ?></td>
                                    <td><?php echo $riwayat['judul']; ?></td>
                                    <td><?php echo $riwayat['penulis']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($riwayat['tanggal_peminjaman'])); ?></td>
                                    <td>
                                        <?php echo $riwayat['tanggal_kembali'] ? date('d/m/Y H:i', strtotime($riwayat['tanggal_kembali'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if($riwayat['status'] == 'kembali'): ?>
                                            <span class="badge badge-kembali">Kembali</span>
                                        <?php else: ?>
                                            <span class="badge badge-telat">Telat</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if(mysqli_num_rows($result_riwayat) == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data riwayat peminjaman</td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>