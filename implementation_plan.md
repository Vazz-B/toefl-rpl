# Redesign UI Berdasarkan Figma Design

Mengubah tampilan frontend TOEFL Registration System agar sesuai dengan desain Figma "Website Design for TOEFL Service". Semua PHP backend logic tetap dipertahankan — hanya HTML/CSS yang berubah.

## Ringkasan Perubahan dari Figma

Dari analisis desain Figma, berikut elemen-elemen utama yang perlu diimplementasikan:

| Section | Status Saat Ini | Perubahan |
|---------|----------------|-----------|
| **Navbar** | Simple: Beranda, Login, Daftar | Tambah: Layanan, Jadwal, Cara Daftar, FAQ (anchor links) |
| **Hero Section** | Basic gradient + heading | Badge "Layanan Resmi UTM", highlight "TOEFL" kuning, checklist features, floating cards, dot pattern |
| **Statistics** | ❌ Tidak ada | 🆕 4 stat cards (peserta, tes, jadwal, rating) |
| **Services/Features** | 3 cards | 6 cards (2x3 grid): Pendaftaran Online, Jadwal Fleksibel, Tes Komputer, Sertifikat Digital, Notifikasi, Dukungan 24/7 |
| **Registration Steps** | ❌ Tidak ada | 🆕 5-step horizontal flow dengan connecting lines |
| **Schedule** | Table format | Cards dengan date header, quota badges (green/orange/red) |
| **FAQ** | ❌ Tidak ada | 🆕 Accordion-style FAQ section |
| **Registration Form** | ❌ Tidak ada (di landing) | 🆕 Inline form section (Nama, NIM, Email, Fakultas, Sesi) |
| **Footer** | 2 columns + kontak | 4 columns: Logo+desc, Quick Links, Help, Contact |

## Proposed Changes

### 1. Landing Page & Design System

---

#### [MODIFY] [style.css](file:///c:/laragon/www/toefl-registration/assets/css/style.css)

Perubahan besar pada CSS untuk implementasi desain Figma:

- **Navbar**: Tambah logo square icon, center nav links, CTA button style navy
- **Hero Section**: 
  - Background dark navy `#1E3A8A` dengan dot pattern SVG
  - Badge translucent untuk "Layanan Resmi UTM"
  - Text "TOEFL" di-highlight warna kuning/golden `#FBBF24`
  - Orange checklist items
  - Floating info cards ("5.000+ Peserta Lulus", "4.9/5 Rating")
  - 2 CTA buttons: Orange solid + White outline
- **Statistics Section**: 4 stat cards baris horizontal, light blue-grey bg
- **Services Section**: 6 cards grid (3 kolom × 2 baris), colored icon backgrounds  
- **Steps Flow**: Horizontal step indicator (1→5) dengan connecting lines, circular blue icons
- **Schedule Cards**: Card-based layout bukan table, date header biru, quota badges
- **FAQ Section**: Accordion dengan blue circular arrow icons
- **Registration Form**: Card dengan form fields (Nama, NIM, Email, dropdown Fakultas & Sesi)
- **Footer**: 4-column layout, dark navy blue background `#0F172A`

---

#### [MODIFY] [index.php](file:///c:/laragon/www/toefl-registration/index.php)

Restructure landing page HTML sesuai Figma:

1. **Hero Section** — Tambah badge, highlight text, checklist, floating cards  
2. **Statistics Section** — 🆕 4 stat cards  
3. **Services Section** — Expand dari 3 → 6 feature cards  
4. **Steps Section** — 🆕 Registration flow visual (5 steps)  
5. **Schedule Section** — Ubah dari table → card-based layout  
6. **FAQ Section** — 🆕 Accordion FAQ  
7. **Registration Form Section** — 🆕 Quick registration form  

> [!IMPORTANT]
> PHP backend query untuk jadwal tes tetap dipertahankan. Hanya HTML structure yang berubah.

---

#### [MODIFY] [header.php](file:///c:/laragon/www/toefl-registration/includes/header.php)

- Tambah nav links: Layanan, Jadwal, Cara Daftar, FAQ (sebagai anchor `#layanan`, `#jadwal`, dll)
- Update navbar brand dengan square icon + "UTM TOEFL" text
- CTA button "Daftar Sekarang" style navy blue (bukan orange)
- Sidebar layout untuk logged-in users **tidak berubah**

---

#### [MODIFY] [footer.php](file:///c:/laragon/www/toefl-registration/includes/footer.php)

- Expand ke 4 kolom: Logo & Deskripsi, Tautan Cepat, Bantuan/Legal (FAQ, Privasi), Kontak
- Tambah social media icons
- Copyright bar di bottom

---

### 2. Auth Pages (Minor Tweaks)

---

#### [MODIFY] [login.php](file:///c:/laragon/www/toefl-registration/login.php)

- Minor styling adjustment agar konsisten dengan color palette baru
- PHP logic tetap sama

#### [MODIFY] [register.php](file:///c:/laragon/www/toefl-registration/register.php)

- Minor styling adjustment agar konsisten dengan color palette baru
- PHP logic tetap sama

---

### 3. JavaScript

---

#### [MODIFY] [app.js](file:///c:/laragon/www/toefl-registration/assets/js/app.js)

- Tambah smooth scroll untuk anchor links di navbar (#layanan, #jadwal, #faq)
- Tambah FAQ accordion toggle logic
- Tambah scroll animation (intersection observer) untuk sections

## File yang TIDAK Berubah

Semua file PHP backend berikut **tidak dimodifikasi**:
- `config/app.php`, `config/database.php`
- `includes/functions.php`  
- `admin/*` (dashboard, jadwal, verifikasi, peserta, input-skor)
- `mahasiswa/*` (dashboard, daftar, hasil, jadwal, kartu-peserta, notifikasi, profil, status)
- `forgot-password.php`, `logout.php`
- `database.sql`

## Verification Plan

### Browser Testing
- Buka landing page `http://localhost/toefl-registration/` dan bandingkan visual dengan Figma
- Cek responsiveness di mobile view (resize browser)
- Test semua anchor links (scroll ke section yang benar)
- Test FAQ accordion buka/tutup
- Test navigasi ke Login & Register masih berfungsi

### Functional Testing
- Pastikan jadwal tes masih ditampilkan dari database
- Login/Register flow tetap berfungsi normal
- Redirect untuk logged-in user tetap berfungsi  
