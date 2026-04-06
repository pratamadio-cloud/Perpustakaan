$(document).ready(function() {
    // Auto-hide alert after 3 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
    
    // Fungsi peminjaman buku
    $('.btn-pinjam').click(function() {
        var id_buku = $(this).data('id');
        var judul = $(this).data('judul');
        
        if(confirm('Apakah Anda yakin ingin meminjam buku "' + judul + '"?')) {
            $.ajax({
                url: 'proses.php',
                type: 'POST',
                data: {
                    action: 'pinjam',
                    id_buku: id_buku
                },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    if(response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
});