<?php
// download_rekap.php - Export Rekap ke Excel (.xls) dengan Styling Rapi
require_once 'functions.php';
requireAdmin();

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

if (!preg_match('/^\d{2}$/', $bulan) || !preg_match('/^\d{4}$/', $tahun)) {
    die('Parameter periode tidak valid.');
}

global $pdo;
$stmt = $pdo->prepare("
    SELECT a.*, u.nama, u.kelas, f.nama_fasilitas, s.status
    FROM aduan a
    JOIN users u ON a.id_user = u.id_user
    JOIN fasilitas f ON a.id_fasilitas = f.id_fasilitas
    LEFT JOIN status s ON s.id_status = CONCAT('ST', SUBSTRING(a.id_status, 2))
    WHERE YEAR(a.tgl_aduan) = ? AND MONTH(a.tgl_aduan) = ?
    ORDER BY a.tgl_aduan DESC
");
$stmt->execute([$tahun, $bulan]);
$data = $stmt->fetchAll();

$namaBulan = date('F', mktime(0,0,0,$bulan,1));
$filename = "Rekap_Aduan_{$bulan}_{$tahun}.xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 1cm; }
    body { font-family: "Calibri", "Segoe UI", Arial, sans-serif; font-size: 11pt; color: #333; }

    /* ── Judul Laporan ── */
    .title {
        font-size: 14pt; font-weight: bold; color: #1a3a8f;
        text-align: center; margin: 14px 0 4px; text-transform: uppercase;
    }
    .subtitle {
        font-size: 10pt; color: #64748b; text-align: center;
        margin-bottom: 14px; border-bottom: 2px solid #1a3a8f; padding-bottom: 5px;
    }

    /* ── Tabel ── */
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th {
        background-color: #1a3a8f;
        color: #fff;
        padding: 8px 6px;
        text-align: center;
        border: 1px solid #b0b8d1;
        font-weight: 700;
        font-size: 10pt;
        text-transform: uppercase;
    }
    td {
        padding: 6px 8px;
        border: 1px solid #dde3ef;
        vertical-align: middle;
        font-size: 10pt;
    }
    tr:nth-child(even) td { background: #f8fafc; }
    tr:hover td { background: #eef2ff; }

    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .fw-bold     { font-weight: 700; }

    .st-selesai { color: #166534; font-weight: 700; background: #dcfce7; padding: 2px 6px; border-radius: 4px; }
    .st-proses  { color: #1e40af; font-weight: 700; background: #dbeafe; padding: 2px 6px; border-radius: 4px; }
    .st-lapor   { color: #92400e; font-weight: 700; background: #fef3c7; padding: 2px 6px; border-radius: 4px; }

    .ruangan {
        background: #e7f1ff; color: #1a3a8f;
        padding: 2px 8px; border-radius: 4px;
        font-weight: 600; font-size: 9.5pt; border: 1px solid #bfdbfe;
    }
</style>
</head>
<body>

<!-- JUDUL LAPORAN -->
<div class="title">REKAP ADUAN FASILITAS RUANGAN ASM</div>
<div class="subtitle">
    Periode: <?= $namaBulan ?> <?= $tahun ?> &nbsp;|&nbsp;
    Dicetak pada: <?= date('d F Y') ?>
</div>

<!-- TABEL DATA -->
<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="10%">ID Aduan</th>
            <th width="11%">Tanggal Lapor</th>
            <th width="14%">Nama Pelapor</th>
            <th width="10%">Kelas</th>
            <th width="9%">Ruangan</th>
            <th width="12%">Fasilitas</th>
            <th width="7%">Jml</th>
            <th width="10%">Status</th>
            <th width="22%">Keterangan Kerusakan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($data as $row):
            preg_match('/\[Ruangan:\s*(ASM\s?\d+)\]/i', $row['keterangan_aduan'] ?? '', $match);
            $ruangan   = $match[1] ?? '-';
            $ket_bersih = trim(preg_replace('/\[Ruangan:\s*ASM\s?\d+\]\s*/i', '', $row['keterangan_aduan'] ?? ''));
            $status    = $row['status'] ?? 'Dilaporkan';
            $stClass   = match($status) {
                'Selesai' => 'st-selesai',
                'Proses'  => 'st-proses',
                default   => 'st-lapor'
            };
        ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td class="text-center fw-bold" style="color:#1a3a8f"><?= htmlspecialchars($row['id_aduan']) ?></td>
            <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_aduan'])) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td class="text-center"><?= htmlspecialchars($row['kelas']) ?></td>
            <td class="text-center"><?= $ruangan !== '-' ? '<span class="ruangan">'.$ruangan.'</span>' : '-' ?></td>
            <td><?= htmlspecialchars($row['nama_fasilitas']) ?></td>
            <td class="text-center fw-bold"><?= (int)$row['jumlah_fasilitas'] ?></td>
            <td class="text-center"><span class="<?= $stClass ?>"><?= htmlspecialchars($status) ?></span></td>
            <td><?= htmlspecialchars($ket_bersih) ?></td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($data)): ?>
        <tr>
            <td colspan="10" class="text-center" style="padding:20px;color:#64748b;">
                Tidak ada data aduan untuk periode ini.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
<?php exit; ?>