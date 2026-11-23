<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $cookie_id = mysqli_real_escape_string($conn, $_COOKIE['remember_user']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$cookie_id'");
    if ($row = mysqli_fetch_assoc($query)) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['nama']    = $row['nama'];
        $_SESSION['role']    = $row['role'];
    }
}

function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function cekAdmin() {
    cekLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: dashboard_karyawan.php");
        exit;
    }
}

function cekKaryawan() {
    cekLogin();
    if ($_SESSION['role'] !== 'karyawan') {
        header("Location: dashboard.php");
        exit;
    }
}
?>