<?php
include('include/navbar.php'); 
include('include/koneksi.php');

// Tangani tombol "Sudah Bayar"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar'])) {
    $id = $_POST['id_menu'];
    $qty = (int)$_POST['qty'];

    // Ambil stok sekarang
    $stokQuery = mysqli_query($conn, "SELECT stok FROM menu WHERE id_menu='$id'");
    $stokData = mysqli_fetch_assoc($stokQuery);

    if ($stokData && $qty > 0 && $qty <= $stokData['stok']) {
        // Kurangi stok
        $update = mysqli_query($conn, "UPDATE menu SET stok = stok - $qty WHERE id_menu='$id'");

        if ($update) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<script>alert('Gagal memperbarui stok.');</script>";
        }
    } else {
        echo "<script>alert('Stok tidak mencukupi.');</script>";
    }
}
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contact Us</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="padding: 70px;">
        <div class="container my-5">
            <h2>Pesan Makanan/Minuman di Kantin</h2>

            <?php
            // Ambil daftar kantin unik
            $kantinQuery = mysqli_query($conn, "SELECT DISTINCT p.nama_kantin, p.id_penjual FROM penjual p JOIN menu m ON p.id_penjual = m.id_penjual");
            while ($kantinRow = mysqli_fetch_assoc($kantinQuery)) {
                $namaKantin = $kantinRow['nama_kantin'];
                $idPenjual = $kantinRow['id_penjual'];
                echo "<h3>Kantin: " . htmlspecialchars($namaKantin) . "</h3>";

                // Ambil menu dari kantin ini
                $menuQuery = mysqli_query($conn, "SELECT * FROM menu WHERE id_penjual='" . mysqli_real_escape_string($conn, $idPenjual) . "'");
                echo '<div class="row">';
                while ($row = mysqli_fetch_assoc($menuQuery)) {
            ?>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <img src="asset/<?php echo htmlspecialchars($row['gambar']); ?>" class="card-img-top" alt="..." width="320" height="320">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['nama_menu']); ?></h5>
                        <p>Harga: Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?> - Stok: <?php echo (int)$row['stok']; ?></p>
                        <form onsubmit="event.preventDefault(); showQRModal('<?php echo htmlspecialchars($row['nama_menu']); ?>', <?php echo $row['harga']; ?>, <?php echo $row['id_menu']; ?>, this.qty.value)">
                            <input type="hidden" name="id_menu" value="<?php echo $row['id_menu']; ?>">
                            <input type="number" name="qty" value="1" min="1" max="<?php echo $row['stok']; ?>" class="form-control mb-2" required>
                            <button type="submit" class="btn btn-primary w-100">Pesan</button>
                        </form>

                    </div>
                </div>
            </div>
            <?php
                }
                echo '</div>'; // tutup row
            }
            ?>

        </div>
    </body>
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p><strong id="namaMenu"></strong> x <span id="jumlahQty"></span></p>
                    <p>Total: <strong>Rp <span id="totalHarga"></span></strong></p>
                    <img id="qrImage" src="asset/qr.png" width="200" alt="QR Code">
                    <form method="post" class="mt-3" action="order.php">
                        <input type="hidden" name="id_menu" id="modalIdMenu">
                        <input type="hidden" name="qty" id="modalQty">
                        <button type="submit" name="bayar" class="btn btn-success">Sudah Bayar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showQRModal(nama, harga, id, qty) {
            const total = harga * qty;
            document.getElementById("namaMenu").textContent = nama;
            document.getElementById("jumlahQty").textContent = qty;
            document.getElementById("totalHarga").textContent = total.toLocaleString("id-ID");
            document.getElementById("modalIdMenu").value = id;
            document.getElementById("modalQty").value = qty;
            const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
            qrModal.show();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</html>
