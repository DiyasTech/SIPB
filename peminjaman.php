
<?php
include "proses/koneksi.php";
cek_login();

$pesan = "";

if (isset($_POST['simpan'])) {
    $id_peminjam = (int) $_POST['id_peminjam'];
    $id_barang = (int) $_POST['id_barang'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_rencana_kembali'];
    $jumlah = (int) $_POST['jumlah'];

    if ($jumlah < 1 || $tanggal_kembali < $tanggal_pinjam) {
        $pesan = "Jumlah atau tanggal peminjaman tidak valid!";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            $stmt = mysqli_prepare($koneksi,
                "SELECT stok_tersedia FROM barang
                 WHERE id_barang=? FOR UPDATE"
            );
            mysqli_stmt_bind_param($stmt, "i", $id_barang);
            mysqli_stmt_execute($stmt);

            $barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$barang || $barang['stok_tersedia'] < $jumlah) {
                throw new Exception("Stok barang tidak mencukupi!");
            }

            $stmt = mysqli_prepare($koneksi,
                "INSERT INTO peminjaman
                (id_peminjam, id_barang, tanggal_pinjam,
                 tanggal_rencana_kembali, jumlah, status)
                VALUES (?, ?, ?, ?, ?, 'Dipinjam')"
            );

            mysqli_stmt_bind_param($stmt, "iissi",
                $id_peminjam, $id_barang,
                $tanggal_pinjam, $tanggal_kembali, $jumlah
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal menyimpan peminjaman!");
            }

            $stmt = mysqli_prepare($koneksi,
                "UPDATE barang
                 SET stok_tersedia = stok_tersedia - ?
                 WHERE id_barang=?"
            );
            mysqli_stmt_bind_param($stmt, "ii", $jumlah, $id_barang);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal memperbarui stok!");
            }

            mysqli_commit($koneksi);
            header("Location: peminjaman.php");
            exit;

        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            $pesan = $e->getMessage();
        }
    }
}

$peminjam = mysqli_query($koneksi,
    "SELECT * FROM peminjam ORDER BY nama_peminjam"
);

$barang = mysqli_query($koneksi,
    "SELECT * FROM barang WHERE stok_tersedia > 0
     ORDER BY nama_barang"
);

$data = mysqli_query($koneksi,
    "SELECT p.*, pm.nama_peminjam, b.nama_barang
     FROM peminjaman p
     JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
     JOIN barang b ON p.id_barang = b.id_barang
     ORDER BY p.id_peminjaman DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peminjaman Barang</title>
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
    <h3>Transaksi Peminjaman</h3>

    <?php if ($pesan != "") { ?>
        <div class="alert alert-danger"><?= e($pesan); ?></div>
    <?php } ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Form Peminjaman</h5>

            <form method="POST">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label>Nama Peminjam</label>
                        <select name="id_peminjam" class="form-select" required>
                            <option value="">Pilih Peminjam</option>
                            <?php while ($p = mysqli_fetch_assoc($peminjam)) { ?>
                                <option value="<?= $p['id_peminjam']; ?>">
                                    <?= e($p['nama_peminjam']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Nama Barang</label>
                        <select name="id_barang" class="form-select" required>
                            <option value="">Pilih Barang</option>
                            <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
                                <option value="<?= $b['id_barang']; ?>">
                                    <?= e($b['nama_barang']); ?>
                                    (Stok: <?= $b['stok_tersedia']; ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah"
                            class="form-control" min="1" required>
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam"
                            class="form-control"
                            value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Rencana Pengembalian</label>
                        <input type="date" name="tanggal_rencana_kembali"
                            class="form-control"
                            min="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-12">
                        <button name="simpan" class="btn btn-primary">
                            Simpan Peminjaman
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Riwayat Peminjaman</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Tanggal Pinjam</th>
                            <th>Rencana Kembali</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no=1; while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= e($row['nama_peminjam']); ?></td>
                            <td><?= e($row['nama_barang']); ?></td>
                            <td><?= e($row['tanggal_pinjam']); ?></td>
                            <td><?= e($row['tanggal_rencana_kembali']); ?></td>
                            <td><?= e($row['jumlah']); ?></td>
                            <td>
                                <span class="badge <?= $row['status'] == 'Dipinjam' ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                    <?= e($row['status']); ?>
                                </span>
                            </td>
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
