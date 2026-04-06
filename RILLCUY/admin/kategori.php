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
        $nama_kategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
        $nomor_rak = mysqli_real_escape_string($conn, $_POST['nomor_rak']);
        
        $query = "INSERT INTO kategori (nama_kategori, nomor_rak) VALUES ('$nama_kategori', '$nomor_rak')";
        mysqli_query($conn, $query);
        
        echo "<script>alert('Kategori berhasil ditambahkan'); window.location='kategori.php';</script>";
    }
    elseif($action == 'edit') {
        $id = $_POST['id'];
        $nama_kategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
        $nomor_rak = mysqli_real_escape_string($conn, $_POST['nomor_rak']);
        
        $query = "UPDATE kategori SET nama_kategori='$nama_kategori', nomor_rak='$nomor_rak' WHERE id_kategori='$id'";
        mysqli_query($conn, $query);
        
        echo "<script>alert('Kategori berhasil diupdate'); window.location='kategori.php';</script>";
    }
    elseif($action == 'delete') {
        $id = $_POST['id'];
        $query = "DELETE FROM kategori WHERE id_kategori='$id'";
        mysqli_query($conn, $query);
        
        echo "<script>alert('Kategori berhasil dihapus'); window.location='kategori.php';</script>";
    }
}

// Ambil data kategori
$query_kategori = "SELECT * FROM kategori ORDER BY id_kategori DESC";
$result_kategori = mysqli_query($conn, $query_kategori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Admin Perpustakaan</title>
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
                <a href="buku.php" class="nav-link">
                    <i class="fas fa-book me-2"></i>Manajemen Buku
                </a>
                <a href="kategori.php" class="nav-link active">
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
                <h2>Manajemen Kategori</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Tambah Kategori
                </button>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                32<th>No</th>
                                    <th>Nama Kategori</th>
                                    <th>Nomor Rak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while($kategori = mysqli_fetch_assoc($result_kategori)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $kategori['nama_kategori']; ?></td>
                                    <td><?php echo $kategori['nomor_rak']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit" 
                                                data-id="<?php echo $kategori['id_kategori']; ?>"
                                                data-nama="<?php echo $kategori['nama_kategori']; ?>"
                                                data-rak="<?php echo $kategori['nomor_rak']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $kategori['id_kategori']; ?>">
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

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label>Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nomor Rak</label>
                        <input type="text" name="nomor_rak" class="form-control" placeholder="Contoh: A-01" required>
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

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label>Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nomor Rak</label>
                        <input type="text" name="nomor_rak" id="edit_rak" class="form-control" required>
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

<!-- Modal Hapus Kategori -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Apakah Anda yakin ingin menghapus kategori ini?</p>
                    <p class="text-danger">Perhatian: Buku dengan kategori ini juga akan terhapus!</p>
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
        $('#edit_nama').val($(this).data('nama'));
        $('#edit_rak').val($(this).data('rak'));
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