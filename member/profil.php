<?php
$page_title = "Profil Saya";
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

if (!isMember()) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$member_id = $_SESSION['user_id'];

// Get member data
$query_member = "SELECT * FROM member WHERE id = ?";
$stmt_member = $conn->prepare($query_member);
$stmt_member->execute([$member_id]);
$member = $stmt_member->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Get statistics
$query_stats = "
    SELECT 
        (SELECT COUNT(*) FROM kehadiran WHERE member_id = ?) as total_kehadiran,
        (SELECT COUNT(*) FROM kehadiran WHERE member_id = ? AND DATE_FORMAT(tanggal, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) as kehadiran_bulan_ini,
        (SELECT SUM(nominal) FROM pembayaran WHERE member_id = ?) as total_pembayaran,
        (SELECT COUNT(*) FROM pembayaran WHERE member_id = ?) as jumlah_pembayaran
";
$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->execute([$member_id, $member_id, $member_id, $member_id]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Get recent payments
$query_payments = "SELECT * FROM pembayaran WHERE member_id = ? ORDER BY tanggal_bayar DESC LIMIT 5";
$stmt_payments = $conn->prepare($query_payments);
$stmt_payments->execute([$member_id]);
$recent_payments = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);

// Get recent attendance
$query_attendance = "SELECT * FROM kehadiran WHERE member_id = ? ORDER BY tanggal DESC, waktu DESC LIMIT 10";
$stmt_attendance = $conn->prepare($query_attendance);
$stmt_attendance->execute([$member_id]);
$recent_attendance = $stmt_attendance->fetchAll(PDO::FETCH_ASSOC);

// Calculate days remaining
$today = new DateTime();
$end_date = new DateTime($member['tanggal_berakhir']);
$days_remaining = $today->diff($end_date)->days;
$is_active = $member['status_aktif'] == 'Aktif';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Profil Member</h1>
        <p class="text-gray-600 mt-2">Kelola informasi profil dan lihat statistik keanggotaan Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Profile Card -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Information -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white">Informasi Profil</h2>
                            <p class="text-blue-100 text-sm">Data pribadi dan keanggotaan</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <i class="fas fa-user-circle text-2xl text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Kode Member</label>
                                <p class="text-lg font-semibold text-gray-800 bg-gray-50 px-3 py-2 rounded-lg">
                                    <?php echo $member['kode_member']; ?>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nama Lengkap</label>
                                <p class="text-lg font-semibold text-gray-800"><?php echo $member['nama']; ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                                <p class="text-lg font-semibold text-gray-800">
                                    <?php echo $member['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">No. Telepon</label>
                                <p class="text-lg font-semibold text-gray-800"><?php echo $member['no_hp']; ?></p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Sekolah</label>
                                <p class="text-lg font-semibold text-gray-800">
                                    <?php echo $member['sekolah'] ?: '<span class="text-gray-400">-</span>'; ?>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Alamat</label>
                                <p class="text-lg font-semibold text-gray-800"><?php echo $member['alamat']; ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Bergabung</label>
                                <p class="text-lg font-semibold text-gray-800"><?php echo formatDate($member['tanggal_bergabung']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status Keanggotaan</label>
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                                    <?php echo $is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $member['status_aktif']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Membership Status -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Status Keanggotaan</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?php echo formatDate($member['tanggal_mulai']); ?></div>
                        <p class="text-sm text-blue-800 mt-1">Tanggal Mulai</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600"><?php echo formatDate($member['tanggal_berakhir']); ?></div>
                        <p class="text-sm text-green-800 mt-1">Tanggal Berakhir</p>
                    </div>
                    <div class="text-center p-4 <?php echo $days_remaining <= 7 ? 'bg-yellow-50' : 'bg-gray-50'; ?> rounded-lg">
                        <div class="text-2xl font-bold <?php echo $days_remaining <= 7 ? 'text-yellow-600' : 'text-gray-600'; ?>">
                            <?php echo $days_remaining; ?> Hari
                        </div>
                        <p class="text-sm <?php echo $days_remaining <= 7 ? 'text-yellow-800' : 'text-gray-600'; ?> mt-1">
                            Sisa Masa Aktif
                        </p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <?php
                $start_date = new DateTime($member['tanggal_mulai']);
                $total_days = $start_date->diff($end_date)->days;
                $progress_percentage = min(100, max(0, (($total_days - $days_remaining) / $total_days) * 100));
                ?>
                <div class="mb-2">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Masa Aktif</span>
                        <span><?php echo round($progress_percentage); ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300" 
                             style="width: <?php echo $progress_percentage; ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Kehadiran Terbaru</h3>
                
                <?php if (count($recent_attendance) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_attendance as $attendance): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo formatDate($attendance['tanggal']); ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('H:i', strtotime($attendance['waktu'])); ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <?php 
                                        $day_names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        echo $day_names[date('w', strtotime($attendance['tanggal']))];
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada riwayat kehadiran</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statistics -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Statistik</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-800">Total Kehadiran</p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo $stats['total_kehadiran']; ?></p>
                        </div>
                        <i class="fas fa-calendar-check text-2xl text-blue-500"></i>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-green-800">Kehadiran Bulan Ini</p>
                            <p class="text-2xl font-bold text-green-600"><?php echo $stats['kehadiran_bulan_ini']; ?></p>
                        </div>
                        <i class="fas fa-chart-line text-2xl text-green-500"></i>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-purple-800">Total Pembayaran</p>
                            <p class="text-2xl font-bold text-purple-600"><?php echo formatRupiah($stats['total_pembayaran']); ?></p>
                        </div>
                        <i class="fas fa-money-bill-wave text-2xl text-purple-500"></i>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-orange-800">Jumlah Pembayaran</p>
                            <p class="text-2xl font-bold text-orange-600"><?php echo $stats['jumlah_pembayaran']; ?></p>
                        </div>
                        <i class="fas fa-receipt text-2xl text-orange-500"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Pembayaran Terbaru</h3>
                
                <?php if (count($recent_payments) > 0): ?>
                    <div class="space-y-3">
                        <?php foreach ($recent_payments as $payment): ?>
                        <div class="border-l-4 border-green-500 pl-4 py-2 bg-green-50 rounded-r-lg">
                            <p class="text-sm font-medium text-gray-800"><?php echo $payment['bulan_dibayar']; ?></p>
                            <p class="text-xs text-gray-600"><?php echo formatDate($payment['tanggal_bayar']); ?></p>
                            <p class="text-sm font-semibold text-green-600"><?php echo formatRupiah($payment['nominal']); ?></p>
                            <?php if ($payment['keterangan']): ?>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $payment['keterangan']; ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 text-sm">Belum ada riwayat pembayaran</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Account Info -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Informasi Login</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                        <div class="flex items-center">
                            <input type="text" value="<?php echo $member['kode_member']; ?>" readonly 
                                   class="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 bg-gray-50">
                            <button onclick="copyToClipboard('<?php echo $member['kode_member']; ?>')" 
                                    class="bg-gray-200 hover:bg-gray-300 px-3 py-2 rounded-r-lg border border-l-0 border-gray-300">
                                <i class="fas fa-copy text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                        <div class="flex items-center">
                            <input type="password" value="<?php echo $member['kode_member']; ?>" readonly 
                                   class="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 bg-gray-50" id="passwordField">
                            <button onclick="togglePassword()" 
                                    class="bg-gray-200 hover:bg-gray-300 px-3 py-2 rounded-r-lg border border-l-0 border-gray-300">
                                <i class="fas fa-eye text-gray-600" id="passwordIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-3">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-yellow-500 mt-0.5 mr-2"></i>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Informasi Keamanan</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Password default adalah kode member Anda. Disarankan untuk mengingat dan tidak membagikan kredensial login.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
                
                <div class="space-y-3">
                    <a href="dashboard.php" 
                       class="w-full flex items-center justify-between p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-tachometer-alt text-blue-500 mr-3"></i>
                            <span class="text-blue-800 font-medium">Dashboard</span>
                        </div>
                        <i class="fas fa-chevron-right text-blue-500"></i>
                    </a>
                    
                    <button onclick="printProfile()" 
                            class="w-full flex items-center justify-between p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-print text-green-500 mr-3"></i>
                            <span class="text-green-800 font-medium">Cetak Profil</span>
                        </div>
                        <i class="fas fa-chevron-right text-green-500"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification('Username berhasil disalin!', 'success');
    }, function(err) {
        console.error('Could not copy text: ', err);
        showNotification('Gagal menyalin username', 'error');
    });
}

function togglePassword() {
    const passwordField = document.getElementById('passwordField');
    const passwordIcon = document.getElementById('passwordIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash text-gray-600';
    } else {
        passwordField.type = 'password';
        passwordIcon.className = 'fas fa-eye text-gray-600';
    }
}

function printProfile() {
    window.print();
}

function showNotification(message, type = 'info') {
    // Simple notification implementation
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add print styles
const style = document.createElement('style');
style.textContent = `
    @media print {
        .no-print {
            display: none !important;
        }
        .bg-gradient-to-r {
            background: #2563eb !important;
        }
        .text-white {
            color: #000 !important;
        }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once '../includes/footer.php'; ?>