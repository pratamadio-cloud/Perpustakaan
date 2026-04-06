<?php
require_once 'config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id_buku = mysqli_real_escape_string($conn, $_POST['id_buku']);
    $id_siswa = $_SESSION['id_siswa'];
    
    if($action == 'pinjam') {
        // Cek stok
        $query_stok = "SELECT stok FROM buku WHERE id_buku = '$id_buku'";
        $result_stok = mysqli_query($conn, $query_stok);
        $buku = mysqli_fetch_assoc($result_stok);
        
        if($buku['stok'] > 0) {
            // Kurangi stok
            mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");
            
            // Tambah peminjaman
            $tanggal = date('Y-m-d H:i:s');
            $query_pinjam = "INSERT INTO peminjaman (id_siswa, id_buku, tanggal_peminjaman, status) 
                             VALUES ('$id_siswa', '$id_buku', '$tanggal', 'dipinjam')";
            mysqli_query($conn, $query_pinjam);
            
            echo json_encode(['success' => true, 'message' => 'Buku berhasil dipinjam']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Stok buku habis']);
        }
    }
    exit();
}
?>