
<?php
include "proses/koneksi.php";

$cek = mysqli_query($koneksi, "SELECT * FROM users");

if (mysqli_num_rows($cek) > 0) {
    die("Akun sudah tersedia. Hapus file buat_admin.php.");
}

$nama = "Administrator";
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "Admin";

$stmt = mysqli_prepare($koneksi,
    "INSERT INTO users (nama, username, password, role)
     VALUES (?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, "ssss",
    $nama, $username, $password, $role
);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin berhasil dibuat!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "Silakan hapus file buat_admin.php setelah selesai.";
} else {
    echo "Gagal membuat admin.";
}
?>
