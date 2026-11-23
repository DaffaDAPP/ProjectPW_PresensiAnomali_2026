<?php
require_once 'auth.php';
cekAdmin();

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    mysqli_query($conn, "DELETE FROM presensi WHERE id = $id");
}

header("Location: dashboard.php?msg=deleted");
exit;
?>