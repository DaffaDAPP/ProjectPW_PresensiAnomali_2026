<?php
require_once 'auth.php';
cekAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id_presensi = (int) $_POST['id_presensi'];
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_anomali']);
    $keterangan  = mysqli_real_escape_string($conn, $_POST['keterangan']);

    mysqli_query($conn, "UPDATE presensi SET status_anomali = '$status_baru', keterangan = '$keterangan' WHERE id = $id_presensi");
    header("Location: dashboard.php?msg=updated");
    exit;
}

$total_data    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM presensi"))['total'];
$total_anomali = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM presensi WHERE status_anomali != 'Normal'"))['total'];

$query_presensi = mysqli_query($conn, "
    SELECT p.*, u.nama, u.email 
    FROM presensi p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.tanggal DESC, p.jam_masuk DESC
");

$data_presensi = [];
while ($row = mysqli_fetch_assoc($query_presensi)) {
    $data_presensi[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Deteksi Anomali Presensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="#">HRIS</a>
    <a class="navbar-brand text-danger" href="#">| Anomaly Detector</a>
    <div class="ms-auto text-white d-flex align-items-center gap-3">
        <span>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> (Admin)</span>
        <a href="logout.php" class="btn btn-danger btn-sm logout">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Data audit presensi berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3 border-start border-primary border-4 shadow-sm">
                <h6 class="text-muted">Total Log Presensi</h6>
                <h3 class="fw-bold"><?= $total_data ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 border-start border-danger border-4 shadow-sm">
                <h6 class="text-muted">Terdeteksi Anomali</h6>
                <h3 class="fw-bold text-danger"><?= $total_anomali ?></h3>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Log Presensi & Hasil Deteksi Sistem</h5>
            <select id="filterAnomali" class="form-select w-auto">
                <option value="all">Semua Status</option>
                <option value="Normal">Normal</option>
                <option value="Terlambat Ekstrem">Terlambat Ekstrem</option>
                <option value="Durasi Tidak Wajar">Durasi Tidak Wajar</option>
                <option value="IP Mencurigakan">IP Mencurigakan</option>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabelPresensi">
                <thead class="table-light">
                    <tr>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>IP Address</th>
                        <th>Status Deteksi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data_presensi as $row): 
                    $badge = ($row['status_anomali'] === 'Normal') ? 'bg-success' : 'bg-danger';
                ?>
                    <tr data-status="<?= htmlspecialchars($row['status_anomali']) ?>">
                        <td><strong><?= htmlspecialchars($row['nama']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small></td>
                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td><?= htmlspecialchars($row['jam_masuk']) ?></td>
                        <td><?= htmlspecialchars($row['jam_keluar'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($row['lokasi_ip']) ?></code></td>
                        <td><span class="badge <?= $badge ?> badge-anomali"><?= htmlspecialchars($row['status_anomali']) ?></span></td>
                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Audit</button>
                            <button type="button" onclick="konfirmasiHapus(<?= $row['id'] ?>)" class="btn btn-sm btn-danger">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($data_presensi as $row): ?>
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="dashboard.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Anomali - <?= htmlspecialchars($row['nama']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_presensi" value="<?= $row['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Status Klasifikasi</label>
                    <select name="status_anomali" class="form-select">
                        <option value="Normal" <?= $row['status_anomali']=='Normal'?'selected':'' ?>>Normal</option>
                        <option value="Terlambat Ekstrem" <?= $row['status_anomali']=='Terlambat Ekstrem'?'selected':'' ?>>Terlambat Ekstrem</option>
                        <option value="Durasi Tidak Wajar" <?= $row['status_anomali']=='Durasi Tidak Wajar'?'selected':'' ?>>Durasi Tidak Wajar</option>
                        <option value="IP Mencurigakan" <?= $row['status_anomali']=='IP Mencurigakan'?'selected':'' ?>>IP Mencurigakan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan Audit HR</label>
                    <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($row['keterangan']) ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_status" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>