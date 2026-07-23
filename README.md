#  Web Pengaduan Fasilitas Labkom

Aplikasi berbasis web yang dirancang untuk memudahkan pengaduan dan pelaporan masalah fasilitas di Laboratorium Komputer. Sistem ini memungkinkan pengguna untuk mengirimkan laporan kerusakan beserta bukti foto, serta mempermudah admin dalam mengelola dan merekap data pengaduan secara terstruktur.

---

##  Fitur Utama

- **Sistem Otentikasi (Login/Logout):** Akses terpisah untuk pengguna/pelapor dan admin.
- **Form Pengaduan:** Pengiriman laporan pengaduan dilengkapi dengan fitur *upload* bukti gambar/foto.
- **Dashboard Admin:** Pengelolaan status pengaduan dan pemantauan laporan yang masuk.
- **Rekapitulasi Data Laporan:** Fitur untuk melihat serta mengunduh (*download*) rekap rekapitulasi data laporan.
- **API Endpoint (`api.php`):** Menyediakan antarmuka data untuk kebutuhan pemrosesan laporan.

---

##  Teknologi yang Digunakan

- **Frontend & UI:** HTML5, CSS3, JavaScript
- **Backend:** PHP (Native)
- **Database:** MySQL
- **Desain UI/UX:** Figma

---

##  Struktur Direktori Proyek

```text
webPengaduanASM1/
├── admin.php           # Halaman kelola laporan untuk Admin
├── api.php             # Endpoint pemrosesan API
├── config.php          # Konfigurasi koneksi database
├── download_rekap.php  # Fitur untuk mengunduh rekap data
├── functions.php       # Fungsi-fungsi bantu (helper functions)
├── index.php           # Halaman utama / landing page
├── login.php           # Halaman login
├── logout.php          # Proses logout
├── proses.php          # Pemroses data form/pengaduan
├── rekap.php           # Tampilan rekap data laporan
└── uploads/            # Direktori penyimpanan bukti laporan
    └── bukti/          # Folder file gambar bukti pengaduan
