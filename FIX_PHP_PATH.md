# Perbaikan Error PHP ENOENT

## Masalah
Error `spawn php ENOENT` terjadi karena PHP tidak ditemukan di PATH system.

## Solusi yang Sudah Diterapkan

### 1. File Konfigurasi Workspace
File `.vscode/settings.json` dan `.cursor/settings.json` telah dibuat untuk mengarahkan ke PHP XAMPP.

### 2. Menambahkan PHP ke PATH (Opsional - Recommended)

#### Metode 1: Melalui System Properties (Permanen)
1. Tekan `Win + R`, ketik `sysdm.cpl`, tekan Enter
2. Klik tab **Advanced**
3. Klik **Environment Variables**
4. Di bagian **System Variables**, cari dan pilih **Path**, klik **Edit**
5. Klik **New** dan tambahkan: `C:\xampp\php`
6. Klik **OK** pada semua dialog
7. **Restart** Cursor/VS Code dan terminal

#### Metode 2: Melalui PowerShell (Sementara - Hanya untuk session ini)
Buka PowerShell sebagai Administrator dan jalankan:
```powershell
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\xampp\php", "Machine")
```

Setelah itu, restart terminal atau buka terminal baru.

#### Metode 3: Manual di Terminal
Untuk session terminal saat ini saja:
```powershell
$env:Path += ";C:\xampp\php"
```

### 3. Verifikasi PHP Terdeteksi
Setelah menambahkan ke PATH, verifikasi dengan:
```powershell
php -v
```

Harus menampilkan versi PHP, contoh:
```
PHP 8.2.0 (cli) (built: ...)
```

## Alternatif: Gunakan XAMPP Control Panel

Jika tidak ingin menambahkan ke PATH, Anda bisa:
1. Buka XAMPP Control Panel
2. Start Apache dan MySQL
3. Akses website melalui browser: `http://localhost/fitness-sentot/`

## Troubleshooting

### Jika masih error:
1. Pastikan XAMPP terinstall di `C:\xampp\`
2. Jika XAMPP di lokasi lain, update path di `.vscode/settings.json`
3. Restart Cursor/VS Code setelah perubahan
4. Pastikan file `php.exe` ada di folder yang ditentukan

### Cek Lokasi PHP:
```powershell
dir C:\xampp\php\php.exe
```

### Test PHP langsung:
```powershell
C:\xampp\php\php.exe -v
```

## Catatan
- File konfigurasi workspace sudah dibuat dan akan digunakan oleh Cursor/VS Code
- Menambahkan ke PATH system adalah solusi terbaik untuk penggunaan jangka panjang
- Setelah menambahkan ke PATH, restart semua aplikasi yang menggunakan terminal



