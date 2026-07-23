<?php
require_once 'functions.php';
requireAdmin();

$flash = getFlash();
global $pdo;

$sql = "
    SELECT a.*, u.nama, u.kelas, f.nama_fasilitas, s.status
    FROM aduan a
    JOIN users u ON a.id_user = u.id_user
    JOIN fasilitas f ON a.id_fasilitas = f.id_fasilitas
    LEFT JOIN status s ON s.id_status = CONCAT('ST', SUBSTRING(a.id_status, 2))
    ORDER BY CAST(SUBSTRING(a.id_aduan, 2) AS UNSIGNED) DESC
";
$data = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - Sistem Pengaduan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
:root {
    --primary: #c0001a; --accent: #1a3a8f;
    --bg: #f5f6fa; --border: #dde3ef;
    --shadow: 0 2px 8px rgba(26,58,143,0.08);
}
body { background: var(--bg); font-family: system-ui, sans-serif; }

/* ── Header ── */
.header-bar {
    background: linear-gradient(135deg,var(--primary) 0%,#8b0010 40%,var(--accent) 100%);
    border-bottom: 3px solid #fff; padding: 1.1rem 0; margin-bottom: 2rem;
    box-shadow: 0 2px 12px rgba(26,58,143,0.18);
}
.header-title { font-size:1.25rem; font-weight:700; color:#fff; margin:0; }
.header-sub   { font-size:0.82rem; color:rgba(255,255,255,0.78); margin:0; }
.btn-panel {
    background: rgba(255,255,255,0.18); color:#fff;
    border: 1px solid rgba(255,255,255,0.45); border-radius:7px;
    padding: 0.38rem 0.9rem; font-size:0.87rem; font-weight:500;
    text-decoration:none; display:inline-block;
}
.btn-panel:hover { background:rgba(255,255,255,0.28); color:#fff; }

/* ── Card & Table ── */
.card { border:1px solid var(--border); border-radius:10px; box-shadow:var(--shadow); }
.table th { background:#f0f3fa; color:var(--accent); font-weight:600; font-size:0.82rem; }
.badge-ruangan { background:#e7f1ff; color:var(--accent); font-size:0.74rem; padding:0.28rem 0.55rem; border-radius:6px; font-weight:500; }

/* ── Badge Status ── */
.badge-status-dilaporkan { background:#fef3c7; color:#92400e; }
.badge-status-proses     { background:#dbeafe; color:#1e40af; }
.badge-status-selesai    { background:#dcfce7; color:#166534; }

/* ── Modal ─ */
.modal-content { border-radius:10px; border:none; }
.modal-header {
    background: linear-gradient(135deg,#c0001a 0%,#8b0010 40%,#1a3a8f 100%);
    border-bottom:3px solid #fff; color:#fff; border-radius:10px 10px 0 0;
}
.modal-header .btn-close { filter:invert(1); }
.detail-label { font-size:0.78rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.4px; }
.detail-value { font-weight:600; font-size:0.95rem; }
.keterangan-box { background:#f1f3f5; border-radius:8px; padding:6px 12px 8px; font-size:0.92rem; line-height:1.6; white-space:pre-line; }
</style>
</head>
<body>

<!-- ── HEADER ─ -->
<header class="header-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1 class="header-title"><i class="bi bi-speedometer2 me-2"></i>DASHBOARD ADMIN</h1>
            <p class="header-sub">Manajemen &amp; Kelola Laporan Pengaduan Fasilitas</p>
        </div>
        <div class="d-flex gap-2">
            <a href="rekap.php" class="btn-panel"><i class="bi bi-bar-chart me-1"></i>Rekap</a>
            <a href="proses.php?act=logout" class="btn-panel"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </div>
    </div>
</header>

<!-- ── FLASH NOTIFIKASI ── -->
<div class="container">
<?php if($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const flash = <?= json_encode($flash, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
    if (!flash?.msg) return;
    let icon='info', title='Info', color='#1a3a8f';
    const msg = (flash.msg||'').toLowerCase();
    if (msg.includes('status berhasil'))        { icon='success'; title='Status diperbarui';   color='#16a34a'; }
    else if (msg.includes('berhasil dihapus'))  { icon='success'; title='Data dihapus';        color='#dc2626'; }
    else if (msg.includes('rekap'))             { icon=flash.type==='success'?'success':'warning'; title='Rekap'; color='#2563eb'; }
    else if (flash.type==='error')              { icon='error';   title='Kesalahan';            color='#dc2626'; }
    else if (flash.type==='warning')            { icon='warning'; title='Perhatian';            color='#ca8a04'; }
    Swal.fire({ icon,title,text:flash.msg,confirmButtonColor:color,timer:3000,timerProgressBar:true,showConfirmButton:true,confirmButtonText:'OK' });
});
</script>
<?php endif; ?>

<!-- ── PAGE TITLE ── -->
<div class="mb-4">
    <h4 class="fw-bold mb-1" style="color:var(--accent)">
        <i class="bi bi-clipboard-data me-2"></i>Data Aduan Masuk
    </h4>
    <p class="text-muted small mb-0">Ubah status langsung dari dropdown untuk update laporan.</p>
</div>

<!-- ── TABLE CARD ── -->
<div class="card shadow-sm mb-5">
<div class="card-body table-responsive p-0">
<table class="table table-hover align-middle mb-0">
<thead>
<tr>
    <th class="ps-3">ID</th>
    <th>Tanggal</th>
    <th>Nama</th>
    <th>Kelas</th>
    <th>Ruangan</th>
    <th>Fasilitas</th>
    <th>Keterangan</th>
    <th>Foto Bukti</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php if(empty($data)): ?>
<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data aduan.</td></tr>
<?php else: foreach($data as $r):
    preg_match('/\[Ruangan:\s*(ASM\s?\d+)\]/i', $r['keterangan_aduan']??'', $match);
    $ruangan    = $match[1] ?? '-';
    $ket_bersih = trim(preg_replace('/\[Ruangan:\s*ASM\s?\d+\]\s*/i','', $r['keterangan_aduan']??''));
    $ket_short  = mb_strlen($ket_bersih) > 40 ? mb_substr($ket_bersih,0,40).'…' : $ket_bersih;
    $status_class = match($r['status']??'') {
        'Selesai' => 'badge-status-selesai',
        'Proses'  => 'badge-status-proses',
        default   => 'badge-status-dilaporkan'
    };
?>
<tr>
    <td class="ps-3"><code class="text-primary"><?= $r['id_aduan'] ?></code></td>
    <td class="small"><?= date('d/m/Y', strtotime($r['tgl_aduan'])) ?></td>
    <td><?= htmlspecialchars($r['nama']) ?></td>
    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['kelas']) ?></span></td>
    <td>
        <?= $ruangan!=='-'
            ? '<span class="badge-ruangan">'.htmlspecialchars($ruangan).'</span>'
            : '<span class="text-muted small">-</span>' ?>
    </td>
    <td class="small"><?= htmlspecialchars($r['nama_fasilitas']) ?></td>
    <td>
        <span class="small text-muted" style="max-width:120px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle" title="<?= htmlspecialchars($ket_bersih) ?>">
            <?= htmlspecialchars($ket_short) ?>
        </span>
        <button class="btn btn-sm btn-outline-primary border-0 p-0 px-1 ms-1 align-middle"
                data-bs-toggle="modal" data-bs-target="#modalDetail<?= $r['id_aduan'] ?>" title="Lihat detail">
            <i class="bi bi-eye"></i>
        </button>
    </td>
    <td>
        <?php if(!empty($r['bukti'])): ?>
        <button class="btn btn-sm btn-outline-info" title="Lihat Foto Bukti"
            data-bs-toggle="modal" data-bs-target="#modalFoto<?= $r['id_aduan'] ?>">
            <i class="bi bi-image"></i>
        </button>
        <?php else: ?>
        <span class="text-muted small">-</span>
        <?php endif; ?>
    </td>

    <!-- ── Form Update Status (Auto-submit) ── -->
    <td>
        <form method="POST" action="proses.php" class="d-inline">
            <input type="hidden" name="act" value="update_status">
            <input type="hidden" name="id_aduan" value="<?= $r['id_aduan'] ?>">
            <select name="id_status" class="form-select form-select-sm" 
                    onchange="this.form.submit()" style="min-width:130px">
                <?php foreach(getAllStatus() as $s):
                    $mapped   = 'S'.substr($s['id_status'],2);
                    $selected = ($r['id_status']==$mapped) ? 'selected' : '';
                ?>
                <option value="<?= $mapped ?>" <?= $selected ?>><?= htmlspecialchars($s['status']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </td>

    <!-- ── Aksi ── -->
    <td>
        <button onclick="hapus('<?= $r['id_aduan'] ?>')" class="btn btn-sm btn-danger" title="Hapus">
            <i class="bi bi-trash3"></i>
        </button>
    </td>
</tr>

<!-- ═══════════════════════════════════════════════════════════
     MODAL DETAIL ADUAN
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetail<?= $r['id_aduan'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
    <div class="modal-header py-3">
        <h6 class="modal-title"><i class="bi bi-card-text me-2"></i>Detail Aduan #<?= $r['id_aduan'] ?></h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body p-4">
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="detail-label">Fasilitas</div>
                <div class="detail-value"><?= htmlspecialchars($r['nama_fasilitas']) ?></div>
            </div>
            <div class="col-6">
                <div class="detail-label">Ruangan</div>
                <div class="detail-value">
                    <?= $ruangan!=='-'
                        ? '<span class="badge-ruangan">'.htmlspecialchars($ruangan).'</span>'
                        : '<span class="text-muted">-</span>' ?>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-label">Jumlah</div>
                <div class="detail-value"><?= (int)$r['jumlah_fasilitas'] ?> unit</div>
            </div>
            <div class="col-6">
                <div class="detail-label">Pelapor</div>
                <div class="detail-value"><?= htmlspecialchars($r['nama']) ?></div>
            </div>
            <div class="col-6">
                <div class="detail-label">Kelas</div>
                <div class="detail-value"><?= htmlspecialchars($r['kelas']) ?></div>
            </div>
            <div class="col-6">
                <div class="detail-label">Tanggal</div>
                <div class="detail-value"><?= date('d F Y', strtotime($r['tgl_aduan'])) ?></div>
            </div>
            <div class="col-12">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="badge <?= $status_class ?>"><?= $r['status']??'-' ?></span>
                </div>
            </div>
        </div>
        <hr class="my-2">
        <div class="detail-label mb-1">Keterangan</div>
        <div class="keterangan-box"><?= nl2br(htmlspecialchars($ket_bersih)) ?: '<span class="text-muted fst-italic">Tidak ada keterangan.</span>' ?></div>
    </div>
</div>
</div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL FOTO BUKTI
════════════════════════════════════════════════════════════ -->
<?php if(!empty($r['bukti'])): ?>
<div class="modal fade" id="modalFoto<?= $r['id_aduan'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">
    <div class="modal-header py-3">
        <h6 class="modal-title"><i class="bi bi-image me-2"></i>Foto Bukti — #<?= $r['id_aduan'] ?></h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body p-3 text-center">
        <?php
            $ext = strtolower(pathinfo($r['bukti'], PATHINFO_EXTENSION));
            $path = 'uploads/bukti/' . htmlspecialchars($r['bukti']);
        ?>
        <?php if($ext === 'pdf'): ?>
            <div class="alert alert-info"><i class="bi bi-file-pdf me-2"></i>File PDF — <a href="<?= $path ?>" target="_blank">Klik untuk membuka</a></div>
        <?php else: ?>
            <img src="<?= $path ?>" alt="Foto Bukti" class="img-fluid rounded" style="max-height:70vh; object-fit:contain;">
        <?php endif; ?>
    </div>
</div>
</div>
</div>
<?php endif; ?>

<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</div><!-- /card -->

</div><!-- /container -->

<!-- ── SCRIPTS ── -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── Hapus aduan ───────────────────────────────────────────
function hapus(id) {
    Swal.fire({
        title:'Hapus Aduan?',
        text:'Data tidak bisa dikembalikan.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#c0001a',
        cancelButtonColor:'#6b7280',
        confirmButtonText:'Ya, Hapus',
        cancelButtonText:'Batal',
        reverseButtons:true
    }).then(res => {
        if(res.isConfirmed) window.location='proses.php?act=delete&id='+id;
    });
}
</script>
</body>
</html>