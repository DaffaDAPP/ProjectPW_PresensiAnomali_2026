<?php
require_once 'koneksi.php';

$email = 'admin@hris.com'; // ganti jika email admin kamu berbeda
$password_baru = '123456';
$hash = password_hash($password_baru, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = '$hash', role = 'admin' WHERE email = '$email'";
if (mysqli_query($conn, $sql)) {
    echo "Berhasil! Email: $email | Password: $password_baru";
} else {
    echo "Gagal: " . mysqli_error($conn);
}