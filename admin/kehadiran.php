<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = '';

// Handle pencatatan kehadiran - HARUS SEBELUM header.php
if ($_POST && isset($_POST['catat_kehadiran'])) {
    $member_id = $_POST['member_id'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    
    // Cek apakah sudah ada kehadiran di tanggal yang sama
    $query_cek = "SELECT id FROM kehadiran WHERE member_id = ? AND tanggal = ?";
    $stmt_cek = $conn->prepare($query_cek);
    $stmt_cek->execute([$member_id, $tanggal]);
    
    if ($stmt_cek->rowCount() == 0) {
        $query = "INSERT INTO kehadiran (member_id, tanggal, waktu) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$member_id, $tanggal, $waktu]);
        
        redirect("kehadiran.php?success=" . urlencode("Kehadiran berhasil dicatat"));
    } else {
        $error = "Member sudah hadir pada tanggal tersebut";
    }
}

// Handle success message dari GET
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

$page_title = "Manajemen Kehadiran";
require_once '../includes/header.php';

// Get all active members for dropdown
$query_members = "SELECT id, kode_member, nama FROM member WHERE status_aktif = 'Aktif' ORDER BY nama";
$stmt_members = $conn->prepare($query_members);
$stmt_members->execute();
$members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);

// Get kehadiran data with filters
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_member = isset($_GET['member_id']) ? $_GET['member_id'] : '';

$query = "SELECT k.*, m.kode_member, m.nama as nama_member 
          FROM kehadiran k 
          JOIN member m ON k.member_id = m.id 
          WHERE 1=1";
$params = [];

if ($filter_tanggal) {
    $query .= " AND k.tanggal = ?";
    $params[] = $filter_tanggal;
}

if ($filter_member) {
    $query .= " AND k.member_id = ?";
    $params[] = $filter_member;
}

$query .= " ORDER BY k.tanggal DESC, k.waktu DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$kehadiran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistik kehadiran hari ini
$query_hari_ini = "SELECT COUNT(*) as total FROM kehadiran WHERE tanggal = ?";
$stmt_hari_ini = $conn->prepare($query_hari_ini);
$stmt_hari_ini->execute([date('Y-m-d')]);
$total_hari_ini = $stmt_hari_ini->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<div class="container mx-auto">
    <!-- Header dengan statistik -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Kehadiran</h1>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-2 rounded">
            <strong>Kehadiran Hari Ini:</strong> <?php echo $total_hari_ini; ?> member
        </div>
    </div>

    <!-- Form Pencatatan Kehadiran -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catat Kehadiran</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu</label>
                <input type="time" name="waktu" value="<?php echo date('H:i'); ?>" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-3 flex items-end">
                <button type="submit" name="catat_kehadiran" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center justify-center">
                    <i class="fas fa-calendar-check mr-2"></i>Catat Kehadiran
                </button>
            </div>
        </form>
        
        <?php if ($error): ?>
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="<?php echo $filter_tanggal; ?>"
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

    <!-- Tabel Kehadiran -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Member</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($kehadiran as $hadir): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo formatDate($hadir['tanggal']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('H:i', strtotime($hadir['waktu'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?php echo $hadir['nama_member']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $hadir['kode_member']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>