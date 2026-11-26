<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Handle pembayaran baru - HARUS SEBELUM header.php
if ($_POST && isset($_POST['tambah_pembayaran'])) {
    $member_id = $_POST['member_id'];
    $tanggal_bayar = $_POST['tanggal_bayar'];
    $nominal = $_POST['nominal'];
    $bulan_dibayar = $_POST['bulan_dibayar'];
    $keterangan = $_POST['keterangan'];
    
    // Insert pembayaran
    $query = "INSERT INTO pembayaran (member_id, tanggal_bayar, nominal, bulan_dibayar, keterangan) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$member_id, $tanggal_bayar, $nominal, $bulan_dibayar, $keterangan]);
    
    // Update masa aktif member
    $query_update = "UPDATE member SET 
                    tanggal_berakhir = DATE_ADD(tanggal_berakhir, INTERVAL 1 MONTH),
                    status_aktif = 'Aktif'
                    WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->execute([$member_id]);
    
    redirect("pembayaran.php?success=" . urlencode("Pembayaran berhasil dicatat"));
}

// Handle success message dari GET
$success = isset($_GET['success']) ? $_GET['success'] : '';

$page_title = "Manajemen Pembayaran";
require_once '../includes/header.php';

// Get all members for dropdown
$query_members = "SELECT id, kode_member, nama FROM member ORDER BY nama";
$stmt_members = $conn->prepare($query_members);
$stmt_members->execute();
$members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);

// Get pembayaran data with filters
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$filter_member = isset($_GET['member_id']) ? $_GET['member_id'] : '';

$query = "SELECT p.*, m.kode_member, m.nama as nama_member 
          FROM pembayaran p 
          JOIN member m ON p.member_id = m.id 
          WHERE 1=1";
$params = [];

if ($filter_bulan) {
    $query .= " AND DATE_FORMAT(p.tanggal_bayar, '%Y-%m') = ?";
    $params[] = $filter_bulan;
}

if ($filter_member) {
    $query .= " AND p.member_id = ?";
    $params[] = $filter_member;
}

$query .= " ORDER BY p.tanggal_bayar DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$pembayaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total pembayaran bulan ini
$query_total = "SELECT SUM(nominal) as total FROM pembayaran WHERE DATE_FORMAT(tanggal_bayar, '%Y-%m') = ?";
$stmt_total = $conn->prepare($query_total);
$stmt_total->execute([date('Y-m')]);
$total_bulan_ini = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<div class="container mx-auto">
    <!-- Success Message -->
    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>
    
    <!-- Header dengan statistik -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Pembayaran</h1>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
            <strong>Total Bulan Ini:</strong> <?php echo formatRupiah($total_bulan_ini); ?>
        </div>
    </div>

    <!-- Form Tambah Pembayaran -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Pembayaran Baru</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                <select name="member_id" required 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Member</option>
                    <?php foreach ($members as $member): ?>
                    <option value="<?php echo $member['id']; ?>">
                        <?php echo $member['kode_member'] . ' - ' . $member['nama']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bayar</label>
                <input type="date" name="tanggal_bayar" value="<?php echo date('Y-m-d'); ?>" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                <input type="number" name="nominal" placeholder="150000" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan Dibayar</label>
                <input type="month" name="bulan_dibayar" value="<?php echo date('Y-m'); ?>" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <input type="text" name="keterangan" placeholder="Pembayaran iuran bulanan..."
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" name="tambah_pembayaran" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center justify-center">
                    <i class="fas fa-money-bill-wave mr-2"></i>Catat Pembayaran
                </button>
            </div>
        </form>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <input type="month" name="bulan" value="<?php echo $filter_bulan; ?>"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                <select name="member_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Member</option>
                    <?php foreach ($members as $member): ?>
                    <option value="<?php echo $member['id']; ?>" <?php echo $filter_member == $member['id'] ? 'selected' : ''; ?>>
                        <?php echo $member['nama']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Pembayaran -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan Dibayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pembayaran as $bayar): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo formatDate($bayar['tanggal_bayar']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $bayar['nama_member']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $bayar['kode_member']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $bayar['bulan_dibayar']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                            <?php echo formatRupiah($bayar['nominal']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo $bayar['keterangan'] ?: '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>