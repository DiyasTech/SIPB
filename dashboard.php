
<?php
include "proses/koneksi.php";
cek_login();

$barang = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM barang"
));

$peminjam = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM peminjam"
));

$pinjam = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM peminjaman WHERE status='Dipinjam'"
));

$kembali = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM pengembalian"
));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard SIPB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">SIPB</a>
        <div class="text-white">
            <?= e($_SESSION['nama']); ?> |
            <a href="logout.php" class="text-wh ite">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3>Dashboard</h3>
    <p>Selamat datang di Sistem Informasi Peminjaman Barang.</p>

    <div class="row g-3 mt-2">

        <div class="col-md-3">
            <div class="card dashboard-card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Barang</h6>
                    <h2><?= $barang['total']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card bg-success text-white">
                <div class="card-body">
                    <h6>Total Peminjam</h6>
                    <h2><?= $peminjam['total']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card bg-warning text-dark">
                <div class="card-body">
                    <h6>Sedang Dipinjam</h6>
                    <h2><?= $pinjam['total']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card bg-info text-white">
                <div class="card-body">
                    <h6>Total Pengembalian</h6>
                    <h2><?= $kembali['total']; ?></h2>
                </div>
            </div>
        </div>

    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5>Menu Aplikasi</h5>

            <div class="row g-2 mt-2">
                <div class="col-md-3">
                    <a href="barang.php" class="btn btn-outline-primary w-100">
                        Data Barang
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="peminjam.php" class="btn btn-outline-primary w-100">
                        Data Peminjam
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="peminjaman.php" class="btn btn-outline-primary w-100">
                        Peminjaman
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="pengembalian.php" class="btn btn-outline-primary w-100">
                        Pengembalian
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
