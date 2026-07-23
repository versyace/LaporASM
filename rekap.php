<?php
require_once 'functions.php';
requireAdmin();

$bulan   = $_GET['bulan'] ?? date('m');
$tahun   = $_GET['tahun'] ?? date('Y');
$rekap   = getRekapByPeriod($bulan, $tahun);
$grouped = getAllRekapGrouped();
$detail  = getDetailAduanByMonth($bulan, $tahun);
$flash   = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Bulanan - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:      #c0001a;
            --accent:       #1a3a8f;
            --accent-light: #2563eb;
            --bg-page:      #f5f6fa;
            --bg-card:      #ffffff;
            --text-main:    #1a1a2e;
            --text-muted:   #64748b;
            --border:       #dde3ef;
            --shadow:       0 2px 8px rgba(26,58,143,0.08);
        }
        body { background: var(--bg-page); font-family: system-ui, sans-serif; color: var(--text-main); }
        .header-bar {
            background: linear-gradient(135deg, var(--primary) 0%, #8b0010 40%, var(--accent) 100%);
            border-bottom: 3px solid #fff;
            padding: 1.1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(26,58,143,0.18);
        }
        .header-title { font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: 0.5px; }
        .header-sub   { font-size: 0.82rem; color: rgba(255,255,255,0.78); margin: 0; }
        .btn-panel {
            background: rgba(255,255,255,0.18); color: #fff;
            border: 1px solid rgba(255,255,255,0.45); border-radius: 7px;
            padding: 0.38rem 0.9rem; font-size: 0.87rem; font-weight: 500;
            text-decoration: none; transition: background 0.2s; display: inline-block;
        }
        .btn-panel:hover { background: rgba(255,255,255,0.28); color: #fff; }
        .card { border: 1px solid var(--border); border-radius: 10px; box-shadow: var(--shadow); background: var(--bg-card); }
        .card-header-custom {
            background: var(--bg-card); border-bottom: 2px solid var(--border);
            padding: 0.9rem 1.25rem; border-radius: 10px 10px 0 0;
        }
        .card-header-custom h6 { font-weight: 700; color: var(--accent); margin: 0; }
        .filter-bar {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; box-shadow: var(--shadow);
        }
        .stat-box {
            background: var(--bg-card); border: 1px solid var(--border);
            border-left: 4px solid var(--accent-light); border-radius: 0 8px 8px 0;
            padding: 1rem 1.25rem; box-shadow: var(--shadow); height: 100%;
        }
        .stat-box.green { border-left-color: #16a34a; }
        .stat-box .stat-label { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.3rem; }
        .stat-box .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--text-main); line-height: 1; }
        .table thead th { background: #f0f3fa; color: var(--accent); font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid var(--border); }
        .table tbody tr:hover { background: #f8fafc; }
        .table td, .table th { vertical-align: middle; }
        .riwayat-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 8px; padding: 0.85rem 1rem;
            transition: box-shadow 0.2s, border-color 0.2s; position: relative;
        }
        .riwayat-card:hover { box-shadow: 0 4px 12px rgba(26,58,143,0.12); border-color: var(--accent-light); }
        .riwayat-card a.card-link { text-decoration: none; color: inherit; display: block; }
        .riwayat-card .period-name { font-weight: 600; color: var(--text-main); font-size: 0.95rem; }
        .riwayat-card .period-meta { font-size: 0.8rem; color: var(--text-muted); }
        .riwayat-card.active-period { border-color: var(--primary); border-left: 3px solid var(--primary); }
        .btn-more {
            background: none; border: none; color: var(--text-muted);
            padding: 0.15rem 0.4rem; border-radius: 5px; font-size: 1.1rem;
            line-height: 1; transition: background 0.15s, color 0.15s;
        }
        .btn-more:hover { background: #fee2e2; color: var(--primary); }
        .section-title { font-weight: 700; color: var(--accent); font-size: 1rem; margin-bottom: 1rem; }
        .badge-ruangan { 
            background:#e7f1ff; color:#1a3a8f; font-size:0.75rem; 
            padding:0.3rem 0.6rem; border-radius:6px; font-weight:500; 
        }
    </style>
</head>
<body>

<header class="header-bar mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1 class="header-title"><i class="bi bi-bar-chart-fill me-2"></i>REKAP BULANAN PENGADUAN</h1>
            <p class="header-sub">Ringkasan &amp; Riwayat Laporan Per Periode</p>
        </div>
        <a href="admin.php" class="btn-panel"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>
</header>

<div class="container pb-5">

    <?php if($flash): ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const flash = <?= json_encode($flash, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
        if (!flash || !flash.msg) return;
        
        let icon='info', title='Info', color='#1a3a8f';
        const msg = (flash.msg || '').toLowerCase();
        
        if (msg.includes('status berhasil diubah')) { icon='success'; title='Status berhasil diubah'; color='#16a34a'; }
        else if (msg.includes('data berhasil dihapus')) { icon='success'; title='Data berhasil dihapus'; color='#dc2626'; }
        else if (msg.includes('rekap berhasil')) { icon='success'; title='Rekap berhasil dibuat'; color='#2563eb'; }
        else if (msg.includes('rekap')) { icon=flash.type==='success'?'success':'warning'; title=flash.type==='success'?'Rekap berhasil dibuat':'Rekap'; color=flash.type==='success'?'#2563eb':'#ca8a04'; }
        else if (flash.type==='error') { icon='error'; title='Error'; color='#dc2626'; }
        else if (flash.type==='warning') { icon='warning'; title='Perhatian'; color='#ca8a04'; }
        
        Swal.fire({
            icon: icon, title: title, text: flash.msg,
            confirmButtonColor: color, timer: 3000, timerProgressBar: true,
            position: 'center', toast: false, showConfirmButton: true, confirmButtonText: 'OK', backdrop: true
        });
    });
    </script>
    <?php endif; ?>

    <!-- FILTER PERIODE -->
    <div class="filter-bar d-flex flex-wrap gap-3 align-items-end">
        <div>
            <label class="form-label small fw-semibold mb-1">Bulan</label>
            <select class="form-select form-select-sm" style="width:140px"
                onchange="location.href=`?bulan=${this.value}&tahun=<?= $tahun ?>`">
                <?php for($m=1;$m<=12;$m++):
                    $sel = ($m == $bulan) ? 'selected' : '';
                    $nm  = date('F', mktime(0,0,0,$m,1));
                ?>
                    <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>" <?= $sel ?>><?= $nm ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label class="form-label small fw-semibold mb-1">Tahun</label>
            <select class="form-select form-select-sm" style="width:100px"
                onchange="location.href=`?bulan=<?= $bulan ?>&tahun=${this.value}`">
                <?php for($y=2024;$y<=2030;$y++): $sel=($y==$tahun)?'selected':''; ?>
                    <option value="<?= $y ?>" <?= $sel ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="ms-auto">
            <a href="proses.php?act=generate_rekap&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
               class="btn btn-sm px-3 fw-600"
               style="background:linear-gradient(90deg,var(--primary) 0%,var(--accent) 100%);color:#fff;border:none;border-radius:7px;font-weight:600;">
                <i class="bi bi-arrow-clockwise me-1"></i>
                <?= empty($rekap) ? 'Generate Rekap' : 'Update Rekap' ?>
            </a>
        </div>
    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-box">
                <div class="stat-label">Total Aduan — <?= date('F Y', mktime(0,0,0,$bulan,1,$tahun)) ?></div>
                <div class="stat-value"><?= count($detail) ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-box green">
                <div class="stat-label">Status Aduan Bulan Ini</div>
                <div class="d-flex gap-2 mt-1 flex-wrap">
                    <span class="badge rounded-pill bg-warning text-dark px-3 py-2">
                        Dilaporkan: <?= count(array_filter($detail, fn($d)=>($d['status']??'')==='Dilaporkan')) ?>
                    </span>
                    <span class="badge rounded-pill bg-primary px-3 py-2">
                        Proses: <?= count(array_filter($detail, fn($d)=>($d['status']??'')==='Proses')) ?>
                    </span>
                    <span class="badge rounded-pill bg-success px-3 py-2">
                        Selesai: <?= count(array_filter($detail, fn($d)=>($d['status']??'')==='Selesai')) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL REKAP AGREGAT -->
    <div class="card mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h6><i class="bi bi-clipboard-data me-2"></i>Rekap Terperiode: <?= date('F Y', mktime(0,0,0,$bulan,1,$tahun)) ?></h6>
        </div>
        <div class="card-body p-0">
            <?php if(empty($rekap)): ?>
                <div class="alert alert-info m-3 mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    Belum ada rekap untuk periode ini. Klik <strong>Generate Rekap</strong> untuk membuat.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th class="ps-3">ID Rekap</th>
                            <th>Periode</th>
                            <th>Total Aduan</th>
                            <th>Keterangan</th>
                            <th>Diperbarui</th>
                        </tr></thead>
                        <tbody>
                           <?php foreach($rekap as $rk):
                                $tglRekap  = $rk['dibuat_pada_tgl'] ?? $rk['tgl_rekap'] ?? null;
                                $tglTampil = $tglRekap ? date('d M Y', strtotime($tglRekap)) : '-'; // ✅ tanpa jam
                            ?>
                            <tr>
                                <td class="ps-3"><code class="text-primary"><?= htmlspecialchars($rk['id_rekap'] ?? '') ?></code></td>
                                <td><?= date('F Y', strtotime($rk['tgl_rekap'])) ?></td>
                                <td><span class="badge bg-primary rounded-pill px-3"><?= (int)($rk['total_aduan'] ?? 0) ?> aduan</span></td>
                                <td class="small text-muted" style="max-width:280px"><?= htmlspecialchars($rk['keterangan_aduan'] ?? '') ?></td>
                                <td class="small text-muted"><?= $tglTampil ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- DETAIL ADUAN -->
    <div class="card mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h6><i class="bi bi-list-ul me-2"></i>Detail Aduan: <?= date('F Y', mktime(0,0,0,$bulan,1,$tahun)) ?></h6>
            <span class="badge bg-secondary rounded-pill"><?= count($detail) ?> data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr>
                        <th class="ps-3">ID</th>
                        <th>Tanggal</th>
                        <th>Pelapor</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Fasilitas</th>
                        <th>Status</th>
                    </tr></thead>
                    <tbody class="small">
                        <?php if(empty($detail)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data aduan untuk periode ini.</td></tr>
                        <?php else:
                            // Urutkan terbaru di atas (A007, A006, ... A001)
                            usort($detail, function($a, $b) {
                                $na = (int) preg_replace('/\D/', '', $a['id_aduan'] ?? '0');
                                $nb = (int) preg_replace('/\D/', '', $b['id_aduan'] ?? '0');
                                return $nb - $na;
                            });
                            $no = 1;
                            foreach($detail as $d): 
                            preg_match('/\[Ruangan:\s*(ASM\s?\d+)\]/i', $d['keterangan_aduan'] ?? '', $match);
                            $ruangan = $match[1] ?? '-';
                        ?>
                            <tr>
                                <td class="ps-3"><code style="color:#c0001a"><?= htmlspecialchars($d['id_aduan'] ?? '') ?></code></td>
                                <td><?= date('d/m/Y', strtotime($d['tgl_aduan'])) ?></td>
                                <td><?= htmlspecialchars($d['nama'] ?? '') ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($d['kelas'] ?? '-') ?></span></td>
                                <td>
                                    <?= $ruangan !== '-' 
                                        ? '<span class="badge-ruangan">'.htmlspecialchars($ruangan).'</span>' 
                                        : '<span class="text-muted small">-</span>' 
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($d['nama_fasilitas'] ?? '') ?></td>
                                <td>
                                    <?php $bg = match($d['status'] ?? '') {
                                        'Selesai' => 'bg-success',
                                        'Proses'  => 'bg-primary',
                                        default   => 'bg-warning text-dark'
                                    }; ?>
                                    <span class="badge <?= $bg ?>"><?= htmlspecialchars($d['status'] ?? '-') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- RIWAYAT REKAP TERSIMPAN -->
    <?php if(!empty($grouped)): ?>
    <div class="mt-2">
        <div class="section-title"><i class="bi bi-archive me-2"></i>Riwayat Rekap Tersimpan</div>
        <div class="row g-2">
            <?php foreach($grouped as $g):
                $isActive  = ($g['bulan'] == intval($bulan) && $g['tahun'] == intval($tahun));
                $linkBulan = str_pad($g['bulan'],2,'0',STR_PAD_LEFT);
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="riwayat-card <?= $isActive ? 'active-period' : '' ?>">
                    <a href="?bulan=<?= $linkBulan ?>&tahun=<?= $g['tahun'] ?>" class="card-link">
                        <div class="period-name"><?= date('F Y', mktime(0,0,0,$g['bulan'],1,$g['tahun'])) ?></div>
                        <div class="period-meta mt-1">
                            <span><i class="bi bi-file-text me-1"></i><?= $g['total_aduan_bulan'] ?> aduan</span>
                        </div>
                    </a>
                    <div class="position-absolute top-0 end-0 p-2">
                        <div class="dropdown">
                            <button class="btn-more" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="download_rekap.php?bulan=<?= $linkBulan ?>&tahun=<?= $g['tahun'] ?>">
                                        <i class="bi bi-download me-2"></i>Download Rekap
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger btn-hapus-rekap"
                                        data-bulan="<?= $linkBulan ?>"
                                        data-tahun="<?= $g['tahun'] ?>"
                                        data-label="<?= date('F Y', mktime(0,0,0,$g['bulan'],1,$g['tahun'])) ?>">
                                        <i class="bi bi-trash3 me-2"></i>Hapus Riwayat Rekap
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.btn-hapus-rekap').forEach(btn => {
    btn.addEventListener('click', function () {
        const bulan = this.dataset.bulan;
        const tahun = this.dataset.tahun;
        const label = this.dataset.label;
        Swal.fire({
            title: 'Hapus Riwayat Rekap?',
            html: `Rekap periode <strong>${label}</strong> akan dihapus permanen.<br><small class="text-muted">Data aduan tidak akan terhapus.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c0001a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `proses.php?act=delete_rekap&bulan=${bulan}&tahun=${tahun}`;
            }
        });
    });
});
</script>
</body>
</html>