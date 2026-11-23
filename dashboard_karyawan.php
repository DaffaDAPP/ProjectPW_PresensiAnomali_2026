<?php
require_once 'auth.php';
cekKaryawan();

$user_id = $_SESSION['user_id'];
$tanggal_hari_ini = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_presensi'])) {
    $jam_sekarang = $_POST['simulasi_jam'];
    $ip_input     = mysqli_real_escape_string($conn, $_POST['simulasi_ip']);

    if ($_POST['aksi_presensi'] === 'masuk') {
        $status = 'Normal';
        $ket    = 'Presensi tepat waktu dari jaringan kantor.';

        if ($ip_input !== '192.168.1.1' && $ip_input !== '127.0.0.1') {
            $status = 'IP Mencurigakan';
            $ket    = 'Sistem mendeteksi akses di luar IP Jaringan Kantor (' . $ip_input . ').';
        } elseif ($jam_sekarang > '09:00:00') {
            $status = 'Terlambat Ekstrem';
            $ket    = 'Check-in melewati batas toleransi pukul 09:00.';
        }

        mysqli_query($conn, "INSERT INTO presensi (user_id, tanggal, jam_masuk, lokasi_ip, status_anomali, keterangan) 
                             VALUES ($user_id, '$tanggal_hari_ini', '$jam_sekarang', '$ip_input', '$status', '$ket')");
    } elseif ($_POST['aksi_presensi'] === 'keluar') {
        $id_log = (int) $_POST['id_log'];
        $data_log = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM presensi WHERE id = $id_log"));

        $selisih_detik = strtotime($jam_sekarang) - strtotime($data_log['jam_masuk']);
        $jam_kerja = $selisih_detik / 3600;

        $status = $data_log['status_anomali'];
        $ket    = $data_log['keterangan'];

        if ($jam_kerja < 4 || $jam_kerja > 14) {
            $status = 'Durasi Tidak Wajar';
            $ket    = 'Total durasi kerja tercatat ' . round($jam_kerja, 1) . ' jam (Anomali Durasi).';
        }

        mysqli_query($conn, "UPDATE presensi SET jam_keluar = '$jam_sekarang', status_anomali = '$status', keterangan = '$ket' WHERE id = $id_log");
    }
    header("Location: dashboard_karyawan.php");
    exit;
}

$log_hari_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM presensi WHERE user_id = $user_id AND tanggal = '$tanggal_hari_ini' ORDER BY id DESC LIMIT 1"));
$riwayat = mysqli_query($conn, "SELECT * FROM presensi WHERE user_id = $user_id ORDER BY tanggal DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portal Karyawan - Presensi HRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="#">HRIS | Portal Karyawan</a>
    <div class="ms-auto text-white d-flex align-items-center gap-3">
        <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
        <a href="logout.php" class="btn btn-sm btn-light text-primary fw-bold">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card p-4 shadow-sm">
                <h5 class="fw-bold">Form Presensi & Simulasi Anomali</h5>
                <p class="text-muted small">Waktu Server: <span id="liveClock" class="fw-bold text-dark"></span></p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Simulasi Jam (Untuk Pengujian)</label>
                        <input type="time" name="simulasi_jam" class="form-control" value="<?= date('H:i') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IP Address Terdeteksi</label>
                        <input type="text" name="simulasi_ip" class="form-control" value="192.168.1.1" required>
                        <small class="text-muted">IP Kantor valid: <code>192.168.1.1</code>. Ubah ke IP lain untuk menguji deteksi anomali.</small>
                    </div>

                    <?php if (!$log_hari_ini || $log_hari_ini['jam_keluar'] !== null): ?>
                        <button type="submit" name="aksi_presensi" value="masuk" class="btn btn-success w-100">Check-In (Masuk)</button>
                    <?php else: ?>
                        <input type="hidden" name="id_log" value="<?= $log_hari_ini['id'] ?>">
                        <button type="submit" name="aksi_presensi" value="keluar" class="btn btn-danger w-100">Check-Out (Pulang)</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card p-4 shadow-sm">
                <h5 class="fw-bold mb-3">Riwayat Presensi Saya</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($r = mysqli_fetch_assoc($riwayat)): 
                            $badge = ($r['status_anomali'] === 'Normal') ? 'bg-success' : 'bg-danger';
                        ?>
                            <tr>
                                <td><?= $r['tanggal'] ?></td>
                                <td><?= $r['jam_masuk'] ?></td>
                                <td><?= $r['jam_keluar'] ?? 'Belum Pulang' ?></td>
                                <td><span class="badge <?= $badge ?>"><?= $r['status_anomali'] ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>