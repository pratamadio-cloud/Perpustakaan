<?php
require_once 'config/database.php';

// Ambil 5 buku paling sering dipinjam
$query_populer = "SELECT b.*, k.nama_kategori, COUNT(p.id_peminjaman) as total_pinjam 
                  FROM buku b 
                  LEFT JOIN kategori k ON b.kategori = k.id_kategori 
                  LEFT JOIN peminjaman p ON b.id_buku = p.id_buku 
                  GROUP BY b.id_buku 
                  ORDER BY total_pinjam DESC 
                  LIMIT 5";
$result_populer = mysqli_query($conn, $query_populer);

// Ambil semua kategori
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$result_kategori = mysqli_query($conn, $query_kategori);

// Filter buku
$where = "";
if(isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $kategori_id = mysqli_real_escape_string($conn, $_GET['kategori']);
    $where = "WHERE b.kategori = '$kategori_id'";
}

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    if($where) {
        $where .= " AND (b.judul LIKE '%$search%' OR b.penulis LIKE '%$search%')";
    } else {
        $where = "WHERE (b.judul LIKE '%$search%' OR b.penulis LIKE '%$search%')";
    }
}

$query_buku = "SELECT b.*, k.nama_kategori, k.nomor_rak 
               FROM buku b 
               LEFT JOIN kategori k ON b.kategori = k.id_kategori 
               $where 
               ORDER BY b.id_buku DESC";
$result_buku = mysqli_query($conn, $query_buku);

include 'includes/header.php';
?>

<!-- Sidebar Kategori -->
<div class="col-md-3">
    <div class="sidebar p-3">
        <h5 class="mb-3"><i class="fas fa-tags me-2"></i>Kategori Buku</h5>
        <a href="index.php" class="nav-link <?php echo !isset($_GET['kategori']) ? 'active' : ''; ?>">
            <i class="fas fa-book me-2"></i>Semua Buku
        </a>
        <?php while($kategori = mysqli_fetch_assoc($result_kategori)): ?>
            <a href="?kategori=<?php echo $kategori['id_kategori']; ?>" 
               class="nav-link <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $kategori['id_kategori']) ? 'active' : ''; ?>">
                <i class="fas fa-folder me-2"></i><?php echo $kategori['nama_kategori']; ?>
                <small class="text-muted">(Rak: <?php echo $kategori['nomor_rak']; ?>)</small>
            </a>
        <?php endwhile; ?>
    </div>
</div>

<!-- Main Content -->
<div class="col-md-9">
    <!-- Search Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari buku berdasarkan judul atau penulis..." 
                           value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Buku Populer -->
    <div class="mb-5">
        <h4 class="mb-3"><i class="fas fa-fire text-danger me-2"></i>Buku Paling Sering Dipinjam</h4>
        <div class="row">
            <?php 
            $populer_data = [];
            while($row = mysqli_fetch_assoc($result_populer)) {
                $populer_data[] = $row;
            }
            $count = 0;
            foreach($populer_data as $buku): 
                if($count >= 5) break;
                $count++;
            ?>
                <div class="col-md-3 mb-3">
                    <div class="card card-book h-100">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo substr($buku['judul'], 0, 30); ?></h6>
                            <small class="text-muted">Penulis: <?php echo $buku['penulis']; ?></small><br>
                            <small class="text-muted">Stok: <?php echo $buku['stok']; ?></small>
                            <div class="mt-2">
                                <span class="badge bg-warning">
                                    <i class="fas fa-chart-line me-1"></i><?php echo $buku['total_pinjam']; ?>x dipinjam
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Semua Buku -->
    <h4 class="mb-3"><i class="fas fa-book me-2"></i>Koleksi Buku</h4>
    <div class="row">
        <?php if(mysqli_num_rows($result_buku) > 0): ?>
            <?php while($buku = mysqli_fetch_assoc($result_buku)): ?>
                <div class="col-md-3 mb-3">
                    <div class="card card-book h-100">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo substr($buku['judul'], 0, 30); ?></h6>
                            <small class="text-muted">Penulis: <?php echo $buku['penulis']; ?></small><br>
                            <small class="text-muted">Penerbit: <?php echo $buku['penerbit']; ?></small><br>
                            <small class="text-muted">Kategori: <?php echo $buku['nama_kategori']; ?></small><br>
                            <small class="text-muted">Stok: <?php echo $buku['stok']; ?></small>
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'siswa' && $buku['stok'] > 0): ?>
                                <button class="btn btn-sm btn-primary mt-2 btn-pinjam" 
                                        data-id="<?php echo $buku['id_buku']; ?>"
                                        data-judul="<?php echo $buku['judul']; ?>">
                                    <i class="fas fa-hand-peace me-1"></i>Pinjam
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Tidak ada buku ditemukan.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>