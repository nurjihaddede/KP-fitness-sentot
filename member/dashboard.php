<?php
$page_title = "Dashboard Member";
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

// Get riwayat pembayaran
$query_pembayaran = "SELECT * FROM pembayaran WHERE member_id = ? ORDER BY tanggal_bayar DESC LIMIT 5";
$stmt_pembayaran = $conn->prepare($query_pembayaran);
$stmt_pembayaran->execute([$member_id]);
$riwayat_pembayaran = $stmt_pembayaran->fetchAll(PDO::FETCH_ASSOC);

// Get riwayat kehadiran
$query_kehadiran = "SELECT * FROM kehadiran WHERE member_id = ? ORDER BY tanggal DESC, waktu DESC LIMIT 10";
$stmt_kehadiran = $conn->prepare($query_kehadiran);
$stmt_kehadiran->execute([$member_id]);
$riwayat_kehadiran = $stmt_kehadiran->fetchAll(PDO::FETCH_ASSOC);

// Hitung total kehadiran bulan ini
$query_total_kehadiran = "SELECT COUNT(*) as total FROM kehadiran WHERE member_id = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
$stmt_total_kehadiran = $conn->prepare($query_total_kehadiran);
$stmt_total_kehadiran->execute([$member_id, date('Y-m')]);
$total_kehadiran_bulan_ini = $stmt_total_kehadiran->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<div class="container mx-auto">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">Halo, <?php echo $member['nama']; ?>! 👋</h1>
                <p class="text-blue-100">Selamat datang di dashboard member Fitness Sentot</p>
                <p class="text-blue-100 mt-1">Kode Member: <strong><?php echo $member['kode_member']; ?></strong></p>
            </div>
            <div class="text-right">
                <div class="text-4xl font-bold"><?php echo $total_kehadiran_bulan_ini; ?></div>
                <p class="text-blue-100">Kehadiran Bulan Ini</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Status Keanggotaan -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Status Keanggotaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Status</label>
                            <div class="mt-1">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    <?php echo $member['status_aktif'] == 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $member['status_aktif']; ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tanggal Bergabung</label>
                            <p class="mt-1 text-lg font-semibold text-gray-800"><?php echo formatDate($member['tanggal_bergabung']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Sisa Masa Aktif</label>
                            <p class="mt-1 text-lg font-semibold text-gray-800">
                                <?php
                                $today = new DateTime();
                                $berakhir = new DateTime($member['tanggal_berakhir']);
                                $sisa = $today->diff($berakhir);
                                echo $sisa->days . ' hari';
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tanggal Mulai</label>
                            <p class="mt-1 text-lg font-semibold text-gray-800"><?php echo formatDate($member['tanggal_mulai']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tanggal Berakhir</label>
                            <p class="mt-1 text-lg font-semibold text-gray-800"><?php echo formatDate($member['tanggal_berakhir']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jenis Kelamin</label>
                            <p class="mt-1 text-lg font-semibold text-gray-800"><?php echo $member['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Kehadiran -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Kehadiran Terbaru</h3>
                <?php if (count($riwayat_kehadiran) > 0): ?>
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
                            <?php foreach ($riwayat_kehadiran as $kehadiran): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo formatDate($kehadiran['tanggal']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('H:i', strtotime($kehadiran['waktu'])); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('l', strtotime($kehadiran['tanggal'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada riwayat kehadiran.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Penting -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Info Penting</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <i class="fas fa-clock text-yellow-500 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Jam Operasional</p>
                            <p class="text-sm text-gray-600">06