# Walkthrough: Redesign UI dari Figma

## Overview
Redesign tampilan frontend TOEFL Registration System berdasarkan Figma "Website Design for TOEFL Service". Semua PHP backend logic tetap dipertahankan.

## Files Changed

### 1. [style.css](file:///c:/laragon/www/toefl-registration/assets/css/style.css) — Full CSS Redesign
- **Color Palette** baru: Navy `#1E3A8A`, Gold `#FBBF24`, Orange `#F97316`
- **26+ CSS sections** covering all UI components
- Navbar putih (light) dengan brand icon kotak
- Hero section: dot pattern background, floating cards, golden highlight
- Statistics: 4-column grid with dividers
- Services: 6 cards (2×3) with colored icons & hover top-border
- Steps: horizontal flow with numbered circles & connecting line
- Schedule: card-based layout (bukan table)
- FAQ: accordion with rotating toggle icons
- Registration form: card-based inline form
- Footer: 4-column dark layout with social icons
- Scroll animations: `.animate-on-scroll` with Intersection Observer
- Full responsive breakpoints (991px, 768px, 576px)

### 2. [header.php](file:///c:/laragon/www/toefl-registration/includes/header.php) — Navbar Update
- Brand: Square icon + "UTM TOEFL" text
- Nav links: Beranda, Layanan, Jadwal, Cara Daftar, FAQ, Login (anchor links)
- CTA button: "Daftar Sekarang" (navy blue)
- Sidebar layout untuk logged-in users **tidak berubah**

### 3. [index.php](file:///c:/laragon/www/toefl-registration/index.php) — Landing Page Sections
- **Hero**: Badge "Layanan Resmi UTM", highlight TOEFL kuning, 3 checklist items, 2 CTA buttons, floating cards (5000+ Peserta, 4.9 Rating)
- **Statistics**: 4 stat items (Total Peserta, Tes Terselenggara, Jadwal Tersedia live dari DB, Rating)
- **Services**: 6 cards — Pendaftaran Online, Jadwal Fleksibel, Tes Komputer, Sertifikat Digital, Notifikasi, Dukungan
- **Steps**: 5-step flow — Buat Akun → Pilih Jadwal → Isi Formulir → Bayar → Ikuti Tes
- **Schedule**: Card-based layout dari database (date header, waktu/lokasi/biaya, quota badge)
- **FAQ**: 5 pertanyaan dengan accordion
- **Registration Form**: Preview form (disabled, redirects to register.php)

### 4. [footer.php](file:///c:/laragon/www/toefl-registration/includes/footer.php) — 4-Column Footer
- Brand + description + social icons
- Quick links (Beranda, Layanan, Jadwal, Login, Daftar)
- Help (FAQ, Cara Daftar, Privasi, Syarat)
- Contact (Alamat, Email, Telepon)

### 5. [app.js](file:///c:/laragon/www/toefl-registration/assets/js/app.js) — JavaScript Enhancements
- Navbar scroll shadow effect
- Active nav link highlighting on scroll
- Intersection Observer scroll animations
- Smooth scroll for anchor links (with mobile nav auto-close)
- FAQ accordion toggle function
- All original features preserved (sidebar, alerts, file upload, form validation, password toggle)

## What Was NOT Changed
- All PHP backend logic
- `config/app.php`, `config/database.php`
- `includes/functions.php`
- All `admin/*` pages (dashboard, jadwal, verifikasi, peserta, input-skor)
- All `mahasiswa/*` pages (dashboard, daftar, hasil, jadwal, kartu-peserta, notifikasi, profil, status)
- `login.php`, `register.php`, `forgot-password.php`, `logout.php` (only CSS changes affect them)
- `database.sql`

## Verification
- Open `http://localhost/toefl-registration/` in browser to see the redesigned landing page
- Test anchor links (click navbar items → scrolls to section)
- Test FAQ accordion (click to expand/collapse)
- Test schedule cards (if database has data)
- Test Login/Register navigation
