<?php
/**
 * Landing Page
 * Halaman utama publik
 */
require_once __DIR__ . '/backend/includes/functions.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/backend/admin/dashboard.php');
    } else {
        redirect('/backend/mahasiswa/dashboard.php');
    }
}

// Ambil jadwal tes aktif
$db = getDB();
$stmt = $db->query("
    SELECT j.*, 
           j.kuota - COALESCE(COUNT(p.id), 0) as sisa_kuota
    FROM jadwal_tes j
    LEFT JOIN pendaftaran p ON j.id = p.jadwal_id AND p.status != 'rejected'
    WHERE j.status = 'aktif' AND j.tanggal >= CURDATE()
    GROUP BY j.id
    ORDER BY j.tanggal ASC
    LIMIT 6
");
$jadwalList = $stmt->fetchAll();

$pageTitle = 'Beranda';
require_once __DIR__ . '/frontend/templates/header.php';
?>

<!-- ============ HERO SECTION ============ -->
<section class="hero-section" id="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-star-fill"></i>
                    Layanan Resmi Universitas Trunojoyo Madura
                </div>
                <h1>Sistem Pelayanan<br><span class="hero-highlight">TOEFL</span> Online UTM</h1>
                <p class="hero-desc">Platform pendaftaran dan pelaksanaan tes TOEFL terpadu khusus untuk
                    mahasiswa Universitas Trunojoyo Madura. Mudah, cepat, dan terpercaya.</p>
                <ul class="hero-checklist">
                    <li><i class="bi bi-check-circle-fill"></i> Pendaftaran online 24/7 tanpa antri</li>
                    <li><i class="bi bi-check-circle-fill"></i> Jadwal tes tersedia setiap minggu</li>
                    <li><i class="bi bi-check-circle-fill"></i> Sertifikat digital terverifikasi resmi</li>
                </ul>
                <div class="hero-buttons">
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-hero-primary">
                        Daftar TOEFL Sekarang <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/jadwal.php" class="btn btn-hero-secondary">
                        Lihat Jadwal Tes
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-visual">
                    <!-- Student Image -->
                    <div class="hero-image-wrapper">
                        <img src="<?= BASE_URL ?>/frontend/assets/images/hero-student.png" alt="Mahasiswa UTM" class="hero-student-img">
                    </div>
                    <!-- Floating Card: 5000+ Peserta -->
                    <div class="floating-card floating-card-1">
                        <div class="fc-icon green">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <div class="fc-value">5.000+</div>
                            <div class="fc-label">Peserta Lulus</div>
                        </div>
                    </div>
                    <!-- Floating Card: Testimonial -->
                    <div class="floating-card floating-card-quote">
                        <div class="fc-quote-text">"Raih Skor TOEFL Terbaikmu!"</div>
                        <div class="fc-quote-sub">Bersama ribuan mahasiswa UTM yang telah berhasil</div>
                    </div>
                    <!-- Floating Card: Rating -->
                    <div class="floating-card floating-card-2">
                        <div class="fc-icon gold">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div>
                            <div class="fc-value">4.9/5</div>
                            <div class="fc-label">Rating Kepuasan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ STATISTICS ============ -->
<section class="stats-section">
    <div class="container">
        <div class="stats-container animate-on-scroll">
            <div class="stat-item">
                <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
                <div class="stat-value">5,200+</div>
                <div class="stat-label">Total Peserta</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon orange"><i class="bi bi-journal-check"></i></div>
                <div class="stat-value">120+</div>
                <div class="stat-label">Tes Terselenggara</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon green"><i class="bi bi-calendar-event"></i></div>
                <div class="stat-value"><?= count($jadwalList) ?></div>
                <div class="stat-label">Jadwal Tersedia</div>
            </div>
            <div class="stat-item">
                <div class="stat-icon gold"><i class="bi bi-star-fill"></i></div>
                <div class="stat-value">4.9</div>
                <div class="stat-label">Rating Layanan</div>
            </div>
        </div>
    </div>
</section>

<!-- ============ SERVICES / LAYANAN ============ -->
<section class="services-section" id="layanan">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-badge"><i class="bi bi-grid-3x3-gap-fill"></i> Layanan Kami</div>
            <h2 class="section-title section-title-center">Mengapa Mendaftar Online?</h2>
            <p class="section-subtitle section-subtitle-center">Nikmati kemudahan pendaftaran TOEFL dengan berbagai
                fitur unggulan kami</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="service-card animate-on-scroll animate-delay-1">
                    <div class="service-icon blue"><i class="bi bi-laptop"></i></div>
                    <h5>Pendaftaran Online</h5>
                    <p>Daftar tes TOEFL dari mana saja tanpa harus datang ke kantor UPT Bahasa. Cukup melalui browser.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card animate-on-scroll animate-delay-2">
                    <div class="service-icon orange"><i class="bi bi-calendar-check"></i></div>
                    <h5>Jadwal Fleksibel</h5>
                    <p>Pilih jadwal tes yang sesuai dengan ketersediaan waktu Anda. Lihat kuota peserta secara
                        real-time.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card animate-on-scroll animate-delay-3">
                    <div class="service-icon green"><i class="bi bi-display"></i></div>
                    <h5>Tes Berbasis Komputer</h5>
                    <p>Pengalaman tes modern dengan sistem berbasis komputer yang reliable dan akurat.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card animate-on-scroll animate-delay-4">
                    <div class="service-icon purple"><i class="bi bi-file-earmark-check"></i></div>
                    <h5>Sertifikat Digital</h5>
                    <p>Dapatkan sertifikat TOEFL resmi dari UPT Bahasa yang diakui dan bisa diakses secara digital.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card animate-on-scroll animate-delay-5">
                    <div class="service-icon pink"><i class="bi bi-bell"></i></div>
                    <h5>Notifikasi Real-time</h5>
                    <p>Dapatkan notifikasi otomatis untuk setiap update status pendaftaran dan pembayaran Anda.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="service-card animate-on-scroll animate-delay-6">
                    <div class="service-icon teal"><i class="bi bi-headset"></i></div>
                    <h5>Dukungan Bantuan</h5>
                    <p>Tim support siap membantu Anda jika mengalami kendala selama proses pendaftaran dan tes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ REGISTRATION STEPS ============ -->
<section class="steps-section" id="cara-daftar">
    <div class="container">
        <div class="text-center mb-4">
            <div class="section-badge"><i class="bi bi-signpost-split-fill"></i> Cara Daftar</div>
            <h2 class="section-title section-title-center">Langkah Pendaftaran</h2>
            <p class="section-subtitle section-subtitle-center">Ikuti 5 langkah mudah berikut untuk mendaftar tes TOEFL
            </p>
        </div>
        <div class="steps-flow animate-on-scroll">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-title">Buat Akun</div>
                <div class="step-desc">Daftar akun baru dengan email aktif</div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-title">Pilih Jadwal</div>
                <div class="step-desc">Pilih jadwal tes yang tersedia</div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-title">Isi Formulir</div>
                <div class="step-desc">Lengkapi data pendaftaran</div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-title">Bayar</div>
                <div class="step-desc">Upload bukti pembayaran biaya tes</div>
            </div>
            <div class="step-item">
                <div class="step-number">5</div>
                <div class="step-title">Ikuti Tes</div>
                <div class="step-desc">Datang sesuai jadwal & ikuti tes</div>
            </div>
        </div>
    </div>
</section>

<!-- ============ SCHEDULE SECTION ============ -->
<section class="schedule-section" id="jadwal">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-badge"><i class="bi bi-calendar2-week-fill"></i> Jadwal Tes</div>
            <h2 class="section-title section-title-center">Jadwal Tes Tersedia</h2>
            <p class="section-subtitle section-subtitle-center">Pilih jadwal yang paling sesuai dengan ketersediaan
                waktu Anda</p>
        </div>

        <?php if (empty($jadwalList)): ?>
            <div class="empty-state animate-on-scroll">
                <i class="bi bi-calendar-x d-block"></i>
                <h5>Belum Ada Jadwal</h5>
                <p>Saat ini belum ada jadwal tes TOEFL yang tersedia. Silakan cek kembali nanti.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($jadwalList as $j): ?>
                    <div class="col-md-6 col-lg-4 animate-on-scroll">
                        <div class="schedule-card">
                            <div class="schedule-card-header">
                                <div class="schedule-date"><?= formatTanggal($j['tanggal']) ?></div>
                                <div class="schedule-day"><?= date('l', strtotime($j['tanggal'])) ?></div>
                            </div>
                            <div class="schedule-card-body">
                                <div class="schedule-info-row">
                                    <i class="bi bi-clock"></i>
                                    <span><?= formatWaktu($j['waktu_mulai']) ?> - <?= formatWaktu($j['waktu_selesai']) ?>
                                        WIB</span>
                                </div>
                                <div class="schedule-info-row">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?= e($j['lokasi']) ?></span>
                                </div>
                                <div class="schedule-info-row">
                                    <i class="bi bi-cash-stack"></i>
                                    <strong><?= formatRupiah($j['biaya']) ?></strong>
                                </div>
                            </div>
                            <div class="schedule-card-footer">
                                <?php if ($j['sisa_kuota'] > 5): ?>
                                    <span class="quota-badge available">
                                        <i class="bi bi-check-circle-fill"></i> <?= $j['sisa_kuota'] ?> kuota
                                    </span>
                                <?php elseif ($j['sisa_kuota'] > 0): ?>
                                    <span class="quota-badge limited">
                                        <i class="bi bi-exclamation-circle-fill"></i> <?= $j['sisa_kuota'] ?> kuota
                                    </span>
                                <?php else: ?>
                                    <span class="quota-badge full">
                                        <i class="bi bi-x-circle-fill"></i> Penuh
                                    </span>
                                <?php endif; ?>

                                <?php if ($j['sisa_kuota'] > 0): ?>
                                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-schedule">
                                        <i class="bi bi-pencil-square"></i> Daftar
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-schedule" disabled>Penuh</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ FAQ SECTION ============ -->
<section class="faq-section" id="faq">
    <div class="container">
        <div class="text-center mb-4">
            <div class="section-badge"><i class="bi bi-question-circle-fill"></i> FAQ</div>
            <h2 class="section-title section-title-center">Pertanyaan yang Sering Diajukan</h2>
            <p class="section-subtitle section-subtitle-center">Temukan jawaban untuk pertanyaan umum tentang tes TOEFL
                di UPT Bahasa UTM</p>
        </div>
        <div class="faq-list animate-on-scroll">
            <div class="faq-item active">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>Apa saja persyaratan untuk mengikuti tes TOEFL?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Anda harus terdaftar sebagai mahasiswa aktif Universitas Trunojoyo Madura. Siapkan KTM (Kartu
                        Tanda Mahasiswa) yang masih berlaku atau identitas resmi lainnya saat mendaftar dan mengikuti
                        tes.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>Berapa biaya mengikuti tes TOEFL?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Biaya tes TOEFL bervariasi tergantung jadwal yang dipilih. Informasi biaya dapat dilihat pada
                        halaman jadwal tes. Pembayaran dilakukan via transfer bank dan bukti pembayaran diunggah melalui
                        sistem.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>Kapan hasil tes TOEFL diumumkan?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Hasil tes TOEFL biasanya diumumkan dalam waktu 3-7 hari kerja setelah pelaksanaan tes. Anda
                        dapat melihat hasil tes langsung melalui dashboard akun Anda di sistem ini.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>Bisakah saya mengubah jadwal setelah mendaftar?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Perubahan jadwal dapat dilakukan dengan menghubungi admin UPT Bahasa sebelum batas waktu
                        pendaftaran ditutup. Pastikan untuk menghubungi pihak administrasi minimal 3 hari sebelum jadwal
                        tes Anda.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>Apakah sertifikat TOEFL ini diakui secara nasional?</span>
                    <div class="faq-toggle"><i class="bi bi-chevron-down"></i></div>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Sertifikat TOEFL ITP yang diterbitkan oleh UPT Bahasa UTM diakui secara nasional dan dapat
                        digunakan untuk keperluan akademik seperti syarat wisuda, beasiswa, dan melamar kerja.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ QUICK REGISTRATION FORM ============ -->
<section class="regform-section" id="daftar">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <div class="section-badge"><i class="bi bi-pencil-square"></i> Pendaftaran</div>
                    <h2 class="section-title section-title-center">Daftar Tes TOEFL</h2>
                    <p class="section-subtitle section-subtitle-center">Lengkapi formulir berikut untuk mulai mendaftar
                        tes TOEFL</p>
                </div>
                <div class="regform-card animate-on-scroll">
                    <form action="<?= BASE_URL ?>/register.php" method="GET">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" placeholder="Masukkan nama lengkap" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIM</label>
                                <input type="text" class="form-control" placeholder="Masukkan NIM" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="Masukkan email aktif" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fakultas</label>
                                <select class="form-select" disabled>
                                    <option selected>Pilih Fakultas</option>
                                    <option>Fakultas Teknik</option>
                                    <option>Fakultas Ekonomi dan Bisnis</option>
                                    <option>Fakultas Hukum</option>
                                    <option>Fakultas Ilmu Sosial dan Budaya</option>
                                    <option>Fakultas Pertanian</option>
                                    <option>Fakultas Ilmu Pendidikan</option>
                                    <option>Fakultas Keislaman</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Sesi Tes</label>
                                <select class="form-select" disabled>
                                    <option selected>Pilih sesi tes yang tersedia</option>
                                    <?php foreach ($jadwalList as $j): ?>
                                        <option><?= formatTanggal($j['tanggal']) ?> — <?= formatWaktu($j['waktu_mulai']) ?>
                                            (<?= $j['sisa_kuota'] ?> kuota)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mt-4 text-center">
                                <a href="<?= BASE_URL ?>/register.php" class="btn btn-regform">
                                    <i class="bi bi-send"></i> Buat Akun & Daftar
                                </a>
                                <p class="text-muted small mt-3">Anda perlu membuat akun terlebih dahulu untuk
                                    melanjutkan pendaftaran</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/frontend/templates/footer.php'; ?>