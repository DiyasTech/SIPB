
<?php
include "proses/koneksi.php";
cek_login();

$pesan = "";

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $stok = (int) $_POST['stok'];
    $kondisi = $_POST['kondisi'];

    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO barang
        (nama_barang, kategori, stok, stok_tersedia, kondisi)
        VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "ssiis",
        $nama, $kategori, $stok, $stok, $kondisi
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: barang.php");
        exit;
    }
    $pesan = "Gagal menambahkan barang.";
}

if (isset($_POST['update'])) {
    $id = (int) $_POST['id_barang'];
    $nama = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $stok = (int) $_POST['stok'];
    $kondisi = $_POST['kondisi'];

    $stmt = mysqli_prepare($koneksi,
        "UPDATE barang
         SET nama_barang=?, kategori=?, stok=?,
         stok_tersedia=stok_tersedia + (? - stok), kondisi=?
         WHERE id_barang=?"
    );

    mysqli_stmt_bind_param($stmt, "ssiisi",
        $nama, $kategori, $stok, $stok, $kondisi, $id
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: barang.php");
        exit;
    }
    $pesan = "Gagal mengubah barang.";
}


if (isset($_POST['hapus'])) {
    $id = (int) $_POST['id_barang'];

    mysqli_begin_transaction($koneksi);

    try {
        $stmt = mysqli_prepare($koneksi,
            "DELETE FROM pengembalian
             WHERE id_peminjaman IN (
                 SELECT id_peminjaman
                 FROM peminjaman
                 WHERE id_barang = ?
             )"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $stmt = mysqli_prepare($koneksi,
            "DELETE FROM peminjaman WHERE id_barang = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        // Hapus barang
        $stmt = mysqli_prepare($koneksi,
            "DELETE FROM barang WHERE id_barang = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        mysqli_commit($koneksi);

        header("Location: barang.php");
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        $pesan = "Gagal menghapus barang: " . $e->getMessage();
    }
}


$edit = null;

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = mysqli_prepare($koneksi,
        "SELECT * FROM barang WHERE id_barang=?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$data = mysqli_query($koneksi,
    "SELECT * FROM barang ORDER BY id_barang DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Barang</title>
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

    <h3>Data Barang</h3>

    <?php if ($pesan != "") { ?>
        <div class="alert alert-danger"><?= e($pesan); ?></div>
    <?php } ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5><?= $edit ? "Edit Barang" : "Tambah Barang"; ?></h5>

            <form method="POST">
                <input type="hidden" name="id_barang"
                    value="<?= e($edit['id_barang'] ?? ''); ?>">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang"
                            class="form-control"
                            value="<?= e($edit['nama_barang'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="col-md-3">
                        <label>Kategori</label>
                        <input type="text" name="kategori"
                            class="form-control"
                            value="<?= e($edit['kategori'] ?? ''); ?>">
                    </div>

                    <div class="col-md-2">
                        <label>Jumlah Stok</label>
                        <input type="number" name="stok"
                            class="form-control" min="0"
                            value="<?= e($edit['stok'] ?? 0); ?>"
                            required>
                    </div>

                    <div class="col-md-2">
                        <label>Kondisi</label>
                        <select name="kondisi" class="form-select">
                            <option value="Baik"
                                <?= ($edit['kondisi'] ?? '') == 'Baik' ? 'selected' : ''; ?>>
                                Baik
                            </option>
                            <option value="Rusak"
                                <?= ($edit['kondisi'] ?? '') == 'Rusak' ? 'selected' : ''; ?>>
                                Rusak
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <?php if ($edit) { ?>
                            <button name="update" class="btn btn-warning w-100">
                                Update
                            </button>
                        <?php } else { ?>
                            <button name="simpan" class="btn btn-primary w-100">
                                Simpan
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Daftar Barang</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Tersedia</th>
                            <th>Kondisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= e($row['nama_barang']); ?></td>
                            <td><?= e($row['kategori']); ?></td>
                            <td><?= e($row['stok']); ?></td>
                            <td><?= e($row['stok_tersedia']); ?></td>
                            <td><?= e($row['kondisi']); ?></td>
                            <td>
                                <a href="?edit=<?= $row['id_barang']; ?>"
                                    class="btn btn-warning btn-sm">Edit</a>

                                <form method="POST" class="d-inline"
                                    onsubmit="return confirm('Hapus barang ini?')">
                                    <input type="hidden" name="id_barang"
                                        value="<?= $row['id_barang']; ?>">
                                    <button name="hapus"
                                        class="btn btn-danger btn-sm">
                                        Hapus
                                    </button>
                                </form>
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
