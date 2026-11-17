<?php
$page_title = "Laporan";
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get filter parameters
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$filter_laporan = isset($_GET['jenis_laporan']) ? $_GET['jenis_laporan'] : 'member';

// Data untuk grafik
$query_chart = "SELECT MONTH(tanggal_bayar) as bulan, SUM(nominal) as total 
                FROM pembayaran 
                WHERE YEAR(tanggal_bayar) = ? 
                GROUP BY MONTH(tanggal_bayar) 
                ORDER BY bulan";
$stmt_chart = $conn->prepare($query_chart);
$stmt_chart->execute([$filter_tahun]);
$chart_data = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

// Data member aktif/tidak aktif
$member_aktif = $conn->query("SELECT COUNT(*) FROM member WHERE status_aktif = 'Aktif'")->fetchColumn();
$member_tidak_aktif = $conn->query("SELECT COUNT(*) FROM member WHERE status_aktif = 'Tidak Aktif'")->fetchColumn();

// Data pembayaran bulan ini
$query_pembayaran_bulan = "SELECT SUM(nominal) as total FROM pembayaran WHERE DATE_FORMAT(tanggal_bayar, '%Y-%m') = ?";
$stmt_pembayaran_bulan = $conn->prepare($query_pembayaran_bulan);
$stmt_pembayaran_bulan->execute([$filter_bulan]);
$total_pembayaran_bulan = $stmt_pembayaran_bulan->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Data kehadiran bulan ini
$query_kehadiran_bulan = "SELECT COUNT(*) as total FROM kehadiran WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?";
$stmt_kehadiran_bulan = $conn->prepare($query_kehadiran_bulan);
$stmt_kehadiran_bulan->execute([$filter_bulan]);
$total_kehadiran_bulan = $stmt_kehadiran_bulan->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Laporan & Statistik</h1>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan</label>
                <select name="jenis_laporan" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="member" <?php echo $filter_laporan == 'member' ? 'selected' : ''; ?>>Data Member</option>
                    <option value="pembayaran" <?php echo $filter_laporan == 'pembayaran' ? 'selected' : ''; ?>>Pembayaran</option>
                    <option value="kehadiran" <?php echo $filter_laporan == 'kehadiran' ? 'selected' : ''; ?>>Kehadiran</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <input type="month" name="bulan" value="<?php echo $filter_bulan; ?>"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select name="tahun" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $filter_tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-600">Total Member</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $member_aktif + $member_tidak_aktif; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-600">Member Aktif</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $member_aktif; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-600">Pembayaran Bulan Ini</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo formatRupiah($total_pembayaran_bulan); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-600">Kehadiran Bulan Ini</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $total_kehadiran_bulan; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Grafik Pembayaran Tahunan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Grafik Pembayaran Tahun <?php echo $filter_tahun; ?></h3>
            <canvas id="pembayaranTahunanChart" height="250"></canvas>
        </div>

        <!-- Pie Chart Status Member -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Status Member</h3>
            <canvas id="statusMemberChart" height="250"></canvas>
        </div>
    </div>

    <!-- Laporan Detail Berdasarkan Filter -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
                <?php 
                switch($filter_laporan) {
                    case 'member': echo 'Laporan Data Member'; break;
                    case 'pembayaran': echo 'Laporan Pembayaran Bulan ' . date('F Y', strtotime($filter_bulan . '-01')); break;
                    case 'kehadiran': echo 'Laporan Kehadiran Bulan ' . date('F Y', strtotime($filter_bulan . '-01')); break;
                }
                ?>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php if ($filter_laporan == 'member'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. HP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bergabung</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <?php elseif ($filter_laporan == 'pembayaran'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan Dibayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                        <?php else: ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Member</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    // Query berdasarkan jenis laporan
                    switch($filter_laporan) {
                        case 'member':
                            $query = "SELECT * FROM member ORDER BY nama";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($data as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo $item['kode_member']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $item['nama']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $item['sekolah']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $item['no_hp']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo formatDate($item['tanggal_bergabung']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $item['status_aktif'] == 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $item['status_aktif']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach;
                            break;
                            
                        case 'pembayaran':
                            $query = "SELECT p.*, m.kode_member, m.nama as nama_member 
                                     FROM pembayaran p 
                                     JOIN member m ON p.member_id = m.id 
                                     WHERE DATE_FORMAT(p.tanggal_bayar, '%Y-%m') = ?
                                     ORDER BY p.tanggal_bayar DESC";
                            $stmt = $conn->prepare($query);
                            $stmt->execute([$filter_bulan]);
                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($data as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo formatDate($item['tanggal_bayar']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $item['nama_member']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $item['kode_member']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $item['bulan_dibayar']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    <?php echo formatRupiah($item['nominal']); ?>
                                </td>
                            </tr>
                            <?php endforeach;
                            break;
                            
                        case 'kehadiran':
                            $query = "SELECT k.*, m.kode_member, m.nama as nama_member 
                                     FROM kehadiran k 
                                     JOIN member m ON k.member_id = m.id 
                                     WHERE DATE_FORMAT(k.tanggal, '%Y-%m') = ?
                                     ORDER BY k.tanggal DESC, k.waktu DESC";
                            $stmt = $conn->prepare($query);
                            $stmt->execute([$filter_bulan]);
                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($data as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo formatDate($item['tanggal']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('H:i', strtotime($item['waktu'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo $item['nama_member']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $item['kode_member']; ?>
                                </td>
                            </tr>
                            <?php endforeach;
                            break;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Grafik Pembayaran Tahunan
const ctxTahunan = document.getElementById('pembayaranTahunanChart').getContext('2d');
const pembayaranTahunanChart = new Chart(ctxTahunan, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Total Pembayaran (Rp)',
            data: [<?php
                $monthly_data = array_fill(0, 12, 0);
                foreach ($chart_data as $data) {
                    $monthly_data[$data['bulan'] - 1] = $data['total'];
                }
                echo implode(', ', $monthly_data);
            ?>],
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderColor: '#3B82F6',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Pie Chart Status Member
const ctxStatus = document.getElementById('statusMemberChart').getContext('2d');
const statusMemberChart = new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: ['Aktif', 'Tidak Aktif'],
        datasets: [{
            data: [<?php echo $member_aktif; ?>, <?php echo $member_tidak_aktif; ?>],
            backgroundColor: [
                '#10B981',
                '#EF4444'
            ],
            borderWidth: 2,
            borderColor: '#FFFFFF'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>