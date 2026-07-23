<?php require_once 'functions.php'; $flash = getFlash(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Fasilitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #c0001a; --primary-hover: #a0001a; --accent: #1a3a8f;
            --accent-light: #2563eb; --bg-page: #f5f6fa; --bg-card: #ffffff;
            --text-main: #1a1a2e; --text-muted: #64748b; --border: #dde3ef;
            --shadow: 0 2px 8px rgba(26,58,143,0.08);
        }
        body {
            background-color: var(--bg-page); color: var(--text-main);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
        }
        /* ── Header ── */
        .header-bar {
            background: linear-gradient(135deg, var(--primary) 0%, #8b0010 40%, var(--accent) 100%);
            border-bottom: 3px solid #fff; padding: 1.1rem 0; margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(26,58,143,0.18);
        }
        .header-title { font-size:1.25rem; font-weight:700; color:#fff; margin:0; letter-spacing:0.5px; }
        .header-sub   { font-size:0.82rem; color:rgba(255,255,255,0.78); margin:0; }
        .btn-panel {
            background: rgba(255,255,255,0.18); color:#fff;
            border: 1px solid rgba(255,255,255,0.45); border-radius:7px;
            padding: 0.42rem 0.95rem; font-size:0.87rem; font-weight:500;
            transition: background 0.2s;
        }
        .btn-panel:hover { background:rgba(255,255,255,0.28); color:#fff; }

        /* ── Form Card ── */
        .form-card {
            background: var(--bg-card); border:1px solid var(--border);
            border-radius:10px; box-shadow:var(--shadow); padding:2rem;
        }
        .form-card-title {
            font-size:1.05rem; font-weight:700; color:var(--accent);
            border-bottom:2px solid var(--primary); padding-bottom:0.65rem; margin-bottom:1.5rem;
        }
        .form-label { font-weight:500; color:var(--text-main); margin-bottom:0.4rem; }
        .form-control, .form-select {
            border-radius:7px; border:1px solid var(--border);
            padding:0.62rem 0.85rem; font-size:0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-light);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .btn-submit {
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            color:#fff; border:none; padding:0.72rem 2rem; border-radius:7px;
            font-weight:600; font-size:0.97rem; width:100%;
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-submit:hover { opacity:0.91; transform:translateY(-1px); color:#fff; }
        .field-divider { border-top:1px dashed var(--border); margin:0.5rem 0 1rem; }

        /* ── Side Drawer ── */
        .side-drawer {
            position:fixed; top:0; right:0; width:340px; height:100vh;
            background:var(--bg-card); border-left:2px solid var(--border);
            box-shadow:-4px 0 18px rgba(26,58,143,0.10); z-index:1040;
            transform:translateX(100%); transition:transform 0.35s cubic-bezier(0.4,0,0.2,1);
            display:flex; flex-direction:column;
        }
        .side-drawer.open { transform:translateX(0); }
        .drawer-header {
            padding:1.1rem 1.25rem; border-bottom:2px solid var(--primary);
            background:linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            flex-shrink:0;
        }
        .drawer-header h6, .drawer-header small { color:#fff !important; }
        .drawer-body { padding:1.25rem; flex:1; overflow-y:auto; }
        .drawer-footer { padding:1rem 1.25rem; border-top:1px solid var(--border); background:#f0f3fa; flex-shrink:0; }

        /* ── Info Box & Steps ── */
        .info-box {
            background:#f0f3fa; border:1px solid var(--border);
            border-radius:8px; padding:1rem; margin-bottom:1.25rem;
        }
        .info-box .info-title { font-weight:600; font-size:0.85rem; color:var(--accent); margin-bottom:0.5rem; }
        .step-item { display:flex; gap:0.6rem; align-items:flex-start; margin-bottom:0.5rem; }
        .step-num {
            background:var(--primary); color:#fff; border-radius:50%;
            width:20px; height:20px; font-size:0.7rem; font-weight:700;
            display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;
        }
        .step-text { font-size:0.82rem; color:var(--text-muted); }
        .empty-state { text-align:center; padding:2rem 1rem; color:var(--text-muted); }

        /* ── Status Result Card ── */
        .status-result {
            background:#f8fafc; border:1px solid var(--border);
            border-left:4px solid var(--accent); border-radius:7px;
            padding:1rem; margin-top:0.75rem; animation:fadeIn 0.3s ease;
        }
        .status-badge {
            display:inline-block; padding:0.22rem 0.7rem; border-radius:999px;
            font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;
        }
        .status-dilaporkan { background:#fef3c7; color:#92400e; }
        .status-proses     { background:#dbeafe; color:#1e40af; }
        .status-selesai    { background:#dcfce7; color:#166534; }

        /* ── Progress Steps ── */
        .progress-steps {
            display:flex; align-items:center; gap:0; margin:10px 0 6px;
        }
        .ps-step {
            display:flex; flex-direction:column; align-items:center; flex:1; position:relative;
        }
        .ps-dot {
            width:28px; height:28px; border-radius:50%; border:2.5px solid #d1d5db;
            background:#fff; display:flex; align-items:center; justify-content:center;
            font-size:12px; z-index:1; position:relative; transition:all 0.3s;
        }
        .ps-dot.done  { border:2.5px solid transparent; background: linear-gradient(#fff,#fff) padding-box, linear-gradient(135deg,#c0001a,#1a3a8f) border-box; color:#c0001a; }
        .ps-dot.aktif { border:2.5px solid transparent; background: linear-gradient(#fff,#fff) padding-box, linear-gradient(135deg,#c0001a,#1a3a8f) border-box; color:#c0001a; }
        .ps-dot.wait  { background:#fff; border-color:#d1d5db; color:#9ca3af; }
        .ps-label { font-size:10px; color:#6b7280; margin-top:4px; text-align:center; white-space:nowrap; }
        .ps-label.done  { color:#059669; font-weight:600; }
        .ps-label.aktif { color:#d97706; font-weight:600; }
        .ps-line {
            flex:1; height:2px; background:#d1d5db; margin-top:-18px; z-index:0;
        }
        .ps-line.done { background:#10b981; }

        /* ── Riwayat Timeline (di dalam card hasil) ── */
        .riwayat-toggle {
            font-size:12px; color:var(--accent); cursor:pointer; background:none;
            border:none; padding:0; text-decoration:underline; margin-top:6px;
        }
        .riwayat-list {
            margin-top:8px; padding-left:0; list-style:none; display:none;
        }
        .riwayat-list.show { display:block; }
        .rw-item {
            display:flex; gap:10px; align-items:flex-start;
            padding:7px 0; border-bottom:1px dashed #e5e7eb;
        }
        .rw-item:last-child { border-bottom:none; }
        .rw-dot {
            width:10px; height:10px; border-radius:50%;
            flex-shrink:0; margin-top:4px;
        }
        .rw-body { flex:1; }
        .rw-status  { font-size:11px; font-weight:700; }
        .rw-pic     { font-size:11.5px; color:#374151; margin:1px 0; }
        .rw-pic::before { content:'👤 '; }
        .rw-catatan { font-size:11px; color:#6b7280; font-style:italic; }
        .rw-date    { font-size:10.5px; color:#9ca3af; }

        /* ── Misc ── */
        .tracking-code {
            font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
            background:#f1f5f9; padding:0.2rem 0.5rem; border-radius:4px; font-weight:600;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
        input[type="number"] { -moz-appearance:textfield; appearance:textfield; }

        /* ── Upload Area ── */
        .upload-area {
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            border:2px dashed var(--border); border-radius:10px;
            padding:1.75rem 1rem; background:#fafbff; cursor:pointer;
            transition:border-color 0.2s, background 0.2s; text-align:center;
        }
        .upload-area:hover { border-color:var(--accent-light); background:#f0f5ff; }
        .upload-area.dragover { border-color:var(--primary); background:#fff0f3; }
        .upload-area.has-file { border-color:#16a34a; background:#f0fdf4; }
        .upload-icon { font-size:2rem; color:var(--accent-light); margin-bottom:0.4rem; line-height:1; }
        .upload-text { font-weight:600; font-size:0.92rem; color:var(--text-main); }
        .upload-hint { font-size:0.78rem; color:var(--text-muted); margin-top:0.2rem; }
        .upload-filename { font-size:0.83rem; color:#16a34a; font-weight:600; margin-top:0.5rem; }
        .upload-filename i { margin-right:0.3rem; }

        @keyframes fadeIn { from{opacity:0;transform:translateY(5px)} to{opacity:1;transform:translateY(0)} }
    </style>
</head>
<body>

<header class="header-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1 class="header-title"><i class="bi bi-megaphone-fill me-2"></i>SISTEM PENGADUAN FASILITAS LAB ASM</h1>
            <p class="header-sub">Laporan Kerusakan &amp; Permintaan Perbaikan</p>
        </div>
        <button class="btn btn-panel" id="openDrawer">
            <i class="bi bi-search me-1"></i> Lacak &amp; Panduan
        </button>
    </div>
</header>

<main class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="form-card">
                <div class="form-card-title">
                    <i class="bi bi-file-earmark-text me-2"></i>Formulir Pengaduan
                </div>

                <form action="proses.php" method="POST" id="formAduan" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="act" value="add">
                    <input type="hidden" name="tgl_aduan" value="<?= date('Y-m-d') ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap sesuai data sekolah">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas <span class="text-danger">*</span></label>
                        <select name="kelas" class="form-select">
                            <option value="" disabled selected>-- Pilih Kelas --</option>
                            <option>XI RPL A</option><option>XI RPL B</option><option>XI RPL C</option>
                            <option>XII RPL A</option><option>XII RPL B</option><option>XII RPL C</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                        <select name="ruangan" class="form-select">
                            <option value="" disabled selected>-- Pilih Ruangan --</option>
                            <option>Lab ASM 1</option><option>Lab ASM 2</option><option>Lab ASM 3</option>
                        </select>
                    </div>

                    <div class="field-divider"></div>

                    <div class="mb-3">
                        <label class="form-label">Fasilitas <span class="text-danger">*</span></label>
                        <select name="id_fasilitas" class="form-select">
                            <option value="" disabled selected>-- Pilih Kategori Fasilitas --</option>
                            <?php foreach(getAllFasilitas() as $f): ?>
                                <option value="<?= htmlspecialchars($f['id_fasilitas']) ?>"><?= htmlspecialchars($f['nama_fasilitas']) ?></option>
                            <?php endforeach; ?>
                            <option value="F008">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Unit Rusak <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" min="1" value="1">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Keterangan Kerusakan <span class="text-danger">*</span></label>
                        <textarea name="keterangan" class="form-control" rows="4"
                            placeholder="Jelaskan secara rinci jenis permasalahan, lokasi penempatan, dan kronologis singkat..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Sertakan Bukti <span class="text-danger">*</span></label>
                        <label for="buktiInput" class="upload-area" id="uploadLabel">
                            <div class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                            <div class="upload-text">Klik atau seret file ke sini</div>
                            <div class="upload-hint">JPG, PNG, WEBP, atau PDF — Maks. 5 MB</div>
                            <div class="upload-filename" id="uploadFilename" style="display:none"></div>
                        </label>
                        <input type="file" name="bukti" id="buktiInput" class="d-none"
                            accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf">
                    </div>

                    <div class="mb-3">
                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>Data dikirim pada: <?= date('d F Y') ?></small>
                    </div>

                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-check2-circle me-2"></i> Kirim Laporan
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<!-- ══════════════════════════════════════════════════════════
     SIDE DRAWER — Panduan & Lacak Aduan
══════════════════════════════════════════════════════════ -->
<aside class="side-drawer" id="sideDrawer">
    <div class="drawer-header">
        <h6 class="fw-bold mb-1"><i class="bi bi-search me-2"></i>Lacak Status Aduan</h6>
        <small>Cek progres &amp; penanggung jawab laporan kamu</small>
    </div>

    <div class="drawer-body">

        <!-- Info cara pakai -->
        <div class="info-box mb-3">
            <div class="info-title"><i class="bi bi-journal-text me-1"></i>Cara Mengisi Formulir</div>
            <div class="step-item"><span class="step-num">1</span><span class="step-text">Isi <strong>Nama Lengkap</strong> sesuai data sekolah.</span></div>
            <div class="step-item"><span class="step-num">2</span><span class="step-text">Pilih <strong>Kelas</strong> dari daftar.</span></div>
            <div class="step-item"><span class="step-num">3</span><span class="step-text">Pilih <strong>Ruangan</strong> (Lab ASM 1/2/3).</span></div>
            <div class="step-item"><span class="step-num">4</span><span class="step-text">Pilih <strong>Fasilitas</strong> yang rusak.</span></div>
            <div class="step-item"><span class="step-num">5</span><span class="step-text">Isi <strong>jumlah unit</strong> dan <strong>keterangan</strong>.</span></div>
            <div class="step-item"><span class="step-num">6</span><span class="step-text">Klik <strong>Kirim Laporan</strong> dan simpan kode aduan kamu.</span></div>
        </div>

        <!-- Form cari -->
        <div class="info-box">
            <div class="info-title"><i class="bi bi-search me-1"></i>Lacak Status Aduan</div>
            <p class="mb-0" style="font-size:0.82rem;color:var(--text-muted)">Masukkan <strong>kode aduan</strong> (A001) atau <strong>nama pelapor</strong>.</p>
        </div>

        <form id="formCek">
            <div class="input-group mb-3">
                <input type="text" id="cariInput" class="form-control" placeholder="Kode (A001) / Nama pelapor" required>
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <!-- Hasil pencarian -->
        <div id="hasilCek" style="display:none"></div>
        <div id="emptyState" class="empty-state">
            <i class="bi bi-clipboard-check fs-4 d-block mb-2"></i>
            <small>Masukkan kode atau nama untuk melihat status laporan.</small>
        </div>

    </div>

    <div class="drawer-footer">
        <a href="login.php" class="btn btn-outline-dark w-100 py-2 fw-medium">
            <i class="bi bi-shield-lock me-2"></i> Masuk Admin
        </a>
    </div>
</aside>

<!-- ── SCRIPTS ── -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── Upload area interaction ────────────────────────────────
const buktiInput  = document.getElementById('buktiInput');
const uploadLabel = document.getElementById('uploadLabel');
const uploadFilename = document.getElementById('uploadFilename');

buktiInput.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const name = this.files[0].name;
        uploadLabel.classList.add('has-file');
        uploadFilename.innerHTML = `<i class="bi bi-check-circle-fill"></i>${name}`;
        uploadFilename.style.display = 'block';
        uploadLabel.querySelector('.upload-icon').innerHTML = '<i class="bi bi-file-earmark-check" style="color:#16a34a"></i>';
    }
});

uploadLabel.addEventListener('dragover', (e) => { e.preventDefault(); uploadLabel.classList.add('dragover'); });
uploadLabel.addEventListener('dragleave', () => uploadLabel.classList.remove('dragover'));
uploadLabel.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadLabel.classList.remove('dragover');
    if (e.dataTransfer.files[0]) {
        buktiInput.files = e.dataTransfer.files;
        buktiInput.dispatchEvent(new Event('change'));
    }
});

// ── Drawer open/close ──────────────────────────────────────
const drawer = document.getElementById('sideDrawer');
document.getElementById('openDrawer').onclick = () => drawer.classList.add('open');
document.addEventListener('click', (e) => {
    if (drawer.classList.contains('open') && !drawer.contains(e.target) && e.target.id !== 'openDrawer') {
        drawer.classList.remove('open');
    }
});

// ── Validasi form aduan ────────────────────────────────────
document.getElementById('formAduan').addEventListener('submit', function(e) {
    e.preventDefault();
    const fields = ['nama','kelas','ruangan','id_fasilitas','jumlah','keterangan'];
    const labels = { nama:'Nama Lengkap',kelas:'Kelas',ruangan:'Ruangan',id_fasilitas:'Fasilitas',jumlah:'Jumlah Unit',keterangan:'Keterangan' };
    let empty = [];
    fields.forEach(f => { const el = document.querySelector(`[name="${f}"]`); if(!el||!el.value.trim()) empty.push(labels[f]); });
    const buktiEl = document.getElementById('buktiInput');
    if (!buktiEl || !buktiEl.files || buktiEl.files.length === 0) empty.push('Sertakan Bukti');
    if (empty.length > 0) {
        Swal.fire({ icon:'warning', title:'Field Belum Terisi', html:`Mohon lengkapi:<br><strong>${empty.join(', ')}</strong>`, confirmButtonText:'OK', confirmButtonColor:'#c0001a' });
        return;
    }
    if (buktiEl.files[0].size > 5 * 1024 * 1024) {
        Swal.fire({ icon:'warning', title:'File Terlalu Besar', text:'Ukuran file maksimal 5 MB.', confirmButtonText:'OK', confirmButtonColor:'#c0001a' });
        return;
    }
    this.submit();
});

// ── Success popup ──────────────────────────────────────────
<?php if(isset($_GET['success']) && isset($_GET['id'])): ?>
Swal.fire({
    icon:'success', title:'Laporan Berhasil Terkirim',
    html:`<div class="text-start"><p class="mb-2 text-muted">Terima kasih atas partisipasi Anda.</p>
    <div class="bg-light p-3 rounded border mb-3">
        <span class="d-block text-muted small mb-1">Nomor Pelaporan:</span>
        <span class="tracking-code fs-5 text-dark"><?= htmlspecialchars($_GET['id']) ?></span>
    </div>
    <p class="mb-0 small text-muted">Simpan nomor ini untuk lacak status via panel samping.</p></div>`,
    confirmButtonText:'Selesai', confirmButtonColor:'#c0001a'
});
<?php endif; ?>

// ── Helper: escape HTML ────────────────────────────────────
function esc(str) {
    return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Helper: format tanggal ─────────────────────────────────
function fmtTgl(str) {
    if (!str) return '-';
    const d = new Date(str);
    return d.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
}
function fmtTglWaktu(str) {
    if (!str) return '-';
    const d = new Date(str);
    return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) +
           ' ' + d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
}

// ── Helper: meta status ────────────────────────────────────
function statusMeta(id_status) {
    const map = {
        'S001': { dot:'#6366f1', cls:'status-dilaporkan', label:'Dilaporkan' },
        'S002': { dot:'#f59e0b', cls:'status-proses',     label:'Diproses'   },
        'S003': { dot:'#10b981', cls:'status-selesai',    label:'Selesai'    },
    };
    return map[id_status] || { dot:'#9ca3af', cls:'status-dilaporkan', label: id_status };
}

// ── Render progress bar 3 langkah ─────────────────────────
function renderProgress(id_status, riwayat) {
    const steps = [
        { id:'S001', label:'Dilaporkan', ico:'📋' },
        { id:'S002', label:'Diproses',   ico:'🔧' },
        { id:'S003', label:'Selesai',    ico:'✅' },
    ];
    const idx = steps.findIndex(s => s.id === id_status);

    let html = '<div class="progress-steps">';
    steps.forEach((step, i) => {
        let dotClass = 'wait', labelClass = '';
        if (i < idx)        { dotClass = 'done';  labelClass = 'done';  }
        else if (i === idx) { dotClass = 'aktif'; labelClass = 'aktif'; }

        html += `<div class="ps-step">
            <div class="ps-dot ${dotClass}" style="">${step.ico}</div>
            <div class="ps-label ${labelClass}">${step.label}</div>
        </div>`;

        if (i < steps.length - 1) {
            html += `<div class="ps-line ${i < idx ? 'done' : ''}"></div>`;
        }
    });
    html += '</div>';
    return html;
}

// ── Render riwayat timeline ────────────────────────────────
function renderRiwayat(riwayat, cardId) {
    if (!riwayat || riwayat.length === 0) {
        return `<p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Belum ada riwayat tercatat.</p>`;
    }

    let items = riwayat.map(log => {
        const m = statusMeta(log.id_status);
        const label = log.label_status || m.label;
        return `<li class="rw-item">
            <span class="rw-dot" style="background: linear-gradient(135deg, #c0001a 0%, #1a3a8f 100%)"></span>
            <div class="rw-body">
                <div class="rw-status" style="color:${m.dot}">${label}</div>
                <div class="rw-pic">${esc(log.nama_pic)}</div>
                ${log.catatan ? `<div class="rw-catatan">"${esc(log.catatan)}"</div>` : ''}
                <div class="rw-date"><i class="bi bi-calendar3 me-1"></i>${fmtTgl(log.created_at)}</div>
            </div>
        </li>`;
    }).join('');

    return `
        <button class="riwayat-toggle" onclick="toggleRiwayat('${cardId}')">
            <i class="bi bi-clock-history me-1"></i>Lihat riwayat penanganan (${riwayat.length} langkah)
        </button>
        <ul class="riwayat-list" id="rw-${cardId}">${items}</ul>`;
}

function toggleRiwayat(id) {
    const el = document.getElementById('rw-' + id);
    if (el) el.classList.toggle('show');
}

// ── AJAX: Lacak aduan ──────────────────────────────────────
document.getElementById('formCek').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn     = e.target.querySelector('button');
    const keyword = document.getElementById('cariInput').value.trim();
    if (!keyword) return Swal.fire('Perhatian','Masukkan kode atau nama pelapor.','warning');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const res      = await fetch(`api.php?act=cek_status&q=${encodeURIComponent(keyword)}`);
        const response = await res.json();
        const hasil    = document.getElementById('hasilCek');
        const empty    = document.getElementById('emptyState');

        empty.style.display = 'none';

        if (response.found && response.data && response.data.length > 0) {

            let html = '';

            if (response.count > 1) {
                html += `<div class="alert alert-info py-2 mb-3 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Ditemukan <strong>${response.count} laporan</strong> untuk "<em>${esc(keyword)}</em>"
                </div>`;
            }

            response.data.forEach((item, idx) => {
                const cardId = `card-${idx}`;
                const s = item.status || 'Dilaporkan';
                // Map status text langsung ke kode, tidak bergantung pada riwayat
                const statusMap = { 'Selesai':'S003', 'Proses':'S002', 'Dilaporkan':'S001' };
                const sId = statusMap[s] || 'S001';
                const meta = statusMeta(sId);

                // Bersihkan keterangan (hapus tag [Ruangan:...])
                const ket = (item.keterangan||'').replace(/\[Ruangan:\s*ASM\s?\d+\]\s*/gi,'').trim();

                html += `<div class="status-result">
                    <!-- Header: status badge + ID -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="status-badge ${meta.cls}">${s}</span>
                        <small class="text-muted fw-medium">ID: <span class="tracking-code">${esc(item.id_aduan)}</span></small>
                    </div>

                    <!-- Progress Steps -->
                    ${renderProgress(sId, item.riwayat)}

                    <!-- Info aduan -->
                    <table class="table table-sm table-borderless mb-0 small mt-2">
                        <tr><td class="text-muted" style="width:45%">Fasilitas:</td>
                            <td class="fw-medium">${esc(item.fasilitas)}</td></tr>
                        <tr><td class="text-muted">Jumlah Unit:</td>
                            <td class="fw-medium text-primary">${item.jumlah} unit</td></tr>
                        <tr><td class="text-muted">Tanggal Lapor:</td>
                            <td class="fw-medium">${fmtTgl(item.tgl_aduan)}</td></tr>
                        ${item.nama ? `<tr><td class="text-muted">Pelapor:</td>
                            <td class="fw-medium">${esc(item.nama)}${item.kelas ? ` <span class="badge bg-info text-dark" style="font-size:10px">${esc(item.kelas)}</span>` : ''}</td></tr>` : ''}
                        ${ket ? `<tr><td class="text-muted">Keterangan:</td>
                            <td class="fst-italic text-muted">${esc(ket.substring(0,120))}${ket.length>120?'…':''}</td></tr>` : ''}
                    </table>

                    <!-- Riwayat penanganan -->
                    <div class="mt-1 pt-1 border-top">
                        ${renderRiwayat(item.riwayat, cardId)}
                    </div>
                </div>`;
            });

            hasil.innerHTML = html;
            hasil.style.display = 'block';

        } else {
            hasil.style.display = 'none';
            empty.style.display = 'block';
            Swal.fire('Data Tidak Ditemukan','Pastikan kode atau nama yang dimasukkan benar.','error');
        }
    } catch(err) {
        Swal.fire('Kesalahan Koneksi','Gagal menghubungi server.','error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-search"></i>';
    }
});
</script>
</body>
</html>