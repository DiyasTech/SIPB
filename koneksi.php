
<?php
session_start();

$host = "sql205.infinityfree.com";
$user = "if0_43438307";
$password = "cxwLTAK9PyTBA";
$database = "if0_43438307_db_peminjaman_barang";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

function cek_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: index.php");
        exit;
    }
}
?>
