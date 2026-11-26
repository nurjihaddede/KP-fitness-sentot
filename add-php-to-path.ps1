# Script untuk menambahkan PHP XAMPP ke PATH
# Jalankan sebagai Administrator

Write-Host "Menambahkan PHP XAMPP ke PATH..." -ForegroundColor Yellow

$phpPath = "C:\xampp\php"
$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")

if ($currentPath -notlike "*$phpPath*") {
    [Environment]::SetEnvironmentVariable("Path", "$currentPath;$phpPath", "Machine")
    Write-Host "✓ PHP berhasil ditambahkan ke PATH!" -ForegroundColor Green
    Write-Host "⚠️ RESTART Cursor/VS Code dan Terminal untuk menerapkan perubahan" -ForegroundColor Yellow
} else {
    Write-Host "✓ PHP sudah ada di PATH" -ForegroundColor Green
}

Write-Host "`nVerifikasi:" -ForegroundColor Cyan
Write-Host "Setelah restart, jalankan: php -v" -ForegroundColor White



