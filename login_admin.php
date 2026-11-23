<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND role = 'admin'");
    if ($admin = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $admin['PASSWORD'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['nama']    = $admin['nama'];
            $_SESSION['role']    = $admin['role'];

            if (isset($_POST['remember'])) {
                setcookie('remember_user', $admin['id'], time() + (86400 * 7), "/");
            }
            header("Location: dashboard.php");
            exit;
        }
    }
    $error = "Kredensial Admin tidak valid!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin HR - HRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
<div class="container">
    <div class="card card-auth p-4">
        <h4 class="text-center mb-3 fw-bold text-danger">Portal Admin HR</h4>
        <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Admin</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remAdmin">
                <label class="form-check-label" for="remAdmin">Ingat Admin</label>
            </div>
            <button type="submit" class="btn btn-danger w-100">Login Admin</button>
        </form>
        <div class="text-center mt-3">
            <small><a href="login.php">Kembali ke Login Karyawan</a></small>
        </div>
    </div>
</div>
</body>
</html>