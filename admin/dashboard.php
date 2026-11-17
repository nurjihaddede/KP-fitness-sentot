<?php
$page_title = "Dashboard Admin";
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Statistik
$total_member = $conn->query("SELECT COUNT(*) FROM member")->fetchColumn();
$member_aktif = $conn->query("SELECT COUNT(*) FROM member WHERE status_aktif = 'Aktif'")->fetchColumn();
$member_akan_habis = $conn->query("SELECT COUNT(*) FROM member WHERE tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status_aktif = 'Aktif'")->fetchColumn();

// Data untuk grafik pembayaran bulanan
$query = "SELECT MONTH(tanggal_bayar) as bulan, YEAR(tanggal_bayar) as tahun, SUM(nominal) as total 
          FROM pembayaran 
          WHERE YEAR(tanggal_bayar) = YEAR(CURDATE()) 
          GROUP BY YEAR(tanggal_bayar), MONTH(tanggal_bayar) 
          ORDER BY tahun, bulan";
$stmt = $conn->prepare($query);
$stmt->execute();
$pembayaran_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Member yang masa aktifnya akan habis
$query = "SELECT * FROM member WHERE tanggal_berakhir BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status_aktif = 'Aktif' LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$member_akan_habis_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto">
    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-600">Total Member</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $total_member; ?></p>
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
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-600">Akan Habis</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo $member_akan_habis; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Grafik Pembayaran -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Grafik Pembayaran Bulanan</h3>
            <canvas id="pembayaranChart" height="250"></canvas>
        </div>

        <!-- Notifikasi Masa Aktif -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Member Masa Aktif Akan Habis</h3>
            <?php if (count($member_akan_habis_list) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($member_akan_habis_list as $member): ?>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <div>
                            <h4 class="font-semibold text-gray-800"><?php echo $member['nama']; ?></h4>
                            <p class="text-sm text-gray-600">Berakhir: <?php echo formatDate($member['tanggal_berakhir']); ?></p>
                        </div>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded"><?php echo $member['kode_member']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Tidak ada member dengan masa aktif akan habis.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Grafik Pembayaran
const ctx = document.getElementById('pembayaranChart').getContext('2d');
const pembayaranChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Total Pembayaran (Rp)',
            data: [<?php
                $monthly_data = array_fill(0, 12, 0);
                foreach ($pembayaran_data as $data) {
                    $monthly_data[$data['bulan'] - 1] = $data['total'];
                }
                echo implode(', ', $monthly_data);
            ?>],
            backgroundColor: '#3B82F6',
            borderColor: '#1D4ED8',
            borderWidth: 1
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
</script>

<?php require_once '../includes/footer.php'; ?>