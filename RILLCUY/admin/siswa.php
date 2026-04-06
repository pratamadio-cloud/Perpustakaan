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
        $nisn = mysqli_real_escape_string($conn, $_POST['nisn']);
        $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
        $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $nomor_tlp = mysqli_real_escape_string($conn, $_POST['nomor_tlp']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Insert ke tabel anggota
        $query_anggota = "INSERT INTO anggota (nisn, nama_lengkap, jenis_kelamin, kelas, alamat, nomor_tlp) 
                          VALUES ('$nisn', '$nama_lengkap', '$jenis_kelamin', '$kelas', '$alamat', '$nomor_tlp')";
        mysqli_query($conn, $query_anggota);
        $id_anggota = mysqli_insert_id($conn);
        
        // Insert ke tabel users
        $query_user = "INSERT INTO users (id_siswa, username, password, email, role) 
                       VALUES ('$id_anggota', '$username', '$password', '$email', 'siswa')";
        mysqli_query($conn, $query_user);
        
        echo "<script>alert('Siswa berhasil ditambahkan'); window.location='siswa.php';</script>";
    }
    elseif($action == 'edit') {
        $id = $_POST['id'];
        $nisn = mysqli_real_escape_string($conn, $_POST['nisn']);
        $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
        $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $nomor_tlp = mysqli_real_escape_string($conn, $_POST['nomor_tlp']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        // Update anggota
        $query_anggota = "UPDATE anggota SET nisn='$nisn', nama_lengkap='$nama_lengkap', 
                          jenis_kelamin='$jenis_kelamin', kelas='$kelas', alamat='$alamat', nomor_tlp='$nomor_tlp' 
                          WHERE id_anggota='$id'";
        mysqli_query($conn, $query_anggota);
        
        // Update users
        $query_user = "UPDATE users SET username='$username', email='$email' WHERE id_siswa='$id'";
        mysqli_query($conn, $query_user);
        
        // Update password jika diisi
        if(!empty($_POST['password'])) {
            $password = $_POST['password'];
            $query_pass = "UPDATE users SET password='$password' WHERE id_siswa='$id'";
            mysqli_query($conn, $query_pass);
        }
        
        echo "<script>alert('Siswa berhasil diupdate'); window.location='siswa.php';</script>";
    }
    elseif($action == 'delete') {
        $id = $_POST['id'];
        $query = "DELETE FROM anggota WHERE id_anggota='$id'";
        mysqli_query($conn, $query);
        
        echo "<script>alert('Siswa berhasil dihapus'); window.location='siswa.php';</script>";
    }
}

// Ambil data siswa
$query_siswa = "SELECT a.*, u.username, u.email FROM anggota a 
                LEFT JOIN users u ON a.id_anggota = u.id_siswa 
                ORDER BY a.id_anggota DESC";
$result_siswa = mysqli_query($conn, $query_siswa);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa - Admin Perpustakaan</title>
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
                <a href="kategori.php" class="nav-link">
                    <i class="fas fa-tags me-2"></i>Manajemen Kategori
                </a>
                <a href="siswa.php" class="nav-link active">
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
                <h2>Manajemen Siswa</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Tambah Siswa
                </button>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                32<th>No</th>
                                    <th>NISN</th>
                                    <th>Nama Lengkap</th>
                                    <th>Kelas</th>
                                    <th>Jenis Kelamin</th>
                                    <th>No Telepon</th>
                                    <th>Username</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while($siswa = mysqli_fetch_assoc($result_siswa)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $siswa['nisn']; ?></td>
                                    <td><?php echo $siswa['nama_lengkap']; ?></td>
                                    <td><?php echo $siswa['kelas']; ?></td>
                                    <td><?php echo $siswa['jenis_kelamin']; ?></td>
                                    <td><?php echo $siswa['nomor_tlp']; ?></td>
                                    <td><?php echo $siswa['username']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit" 
                                                data-id="<?php echo $siswa['id_anggota']; ?>"
                                                data-nisn="<?php echo $siswa['nisn']; ?>"
                                                data-nama="<?php echo $siswa['nama_lengkap']; ?>"
                                                data-jk="<?php echo $siswa['jenis_kelamin']; ?>"
                                                data-kelas="<?php echo $siswa['kelas']; ?>"
                                                data-alamat="<?php echo htmlspecialchars($siswa['alamat']); ?>"
                                                data-tlp="<?php echo $siswa['nomor_tlp']; ?>"
                                                data-username="<?php echo $siswa['username']; ?>"
                                                data-email="<?php echo $siswa['email']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $siswa['id_anggota']; ?>">
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

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>NISN</label>
                                <input type="text" name="nisn" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control" required>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Kelas</label>
                                <input type="text" name="kelas" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label>No Telepon</label>
                                <input type="text" name="nomor_tlp" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
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

<!-- Modal Edit Siswa -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>NISN</label>
                                <input type="text" name="nisn" id="edit_nisn" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="edit_jk" class="form-control" required>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Kelas</label>
                                <input type="text" name="kelas" id="edit_kelas" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Alamat</label>
                                <textarea name="alamat" id="edit_alamat" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label>No Telepon</label>
                                <input type="text" name="nomor_tlp" id="edit_tlp" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" id="edit_username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password (Kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                        </div>
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

<!-- Modal Hapus Siswa -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Apakah Anda yakin ingin menghapus siswa ini?</p>
                    <p class="text-danger">Perhatian: Data peminjaman siswa ini juga akan terhapus!</p>
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
        $('#edit_nisn').val($(this).data('nisn'));
        $('#edit_nama').val($(this).data('nama'));
        $('#edit_jk').val($(this).data('jk'));
        $('#edit_kelas').val($(this).data('kelas'));
        $('#edit_alamat').val($(this).data('alamat'));
        $('#edit_tlp').val($(this).data('tlp'));
        $('#edit_username').val($(this).data('username'));
        $('#edit_email').val($(this).data('email'));
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