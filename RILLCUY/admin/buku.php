<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses CRUD
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action == 'add') {
        $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
        $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
        $tahun_terbit = mysqli_real_escape_string($conn, $_POST['tahun_terbit']);
        $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $stok = mysqli_real_escape_string($conn, $_POST['stok']);
        
        $query = "INSERT INTO buku (isbn, judul, penulis, penerbit, tahun_terbit, kategori, deskripsi, stok) 
                  VALUES ('$isbn', '$judul', '$penulis', '$penerbit', '$tahun_terbit', '$kategori', '$deskripsi', '$stok')";
        mysqli_query($conn, $query);
        
        echo "<script>alert('Buku berhasil ditambahkan'); window.location='buku.php';</script>";
    }
    elseif($action == 'edit') {
        $id = $_POST['id'];
        $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
        $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
        $tahun_terbit = mysqli_real_escape_string($conn, $_POST['tahun_terbit']);
        $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $stok = mysqli_real_escape_string($conn, $_POST['stok']);
        
        $query = "UPDATE buku SET isbn='$isbn', judul='$judul', penulis='$penulis', penerbit='$penerbit', 
                  tahun_terbit='$tahun_terbit', kategori='$kategori', deskripsi='$deskripsi', stok='$stok' 
                  WHERE id_buku='$id'";
        mysqli_query($conn, $query);
        
        echo "<script>alert('Buku berhasil diupdate'); window.location='buku.php';</script>";
    }
    elseif($action == 'delete') {
        $id = $_POST['id'];
        $query = "DELETE FROM buku WHERE id_buku='$id'";
        mysqli_query($conn, $query);
        
        echo "<script>alert('Buku berhasil dihapus'); window.location='buku.php';</script>";
    }
}

// Ambil data buku
$query_buku = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori = k.id_kategori ORDER BY b.id_buku DESC";
$result_buku = mysqli_query($conn, $query_buku);

// Ambil data kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$result_kategori = mysqli_query($conn, $query_kategori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Buku - Admin Perpustakaan</title>
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
                <a href="buku.php" class="nav-link active">
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
                <a href="riwayat.php" class="nav-link">
                    <i class="fas fa-history me-2"></i>Riwayat Peminjaman
                </a>
            </div>
        </div>
        
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manajemen Buku</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Tambah Buku
                </button>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                32<tr>
                                    <th>ISBN</th>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Penerbit</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($buku = mysqli_fetch_assoc($result_buku)): ?>
                                <tr>
                                    <td><?php echo $buku['isbn']; ?></td>
                                    <td><?php echo $buku['judul']; ?></td>
                                    <td><?php echo $buku['penulis']; ?></td>
                                    <td><?php echo $buku['penerbit']; ?></td>
                                    <td><?php echo $buku['nama_kategori']; ?></td>
                                    <td><?php echo $buku['stok']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit" 
                                                data-id="<?php echo $buku['id_buku']; ?>"
                                                data-isbn="<?php echo $buku['isbn']; ?>"
                                                data-judul="<?php echo $buku['judul']; ?>"
                                                data-penulis="<?php echo $buku['penulis']; ?>"
                                                data-penerbit="<?php echo $buku['penerbit']; ?>"
                                                data-tahun="<?php echo $buku['tahun_terbit']; ?>"
                                                data-kategori="<?php echo $buku['kategori']; ?>"
                                                data-deskripsi="<?php echo htmlspecialchars($buku['deskripsi']); ?>"
                                                data-stok="<?php echo $buku['stok']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $buku['id_buku']; ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Buku -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label>ISBN</label>
                        <input type="text" name="isbn" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Judul Buku</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Penulis</label>
                        <input type="text" name="penulis" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Penerbit</label>
                        <input type="text" name="penerbit" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Kategori</label>
                        <select name="kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php 
                            mysqli_data_seek($result_kategori, 0);
                            while($kategori = mysqli_fetch_assoc($result_kategori)): 
                            ?>
                                <option value="<?php echo $kategori['id_kategori']; ?>"><?php echo $kategori['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Stok</label>
                        <input type="number" name="stok" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Buku -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label>ISBN</label>
                        <input type="text" name="isbn" id="edit_isbn" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Judul Buku</label>
                        <input type="text" name="judul" id="edit_judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Penulis</label>
                        <input type="text" name="penulis" id="edit_penulis" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Penerbit</label>
                        <input type="text" name="penerbit" id="edit_penerbit" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" id="edit_tahun" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Kategori</label>
                        <select name="kategori" id="edit_kategori" class="form-control" required>
                            <?php 
                            mysqli_data_seek($result_kategori, 0);
                            while($kategori = mysqli_fetch_assoc($result_kategori)): 
                            ?>
                                <option value="<?php echo $kategori['id_kategori']; ?>"><?php echo $kategori['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Stok</label>
                        <input type="number" name="stok" id="edit_stok" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Buku -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Apakah Anda yakin ingin menghapus buku ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.btn-edit').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_isbn').val($(this).data('isbn'));
        $('#edit_judul').val($(this).data('judul'));
        $('#edit_penulis').val($(this).data('penulis'));
        $('#edit_penerbit').val($(this).data('penerbit'));
        $('#edit_tahun').val($(this).data('tahun'));
        $('#edit_kategori').val($(this).data('kategori'));
        $('#edit_deskripsi').val($(this).data('deskripsi'));
        $('#edit_stok').val($(this).data('stok'));
        $('#editModal').modal('show');
    });
    
    $('.btn-delete').click(function() {
        $('#delete_id').val($(this).data('id'));
        $('#deleteModal').modal('show');
    });
});
</script>
</body>
</html>