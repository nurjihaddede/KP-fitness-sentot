# Fitness Sentot

Aplikasi web kerja praktek untuk pengelolaan member, kehadiran, pembayaran, dan laporan Fitness Sentot. Tersedia halaman admin serta halaman member untuk melihat dashboard dan profil.

## Kebutuhan

- PHP dengan ekstensi PDO MySQL. Sintaks kode telah diperiksa menggunakan PHP 8.2.12.
- MySQL atau MariaDB serta database proyek yang sesuai.
- Apache, misalnya melalui XAMPP.
- Koneksi internet untuk memuat Tailwind CSS, Font Awesome, dan Chart.js dari CDN.

## Menjalankan secara lokal

1. Clone repositori ini ke folder `htdocs` dengan nama `fitness-sentot`.
2. Salin `config/database.example.php` menjadi `config/database.php`.
3. Isi host, nama database, username, dan password pada `config/database.php` sesuai pengaturan lokal.
4. Siapkan database menggunakan struktur database proyek yang sudah ada. Repositori ini belum menyertakan berkas SQL; membuat database kosong saja belum cukup untuk menjalankan aplikasi.
5. Pastikan folder `assets/uploads` tersedia dan dapat ditulis oleh server web.
6. Jalankan Apache dan MySQL melalui XAMPP, lalu buka `http://localhost/fitness-sentot/`.
7. Gunakan akun yang telah tersedia pada database proyek.

## Struktur utama

- `admin/`: dashboard dan pengelolaan member, kehadiran, pembayaran, serta laporan.
- `member/`: dashboard dan profil member.
- `includes/`: autentikasi, fungsi bersama, header, dan footer.
- `config/database.example.php`: contoh konfigurasi koneksi database.
- `assets/`: CSS, JavaScript, dan folder unggahan pengguna.

Konfigurasi database lokal, berkas SQL, dan isi unggahan pengguna dikecualikan dari Git melalui `.gitignore`.
