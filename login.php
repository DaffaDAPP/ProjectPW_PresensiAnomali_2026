<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_karyawan.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND role = 'karyawan'");
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['PASSWORD'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            if (isset($_POST['remember'])) {
                setcookie('remember_user', $user['id'], time() + (86400 * 7), "/");
            }
            header("Location: dashboard_karyawan.php");
            exit;
        }
    }
    $error = "Email atau password karyawan salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Karyawan - HRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="card card-auth p-4">
        <h4 class="text-center mb-3 fw-bold text-primary">Login Karyawan</h4>
        <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="rem">
                <label class="form-check-label" for="rem">Ingat Saya</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
        <div class="text-center mt-3">
            <small>Belum punya akun? <a href="register.php">Daftar</a></small><br>
            <small><a href="login_admin.php" class="text-secondary">Masuk sebagai Admin HR</a></small>
        </div>
    </div>
</div>
</body>
</html>