<?php
use Dompdf\Dompdf;
use Dompdf\Options;

require_once '../includes/auth.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$unduh_pdf = ($_GET['format'] ?? '') === 'pdf';
if ($unduh_pdf) {
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoload)) {
        http_response_code(503);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('Unduhan PDF belum tersedia. Jalankan composer install di folder aplikasi.');
    }
    require_once $autoload;
}

$database = new Database();
$conn = $database->getConnection();

$bulan = $_GET['bulan'] ?? '';
$filter_bulan = is_string($bulan) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $bulan) ? $bulan : date('Y-m');
$filter_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : (int) date('Y');
$filter_laporan = isset($_GET['jenis_laporan']) ? $_GET['jenis_laporan'] : 'member';
if (!in_array($filter_laporan, ['member', 'pembayaran', 'kehadiran'], true)) {
    $filter_laporan = 'member';
}

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$bulan_angka = (int) substr($filter_bulan, 5, 2);
$tahun_bulan = substr($filter_bulan, 0, 4);
$label_periode = ($nama_bulan[$bulan_angka] ?? $filter_bulan) . ' ' . $tahun_bulan;

$judul = 'Laporan Data Member';
if ($filter_laporan === 'pembayaran') {
    $judul = 'Laporan Pembayaran ' . $label_periode;
} elseif ($filter_laporan === 'kehadiran') {
    $judul = 'Laporan Kehadiran ' . $label_periode;
}

$rows = [];
if ($filter_laporan === 'member') {
    $stmt = $conn->prepare("SELECT * FROM member ORDER BY nama");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($filter_laporan === 'pembayaran') {
    $stmt = $conn->prepare("SELECT p.*, m.kode_member, m.nama as nama_member
        FROM pembayaran p
        JOIN member m ON p.member_id = m.id
        WHERE DATE_FORMAT(p.tanggal_bayar, '%Y-%m') = ?
        ORDER BY p.tanggal_bayar DESC");
    $stmt->execute([$filter_bulan]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT k.*, m.kode_member, m.nama as nama_member
        FROM kehadiran k
        JOIN member m ON k.member_id = m.id
        WHERE DATE_FORMAT(k.tanggal, '%Y-%m') = ?
        ORDER BY k.tanggal DESC, k.waktu DESC");
    $stmt->execute([$filter_bulan]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$total_nominal = 0;
if ($filter_laporan === 'pembayaran') {
    foreach ($rows as $row) {
        $total_nominal += (float) $row['nominal'];
    }
}

// Gunakan tabel yang sama untuk pratinjau cetak dan berkas PDF.
if ($unduh_pdf) {
    ob_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($judul); ?> - Fitness Sentot</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f3f4f6;
        }
        .toolbar {
            position: sticky;
            top: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #1e3a8a;
            color: #fff;
        }
        .toolbar p { margin: 0; font-size: 13px; line-height: 1.4; }
        .toolbar button, .toolbar a {
            min-height: 44px;
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-print { background: #dc2626; color: #fff; }
        .btn-back { background: #fff; color: #1e3a8a; }
        .sheet {
            max-width: 900px;
            margin: 16px auto;
            background: #fff;
            padding: 24px;
        }
        h1 { margin: 0; font-size: 22px; }
        h2 { margin: 4px 0 0; font-size: 16px; font-weight: 600; }
        .meta { margin: 12px 0 16px; font-size: 13px; color: #374151; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #eff6ff; }
        .total { margin-top: 12px; font-weight: 700; }
        .empty { padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { margin: 0; max-width: none; padding: 0; }
            a { color: inherit; text-decoration: none; }
        }
        @page { margin: 14mm; }
        <?php if ($unduh_pdf): ?>
        body { background: #fff; font-family: 'DejaVu Sans', sans-serif; }
        .sheet { display: block; max-width: none; margin: 0; padding: 0; }
        table { table-layout: fixed; font-size: 10pt; }
        thead { display: table-header-group; }
        th, td { padding: 5pt; overflow-wrap: break-word; word-wrap: break-word; }
        <?php endif; ?>
    </style>
</head>
<body>
    <?php if (!$unduh_pdf): ?>
    <div class="toolbar">
        <p>Di komputer atau Android, pilih <strong>Simpan sebagai PDF</strong> pada dialog cetak.</p>
        <div style="display:flex; gap:8px;">
            <a class="btn-back" href="laporan.php?jenis_laporan=<?php echo urlencode($filter_laporan); ?>&bulan=<?php echo urlencode($filter_bulan); ?>&tahun=<?php echo urlencode((string) $filter_tahun); ?>">Kembali</a>
            <button type="button" class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        </div>
    </div>
    <?php endif; ?>
    <main class="sheet">
        <h1>Fitness Sentot</h1>
        <h2><?php echo htmlspecialchars($judul); ?></h2>
        <p class="meta">
            Dicetak: <?php echo date('d/m/Y H:i'); ?>
            <?php if ($filter_laporan === 'member'): ?>
                · Seluruh data member
            <?php else: ?>
                · Periode: <?php echo htmlspecialchars($label_periode); ?>
            <?php endif; ?>
            · Jumlah data: <?php echo count($rows); ?>
        </p>

        <?php if (count($rows) === 0): ?>
            <p class="empty">Tidak ada data untuk filter ini.</p>
        <?php elseif ($filter_laporan === 'member'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>No. HP</th>
                        <th>Sekolah</th>
                        <th>Bergabung</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['kode_member']); ?></td>
                        <td><?php echo htmlspecialchars($item['nama']); ?></td>
                        <td><?php echo htmlspecialchars($item['no_hp'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['sekolah'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars(formatDate($item['tanggal_bergabung'])); ?></td>
                        <td><?php echo htmlspecialchars($item['status_aktif']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($filter_laporan === 'pembayaran'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Member</th>
                        <th>Kode</th>
                        <th>Bulan Dibayar</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(formatDate($item['tanggal_bayar'])); ?></td>
                        <td><?php echo htmlspecialchars($item['nama_member']); ?></td>
                        <td><?php echo htmlspecialchars($item['kode_member']); ?></td>
                        <td><?php echo htmlspecialchars($item['bulan_dibayar']); ?></td>
                        <td><?php echo htmlspecialchars(formatRupiah($item['nominal'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="total">Total: <?php echo htmlspecialchars(formatRupiah($total_nominal)); ?></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Member</th>
                        <th>Kode Member</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(formatDate($item['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars(date('H:i', strtotime($item['waktu']))); ?></td>
                        <td><?php echo htmlspecialchars($item['nama_member']); ?></td>
                        <td><?php echo htmlspecialchars($item['kode_member']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
<?php
if ($unduh_pdf) {
    $html = ob_get_clean();
    $opsi = new Options();
    $opsi->setDefaultFont('DejaVu Sans');
    $opsi->setDefaultMediaType('print');
    $opsi->setIsRemoteEnabled(false);
    $opsi->setIsJavascriptEnabled(false);
    $pdf = new Dompdf($opsi);
    $pdf->setPaper('A4', $filter_laporan === 'member' ? 'landscape' : 'portrait');
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->render();
    $isi_pdf = $pdf->output();

    $periode_file = $filter_laporan === 'member' ? '' : '-' . $filter_bulan;
    $nama_file = 'fitness-sentot-' . $filter_laporan . $periode_file . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nama_file . '"');
    header('Cache-Control: private, no-store');
    echo $isi_pdf;
}
