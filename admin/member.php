<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form actions - HARUS SEBELUM header.php
if ($_POST) {
    if (isset($_POST['tambah_member'])) {
        $kode_member = $_POST['kode_member'];
        $nama = $_POST['nama'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $alamat = $_POST['alamat'];
        $no_hp = $_POST['no_hp'];
        $sekolah = $_POST['sekolah'];
        $tanggal_bergabung = $_POST['tanggal_bergabung'];
        $tanggal_mulai = $_POST['tanggal_mulai'];
        $tanggal_berakhir = $_POST['tanggal_berakhir'];
        $status_aktif = $_POST['status_aktif'];
        
        $query = "INSERT INTO member (kode_member, nama, jenis_kelamin, alamat, no_hp, sekolah, tanggal_bergabung, tanggal_mulai, tanggal_berakhir, status_aktif) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$kode_member, $nama, $jenis_kelamin, $alamat, $no_hp, $sekolah, $tanggal_bergabung, $tanggal_mulai, $tanggal_berakhir, $status_aktif]);
        
        redirect("member.php?success=" . urlencode("Member berhasil ditambahkan"));
    }
    
    if (isset($_POST['edit_member'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $alamat = $_POST['alamat'];
        $no_hp = $_POST['no_hp'];
        $sekolah = $_POST['sekolah'];
        $tanggal_bergabung = $_POST['tanggal_bergabung'];
        $tanggal_mulai = $_POST['tanggal_mulai'];
        $tanggal_berakhir = $_POST['tanggal_berakhir'];
        $status_aktif = $_POST['status_aktif'];
        
        $query = "UPDATE member SET nama=?, jenis_kelamin=?, alamat=?, no_hp=?, sekolah=?, tanggal_bergabung=?, tanggal_mulai=?, tanggal_berakhir=?, status_aktif=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$nama, $jenis_kelamin, $alamat, $no_hp, $sekolah, $tanggal_bergabung, $tanggal_mulai, $tanggal_berakhir, $status_aktif, $id]);
        
        redirect("member.php?success=" . urlencode("Member berhasil diupdate"));
    }
}

// Handle delete - HARUS SEBELUM header.php
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM member WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    
    redirect("member.php?success=" . urlencode("Member berhasil dihapus"));
}

// Handle success message dari GET
$success = isset($_GET['success']) ? $_GET['success'] : '';

$page_title = "Manajemen Member";
require_once '../includes/header.php';

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with filters
$query = "SELECT * FROM member WHERE 1=1";
$params = [];

if ($filter_status) {
    $query .= " AND status_aktif = ?";
    $params[] = $filter_status;
}

if ($search) {
    $query .= " AND (nama LIKE ? OR kode_member LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto">
    <!-- Success Message -->
    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>
    
    <!-- Header dengan tombol tambah -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Data Member</h1>
        <button onclick="openModal('tambahModal')" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>Tambah Member
        </button>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif" <?php echo $filter_status == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?php echo $filter_status == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Cari nama atau kode member..."
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md w-full">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Member -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. HP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bergabung</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Masa Aktif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($members as $member): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900"><?php echo $member['kode_member']; ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $member['nama']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $member['sekolah']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $member['no_hp']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo formatDate($member['tanggal_bergabung']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo formatDate($member['tanggal_berakhir']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $member['status_aktif'] == 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $member['status_aktif']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete(<?php echo $member['id']; ?>)" 
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Member -->
<div id="tambahModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Member Baru</h3>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Member</label>
                        <input type="text" name="kode_member" value="<?php echo generateKodeMember(); ?>" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="nama" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No. HP</label>
                        <input type="tel" name="no_hp" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="alamat" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sekolah</label>
                        <input type="text" name="sekolah" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Bergabung</label>
                        <input type="date" name="tanggal_bergabung" value="<?php echo date('Y-m-d'); ?>" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="<?php echo date('Y-m-d'); ?>" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Berakhir</label>
                        <input type="date" name="tanggal_berakhir" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status Aktif</label>
                        <select name="status_aktif" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('tambahModal')" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                        Batal
                    </button>
                    <button type="submit" name="tambah_member" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                        Simpan Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Member -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Data Member</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Member</label>
                        <input type="text" id="edit_kode_member" readonly
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="nama" id="edit_nama" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="edit_jenis_kelamin" 
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No. HP</label>
                        <input type="tel" name="no_hp" id="edit_no_hp" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="alamat" id="edit_alamat" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sekolah</label>
                        <input type="text" name="sekolah" id="edit_sekolah" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Bergabung</label>
                        <input type="date" name="tanggal_bergabung" id="edit_tanggal_bergabung" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Berakhir</label>
                        <input type="date" name="tanggal_berakhir" id="edit_tanggal_berakhir" 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status Aktif</label>
                        <select name="status_aktif" id="edit_status_aktif" 
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                        Batal
                    </button>
                    <button type="submit" name="edit_member" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                        Update Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function editMember(member) {
    document.getElementById('edit_id').value = member.id;
    document.getElementById('edit_kode_member').value = member.kode_member;
    document.getElementById('edit_nama').value = member.nama;
    document.getElementById('edit_jenis_kelamin').value = member.jenis_kelamin;
    document.getElementById('edit_alamat').value = member.alamat;
    document.getElementById('edit_no_hp').value = member.no_hp;
    document.getElementById('edit_sekolah').value = member.sekolah || '';
    document.getElementById('edit_tanggal_bergabung').value = member.tanggal_bergabung;
    document.getElementById('edit_tanggal_mulai').value = member.tanggal_mulai;
    document.getElementById('edit_tanggal_berakhir').value = member.tanggal_berakhir;
    document.getElementById('edit_status_aktif').value = member.status_aktif;
    
    openModal('editModal');
}

function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus member ini?')) {
        window.location.href = 'member.php?delete=' + id;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('fixed')) {
        event.target.classList.add('hidden');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
                        