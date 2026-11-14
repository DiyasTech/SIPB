
<?php
include "proses/koneksi.php";
cek_login();

$pesan = "";

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_peminjam'];
    $nim = $_POST['nim'];
    $jurusan = $_POST['jurusan'];
    $hp = $_POST['no_hp'];

    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO peminjam
        (nama_peminjam, nim, jurusan, no_hp)
        VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "ssss", $nama, $nim, $jurusan, $hp);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: peminjam.php");
        exit;
    }
    $pesan = "Gagal menyimpan data.";
}

if (isset($_POST['update'])) {
    $id = (int) $_POST['id_peminjam'];
    $nama = $_POST['nama_peminjam'];
    $nim = $_POST['nim'];
    $jurusan = $_POST['jurusan'];
    $hp = $_POST['no_hp'];

    $stmt = mysqli_prepare($koneksi,
        "UPDATE peminjam SET
        nama_peminjam=?, nim=?, jurusan=?, no_hp=?
        WHERE id_peminjam=?"
    );

    mysqli_stmt_bind_param($stmt, "ssssi", $nama, $nim, $jurusan, $hp, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: peminjam.php");
        exit;
    }
    $pesan = "Gagal mengubah data.";
}

if (isset($_POST['hapus'])) {
    $id = (int) $_POST['id_peminjam'];

    $stmt = mysqli_prepare($koneksi,
        "DELETE FROM peminjam WHERE id_peminjam=?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: peminjam.php");
        exit;
    }
    $pesan = "Data tidak bisa dihapus karena sudah memiliki transaksi.";
}

$edit = null;

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = mysqli_prepare($koneksi,
        "SELECT * FROM peminjam WHERE id_peminjam=?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$data = mysqli_query($koneksi,
    "SELECT * FROM peminjam ORDER BY id_peminjam DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Peminjam</title>
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
    <h3>Data Peminjam</h3>

    <?php if ($pesan != "") { ?>
        <div class="alert alert-danger"><?= e($pesan); ?></div>
    <?php } ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5><?= $edit ? "Edit Peminjam" : "Tambah Peminjam"; ?></h5>

            <form method="POST">
                <input type="hidden" name="id_peminjam"
                    value="<?= e($edit['id_peminjam'] ?? ''); ?>">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Nama Peminjam</label>
                        <input type="text" name="nama_peminjam"
                            class="form-control"
                            value="<?= e($edit['nama_peminjam'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="col-md-2">
                        <label>NIM</label>
                        <input type="text" name="nim"
                            class="form-control"
                            value="<?= e($edit['nim'] ?? ''); ?>">
                    </div>

                    <div class="col-md-3">
                        <label>Jurusan</label>
                        <input type="text" name="jurusan"
                            class="form-control"
                            value="<?= e($edit['jurusan'] ?? ''); ?>">
                    </div>

                    <div class="col-md-2">
                        <label>No. HP</label>
                        <input type="text" name="no_hp"
                            class="form-control"
                            value="<?= e($edit['no_hp'] ?? ''); ?>">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button name="<?= $edit ? 'update' : 'simpan'; ?>"
                            class="btn btn-primary w-100">
                            <?= $edit ? 'Update' : 'Simpan'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Daftar Peminjam</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Jurusan</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= e($row['nama_peminjam']); ?></td>
                            <td><?= e($row['nim']); ?></td>
                            <td><?= e($row['jurusan']); ?></td>
                            <td><?= e($row['no_hp']); ?></td>
                            <td>
                                <a href="?edit=<?= $row['id_peminjam']; ?>"
                                    class="btn btn-warning btn-sm">Edit</a>

                                <form method="POST" class="d-inline"
                                    onsubmit="return confirm('Hapus peminjam ini?')">
                                    <input type="hidden" name="id_peminjam"
                                        value="<?= $row['id_peminjam']; ?>">
                                    <button name="hapus"
                                        class="btn btn-danger btn-sm">Hapus</button>
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
