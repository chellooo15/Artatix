# Artatix Final — Struktur Modular

- `components/`: potongan tampilan (navbar, footer, flash, event card, dll).
- `actions/`: controller dan base Controller.
- `config/`: bootstrap, helpers, Flash, konfigurasi aplikasi.
- `database/`: Database dan migrasi.
- `middleware/`: autentikasi.
- `repositories/`: akses data.
- `pages/`: halaman dan layout.

Jalankan dari root proyek: `php -S 0.0.0.0:5000 index.php` (untuk routing dinamis; aset statis sebaiknya gunakan router khusus atau web server).
