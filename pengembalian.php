
<?php
include "proses/koneksi.php";
cek_login();

$pesan = "";

if (isset($_POST['simpan'])) {
    $id = (int) $_POST['id_peminjaman'];
    $tanggal = $_POST['tanggal_kembali'];
    $kondisi = $_POST['kondisi_barang'];
    $keterangan = $_POST['keterangan'];

    mysqli_begin_transaction($koneksi);

    try {
        $stmt = mysqli_prepare($koneksi,
            "SELECT id_barang, jumlah, status
             FROM peminjaman
             WHERE id_peminjaman=? FOR UPDATE"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $pinjam = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$pinjam || $pinjam['status'] != 'Dipinjam') {
            throw new Exception("Data peminjaman tidak ditemukan atau sudah dikembalikan!");
        }

        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO pengembalian
            (id_peminjaman, tanggal_kembali, kondisi_barang, keterangan)
            VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "isss",
            $id, $tanggal, $kondisi, $keterangan
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal menyimpan pengembalian!");
        }

        $stmt = mysqli_prepare($koneksi,
            "UPDATE peminjaman SET status='Dikembalikan'
             WHERE id_peminjaman=?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal mengubah status!");
        }

        $stmt = mysqli_prepare($koneksi,
            "UPDATE barang
             SET stok_tersedia = stok_tersedia + ?
             WHERE id_barang=?"
        );
        mysqli_stmt_bind_param($stmt, "ii",
            $pinjam['jumlah'], $pinjam['id_barang']
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui stok!");
        }

        mysqli_commit($koneksi);
        header("Location: pengembalian.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        $pesan = $e->getMessage();
    }
}

$data_pinjam = mysqli_query($koneksi,
    "SELECT p.id_peminjaman, pm.nama_peminjam,
            b.nama_barang, p.jumlah
     FROM peminjaman p
     JOIN peminjam pm ON p.id_peminjam=pm.id_peminjam
     JOIN barang b ON p.id_barang=b.id_barang
     WHERE p.status='Dipinjam'
     ORDER BY p.id_peminjaman DESC"
);

$data = mysqli_query($koneksi,
    "SELECT k.*, pm.nama_peminjam, b.nama_barang
     FROM pengembalian k
     JOIN peminjaman p ON k.id_peminjaman=p.id_peminjaman
     JOIN peminjam pm ON p.id_peminjam=pm.id_peminjam
     JOIN barang b ON p.id_barang=b.id_barang
     ORDER BY k.id_pengembalian DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengembalian Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">SIPB</a>
        <a href="logout.php" class="text-white">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">Kembali</a>
    <h3>Transaksi Pengembalian</h3>

    <?php if ($pesan != "") { ?>
        <div class="alert alert-danger"><?= e($pesan); ?></div>
    <?php } ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Form Pengembalian</h5>

            <form method="POST">
                <div class="mb-3">
                    <label>Pilih Peminjaman</label>
                    <select name="id_peminjaman" class="form-select" required>
                        <option value="">Pilih Transaksi</option>

                        <?php while ($p = mysqli_fetch_assoc($data_pinjam)) { ?>
                            <option value="<?= $p['id_peminjaman']; ?>">
                                <?= e($p['nama_peminjam']); ?> -
                                <?= e($p['nama_barang']); ?> -
                                Jumlah: <?= $p['jumlah']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Tanggal Pengembalian</label>
                    <input type="date" name="tanggal_kembali"
                        class="form-control"
                        value="<?= date('Y-m-d'); ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label>Kondisi Barang</label>
                    <select name="kondisi_barang" class="form-select" required>
                        <option value="Baik">Baik</option>
                        <option value="Rusak">Rusak</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"
                        rows="3"></textarea>
                </div>

                <button name="simpan" class="btn btn-primary">
                    Simpan Pengembalian
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Riwayat Pengembalian</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Tanggal Kembali</th>
                            <th>Kondisi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no=1; while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= e($row['nama_peminjam']); ?></td>
                            <td><?= e($row['nama_barang']); ?></td>
                            <td><?= e($row['tanggal_kembali']); ?></td>
                            <td><?= e($row['kondisi_barang']); ?></td>
                            <td><?= e($row['keterangan']); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
