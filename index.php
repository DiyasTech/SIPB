
<?php
include "proses/koneksi.php";

if ($koneksi) {
    echo "mantap,udah nyambung";
}else {
    echo "yah,gagal nyambung";
}

if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($koneksi,
        "SELECT * FROM users WHERE username = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($hasil);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login SIPB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

<div class="container">
    <div class="login-card">
        <h3 class="text-center mb-2">SIPB</h3>
        <p class="text-center text-muted">
            Sistem Informasi Peminjaman Barang
        </p>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger">
                <?= e($error); ?>
            </div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username"
                    class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password"
                    class="form-control" required>
            </div>

            <button type="submit" name="login"
                class="btn btn-primary w-100">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
