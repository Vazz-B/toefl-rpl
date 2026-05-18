<?php
/**
 * Kartu Peserta TOEFL
 * Generate HTML preview kartu peserta (PDF generation memerlukan library FPDF)
 */
require_once __DIR__ . '/../includes/functions.php';
requireMahasiswa();

$db = getDB();
$userId = $_SESSION['user_id'];
$pendaftaranId = intval($_GET['id'] ?? 0);

// Ambil data pendaftaran
$stmt = $db->prepare("
    SELECT p.*, j.tanggal, j.waktu_mulai, j.waktu_selesai, j.lokasi,
           u.nama_lengkap, u.nim, u.prodi, u.email
    FROM pendaftaran p
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ? AND p.user_id = ? AND p.status IN ('verified', 'completed')
");
$stmt->execute([$pendaftaranId, $userId]);
$data = $stmt->fetch();

if (!$data) {
    setFlash('warning', 'Kartu peserta tidak tersedia. Pastikan pembayaran sudah diverifikasi.');
    redirect('/backend/mahasiswa/status.php');
}

// Jika request download (print mode)
$printMode = isset($_GET['print']);

if ($printMode):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Peserta TOEFL - <?= e($data['nomor_peserta']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; padding: 20px; }
        
        .card-container {
            width: 600px; margin: 0 auto; background: #fff;
            border: 2px solid #1a2540; border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #1a2540, #253456);
            color: #fff; padding: 24px 30px; text-align: center;
        }
        .card-header h2 { font-size: 1.3rem; font-weight: 800; margin-bottom: 4px; }
        .card-header p { font-size: 0.85rem; opacity: 0.8; }
        
        .card-body { padding: 30px; }
        .card-body table { width: 100%; border-collapse: collapse; }
        .card-body td { padding: 8px 0; font-size: 0.9rem; }
        .card-body td:first-child { color: #6c757d; width: 140px; }
        .card-body td:last-child { font-weight: 600; }
        
        .card-number {
            text-align: center; margin: 20px 0; padding: 16px;
            background: #fdebd4; border-radius: 8px;
        }
        .card-number .label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; }
        .card-number .number { font-size: 1.5rem; font-weight: 800; color: #f07b3f; }
        
        .card-footer {
            background: #f5f7fa; padding: 16px 30px;
            text-align: center; font-size: 0.75rem; color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        
        .print-note { text-align: center; margin-top: 20px; color: #6c757d; font-size: 0.85rem; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .print-note { display: none; }
            .card-container { box-shadow: none; border: 2px solid #1a2540; }
        }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="card-header">
            <h2>KARTU PESERTA TES TOEFL</h2>
            <p>UPT Bahasa Universitas Trunojoyo Madura</p>
        </div>
        <div class="card-body">
            <div class="card-number">
                <div class="label">Nomor Peserta</div>
                <div class="number"><?= e($data['nomor_peserta']) ?></div>
            </div>
            <table>
                <tr>
                    <td>Nama Lengkap</td>
                    <td><?= e($data['nama_lengkap']) ?></td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td><?= e($data['nim']) ?></td>
                </tr>
                <tr>
                    <td>Program Studi</td>
                    <td><?= e($data['prodi']) ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?= e($data['email']) ?></td>
                </tr>
                <tr><td colspan="2"><hr style="margin: 8px 0; border-color: #e9ecef;"></td></tr>
                <tr>
                    <td>Tanggal Tes</td>
                    <td><?= formatTanggal($data['tanggal']) ?></td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td><?= formatWaktu($data['waktu_mulai']) ?> - <?= formatWaktu($data['waktu_selesai']) ?></td>
                </tr>
                <tr>
                    <td>Lokasi</td>
                    <td><?= e($data['lokasi']) ?></td>
                </tr>
            </table>
        </div>
        <div class="card-footer">
            Kartu ini wajib dibawa saat pelaksanaan tes. Hadir 30 menit sebelum tes dimulai.<br>
            Dicetak pada: <?= formatTanggal(date('Y-m-d')) ?>
        </div>
    </div>
    <p class="print-note">Tekan <strong>Ctrl+P</strong> untuk mencetak atau simpan sebagai PDF.</p>
    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
<?php
exit;
endif;

// Normal view
$pageTitle = 'Kartu Peserta';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-card-heading me-2 text-accent"></i>Kartu Peserta
</h4>

<div class="content-card">
    <div class="card-header-custom">
        <h5>Preview Kartu Peserta</h5>
        <a href="<?= BASE_URL ?>/backend/mahasiswa/kartu-peserta.php?id=<?= $pendaftaranId ?>&print=1" target="_blank" class="btn btn-accent btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak / Download PDF
        </a>
    </div>
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="border rounded-custom p-4">
                    <div class="text-center mb-4" style="background: linear-gradient(135deg, #1a2540, #253456); color: #fff; padding: 20px; border-radius: 8px;">
                        <h5 class="fw-800 mb-1">KARTU PESERTA TES TOEFL</h5>
                        <small>UPT Bahasa Universitas Trunojoyo Madura</small>
                    </div>
                    
                    <div class="text-center mb-4">
                        <small class="text-muted text-uppercase" style="letter-spacing:1px;">Nomor Peserta</small>
                        <h3 class="fw-800 text-accent"><?= e($data['nomor_peserta']) ?></h3>
                    </div>
                    
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted" width="150">Nama Lengkap</td><td><strong><?= e($data['nama_lengkap']) ?></strong></td></tr>
                        <tr><td class="text-muted">NIM</td><td><?= e($data['nim']) ?></td></tr>
                        <tr><td class="text-muted">Program Studi</td><td><?= e($data['prodi']) ?></td></tr>
                        <tr><td colspan="2"><hr></td></tr>
                        <tr><td class="text-muted">Tanggal Tes</td><td><strong><?= formatTanggal($data['tanggal']) ?></strong></td></tr>
                        <tr><td class="text-muted">Waktu</td><td><?= formatWaktu($data['waktu_mulai']) ?> - <?= formatWaktu($data['waktu_selesai']) ?></td></tr>
                        <tr><td class="text-muted">Lokasi</td><td><?= e($data['lokasi']) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
