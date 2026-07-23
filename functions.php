<?php
require_once 'config.php';

// === FUNGSI USER ===
function findOrCreateUser($nama, $kelas) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE nama = ? AND kelas = ?");
    $stmt->execute([$nama, $kelas]);
    $user = $stmt->fetch();

    if ($user) return $user['id_user'];

    $last = $pdo->query("SELECT MAX(CAST(SUBSTRING(id_user, 2) AS UNSIGNED)) FROM users WHERE id_user LIKE 'S%'")->fetchColumn();
    $num  = $last ? $last + 1 : 1;
    $id_user = 'S' . str_pad($num, 3, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("INSERT INTO users (id_user, nama, role, kelas) VALUES (?, ?, 'siswa', ?)");
    $stmt->execute([$id_user, $nama, $kelas]);
    return $id_user;
}

// === FUNGSI ADUAN ===
function generateAduanId() {
    global $pdo;
    $last = $pdo->query("SELECT MAX(CAST(SUBSTRING(id_aduan, 2) AS UNSIGNED)) FROM aduan")->fetchColumn();
    $num  = $last ? $last + 1 : 1;
    return 'A' . str_pad($num, 3, '0', STR_PAD_LEFT);
}

function addAduan($data) {
    global $pdo;
    $id_aduan = generateAduanId();
    $id_user  = findOrCreateUser($data['nama'], $data['kelas']);

    $stmt = $pdo->prepare("
        INSERT INTO aduan (id_aduan, id_fasilitas, id_status, id_user, jumlah_fasilitas, keterangan_aduan, tgl_aduan, bukti)
        VALUES (?, ?, 'S001', ?, ?, ?, ?, ?)
    ");

    $ok = $stmt->execute([
        $id_aduan,
        $data['id_fasilitas'],
        $id_user,
        (int)$data['jumlah'],
        trim($data['keterangan']),
        $data['tgl_aduan'],
        $data['bukti'] ?? null
    ]);

    if ($ok) {
        // Catat log awal: status Dilaporkan
        addLogStatus($id_aduan, 'S001', 'Sistem', 'Aduan baru masuk dari ' . $data['nama']);
    }

    return $ok ? $id_aduan : false;
}

function getAllAduan() {
    global $pdo;
    $sql = "
        SELECT a.*, u.nama, u.kelas, f.nama_fasilitas, s.status
        FROM aduan a
        JOIN users u ON a.id_user = u.id_user
        JOIN fasilitas f ON a.id_fasilitas = f.id_fasilitas
        LEFT JOIN status s ON s.id_status = CONCAT('ST', SUBSTRING(a.id_status, 2))
        ORDER BY CAST(SUBSTRING(a.id_aduan, 2) AS UNSIGNED) DESC
    ";
    return $pdo->query($sql)->fetchAll();
}

function getAduanById($id_aduan) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM aduan WHERE id_aduan = ?");
    $stmt->execute([$id_aduan]);
    return $stmt->fetch();
}

function updateStatusAduan($id_aduan, $id_status) {
    global $pdo;
    if (!preg_match('/^S\d{3}$/', $id_status)) return false;
    $stmt = $pdo->prepare("UPDATE aduan SET id_status = ? WHERE id_aduan = ?");
    return $stmt->execute([$id_status, $id_aduan]);
}

function deleteAduan($id_aduan) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM aduan WHERE id_aduan = ?");
    return $stmt->execute([$id_aduan]);
}

// === FUNGSI LOG STATUS & RIWAYAT ===

/**
 * Catat perubahan status ke tabel log_status.
 */
function addLogStatus(string $id_aduan, string $id_status, string $nama_pic, string $catatan = ''): bool {
    global $pdo;
    if (!preg_match('/^S\d{3}$/', $id_status)) return false;
    $stmt = $pdo->prepare("
        INSERT INTO log_status (id_aduan, id_status, nama_pic, catatan)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$id_aduan, $id_status, $nama_pic, $catatan]);
}

/**
 * Ambil riwayat log untuk satu aduan, urut dari terlama ke terbaru.
 */
function getLogByAduan(string $id_aduan): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT
            l.id_log,
            l.id_aduan,
            l.id_status,
            s.status AS label_status,
            l.nama_pic,
            l.catatan,
            l.created_at
        FROM log_status l
        LEFT JOIN status s ON s.id_status = CONCAT('ST', SUBSTRING(l.id_status, 2))
        WHERE l.id_aduan = ?
        ORDER BY l.created_at ASC
    ");
    $stmt->execute([$id_aduan]);
    return $stmt->fetchAll();
}

/**
 * Update status + catat log dalam 1 transaksi.
 * Gunakan ini di proses.php, bukan updateStatusAduan() langsung.
 */
function updateStatusWithLog(string $id_aduan, string $id_status, string $nama_pic, string $catatan = ''): bool {
    global $pdo;
    try {
        $pdo->beginTransaction();
        if (!updateStatusAduan($id_aduan, $id_status)) { $pdo->rollBack(); return false; }
        if (!addLogStatus($id_aduan, $id_status, $nama_pic, $catatan)) { $pdo->rollBack(); return false; }
        $pdo->commit();
        return true;
    } catch (\PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Ambil PIC terakhir yang menangani aduan.
 */
function getLastPICByAduan(string $id_aduan): string {
    global $pdo;
    $stmt = $pdo->prepare("SELECT nama_pic FROM log_status WHERE id_aduan = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$id_aduan]);
    $row = $stmt->fetch();
    return $row ? $row['nama_pic'] : '-';
}

// === FUNGSI PENCARIAN ADUAN (UNTUK SISWA) ===

function searchAduanById($id_aduan) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.id_aduan, a.tgl_aduan, a.jumlah_fasilitas, a.keterangan_aduan,
               f.nama_fasilitas AS fasilitas, s.status
        FROM aduan a
        JOIN fasilitas f ON a.id_fasilitas = f.id_fasilitas
        LEFT JOIN status s ON s.id_status = CONCAT('ST', SUBSTRING(a.id_status, 2))
        WHERE a.id_aduan = ?
    ");
    $stmt->execute([strtoupper($id_aduan)]);
    return $stmt->fetch();
}

function searchAduanByUser($nama_or_kelas) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.id_aduan, a.tgl_aduan, a.jumlah_fasilitas, a.keterangan_aduan,
               f.nama_fasilitas AS fasilitas, s.status, u.nama, u.kelas
        FROM aduan a
        JOIN users u ON a.id_user = u.id_user
        JOIN fasilitas f ON a.id_fasilitas = f.id_fasilitas
        LEFT JOIN status s ON s.id_status = CONCAT('ST', SUBSTRING(a.id_status, 2))
        WHERE LOWER(u.nama) LIKE LOWER(?) OR LOWER(u.kelas) LIKE LOWER(?)
        ORDER BY a.tgl_aduan DESC
    ");
    $like = "%$nama_or_kelas%";
    $stmt->execute([$like, $like]);
    return $stmt->fetchAll();
}

function searchAduan($keyword) {
    if (preg_match('/^A\d{3}$/i', $keyword)) {
        $result = searchAduanById($keyword);
        return $result ? [$result] : [];
    }
    return searchAduanByUser($keyword);
}

// === FUNGSI REKAP ===

function generateRekapBulanan($bulan = null, $tahun = null) {
    global $pdo;
    if (!$bulan) $bulan = date('m');
    if (!$tahun) $tahun = date('Y');

    $stmt = $pdo->prepare("
        SELECT f.nama_fasilitas, COUNT(a.id_aduan) as total,
               GROUP_CONCAT(DISTINCT s.status SEPARATOR ', ') as status_list
        FROM aduan a
        JOIN fasilitas f ON a.id_fasilitas = f.id_fasilitas
        LEFT JOIN status s ON s.id_status = CONCAT('ST', SUBSTRING(a.id_status, 2))
        WHERE YEAR(a.tgl_aduan) = ? AND MONTH(a.tgl_aduan) = ?
        GROUP BY a.id_fasilitas
    ");
    $stmt->execute([$tahun, $bulan]);
    $detail = $stmt->fetchAll();

    if (empty($detail)) return ['success' => false, 'message' => 'Belum ada data aduan untuk periode tersebut.'];

    $grandTotal = array_sum(array_column($detail, 'total'));
    $keterangan = "Rekap " . date('F Y', mktime(0,0,0,$bulan,1,$tahun)) .
                  " | Total: {$grandTotal} aduan | Fasilitas: " .
                  implode(', ', array_column($detail, 'nama_fasilitas'));

    $stmtCek = $pdo->prepare("SELECT id_rekap FROM rekap_bulanan WHERE YEAR(tgl_rekap) = ? AND MONTH(tgl_rekap) = ? LIMIT 1");
    $stmtCek->execute([$tahun, $bulan]);
    $existing = $stmtCek->fetch();
    $nowDatetime = date('Y-m-d H:i:s');

    if ($existing) {
        $stmtUp = $pdo->prepare("UPDATE rekap_bulanan SET total_aduan=?,keterangan_aduan=?,dibuat_pada_tgl=? WHERE id_rekap=?");
        return $stmtUp->execute([$grandTotal, $keterangan, $nowDatetime, $existing['id_rekap']])
            ? ['success'=>true,'message'=>'Rekap berhasil diperbarui.','id'=>$existing['id_rekap']]
            : ['success'=>false,'message'=>'Gagal memperbarui rekap.'];
    }

    $id_rekap = 'RK' . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT);
    $stmtIns = $pdo->prepare("INSERT INTO rekap_bulanan (id_rekap,tgl_rekap,total_aduan,dibuat_pada_tgl,keterangan_aduan) VALUES (?,?,?,?,?)");
    return $stmtIns->execute([$id_rekap,"$tahun-$bulan-01",$grandTotal,$nowDatetime,$keterangan])
        ? ['success'=>true,'message'=>'Rekap berhasil dibuat.','id'=>$id_rekap]
        : ['success'=>false,'message'=>'Gagal menyimpan rekap.'];
}

function deleteRekapByPeriod($bulan, $tahun) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM rekap_bulanan WHERE YEAR(tgl_rekap)=? AND MONTH(tgl_rekap)=?");
    return $stmt->execute([$tahun, $bulan]);
}

function getRekapByPeriod($bulan=null,$tahun=null) {
    global $pdo;
    if (!$bulan) $bulan=date('m');
    if (!$tahun) $tahun=date('Y');
    $stmt=$pdo->prepare("SELECT * FROM rekap_bulanan WHERE YEAR(tgl_rekap)=? AND MONTH(tgl_rekap)=? ORDER BY dibuat_pada_tgl DESC");
    $stmt->execute([$tahun,$bulan]);
    return $stmt->fetchAll();
}

function getAllRekapGrouped() {
    global $pdo;
    return $pdo->query("
        SELECT YEAR(tgl_rekap) as tahun, MONTH(tgl_rekap) as bulan,
               COUNT(*) as jumlah_rekap, SUM(total_aduan) as total_aduan_bulan,
               MAX(dibuat_pada_tgl) as terakhir_dibuat
        FROM rekap_bulanan
        GROUP BY YEAR(tgl_rekap), MONTH(tgl_rekap)
        ORDER BY tahun DESC, bulan DESC
    ")->fetchAll();
}

function getDetailAduanByMonth($bulan,$tahun) {
    global $pdo;
    $stmt=$pdo->prepare("
        SELECT a.*,u.nama,u.kelas,f.nama_fasilitas,s.status
        FROM aduan a
        JOIN users u ON a.id_user=u.id_user
        JOIN fasilitas f ON a.id_fasilitas=f.id_fasilitas
        LEFT JOIN status s ON s.id_status=CONCAT('ST',SUBSTRING(a.id_status,2))
        WHERE YEAR(a.tgl_aduan)=? AND MONTH(a.tgl_aduan)=?
        ORDER BY a.tgl_aduan DESC
    ");
    $stmt->execute([$tahun,$bulan]);
    return $stmt->fetchAll();
}

// === FUNGSI MASTER DATA ===
function getAllFasilitas() {
    global $pdo;
    return $pdo->query("SELECT * FROM fasilitas ORDER BY nama_fasilitas")->fetchAll();
}

function getAllStatus() {
    global $pdo;
    return $pdo->query("SELECT * FROM status ORDER BY id_status")->fetchAll();
}

function getStatusForSelect($current=null) {
    $status  = getAllStatus();
    $options = '';
    foreach ($status as $s) {
        $mapped_id = 'S' . substr($s['id_status'], 2);
        $selected  = ($current && $current == $mapped_id) ? 'selected' : '';
        $options  .= "<option value=\"$mapped_id\" $selected>" . htmlspecialchars($s['status']) . "</option>";
    }
    return $options;
}