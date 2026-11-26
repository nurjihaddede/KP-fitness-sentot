# Script untuk menambahkan PHP XAMPP ke PATH User (tidak perlu Admin)
# Jalankan di PowerShell biasa

Write-Host "Menambahkan PHP XAMPP ke User PATH..." -ForegroundColor Yellow

$phpPath = "C:\xampp\php"
$currentPath = [Environment]::GetEnvironmentVariable("Path", "User")

if ($currentPath -notlike "*$phpPath*") {
    [Environment]::SetEnvironmentVariable("Path", "$currentPath;$phpPath", "User")
    Write-Host "✓ PHP berhasil ditambahkan ke User PATH!" -ForegroundColor Green
    Write-Host "⚠️ RESTART Cursor/VS Code dan Terminal untuk menerapkan perubahan" -ForegroundColor Yellow
} else {
    Write-Host "✓ PHP sudah ada di PATH" -ForegroundColor Green
}

# Refresh PATH untuk session ini
$env:Path += ";$phpPath"

Write-Host "`nVerifikasi:" -ForegroundColor Cyan
php -v



