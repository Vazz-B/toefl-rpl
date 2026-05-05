<?php
/**
 * Footer Template
 */
?>
    <?php if (isLoggedIn()): ?>
                </div><!-- /.container-fluid -->
            </main>
        </div><!-- /#page-content-wrapper -->
    </div><!-- /#wrapper -->
    <?php else: ?>
    <!-- Public Footer -->
    <footer class="public-footer">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        <div class="footer-brand-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <span class="footer-brand-text">UTM TOEFL</span>
                    </div>
                    <p class="footer-desc">Sistem Manajemen Pendaftaran TOEFL berbasis web untuk mahasiswa Universitas Trunojoyo Madura. Mudah, cepat, dan terpercaya.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Tautan Cepat</h6>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/">Beranda</a></li>
                        <li><a href="#layanan">Layanan</a></li>
                        <li><a href="#jadwal">Jadwal Tes</a></li>
                        <li><a href="<?= BASE_URL ?>/login.php">Login</a></li>
                        <li><a href="<?= BASE_URL ?>/register.php">Daftar</a></li>
                    </ul>
                </div>

                <!-- Help & Legal -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Bantuan</h6>
                    <ul class="footer-links">
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#cara-daftar">Cara Daftar</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Kontak</h6>
                    <ul class="footer-contact list-unstyled">
                        <li>
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Jl. Raya Telang, Perumahan Telang Inda, Telang, Kec. Kamal, Kabupaten Bangkalan, Jawa Timur 69162</span>
                        </li>
                        <li>
                            <i class="bi bi-envelope-fill"></i>
                            <span>upt-bahasa@trunojoyo.ac.id</span>
                        </li>
                        <li>
                            <i class="bi bi-telephone-fill"></i>
                            <span>(031) 3011146</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> <?= APP_ORG ?>. All rights reserved.
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
