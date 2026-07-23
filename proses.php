<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'functions.php';
global $pdo;
$act = $_REQUEST['act'] ?? '';

try {
    // ── Tambah Aduan ──────────────────────────────────────────
    if ($act === 'add') {
        if (empty($_POST['nama']) || empty($_POST['kelas']) || empty($_POST['id_fasilitas'])) {
            throw new Exception('Nama, Kelas, dan Fasilitas wajib diisi.');
        }
        $ruangan_allowed = ['ASM 1','ASM 2','ASM 3'];
        $ruangan = in_array($_POST['ruangan']??'', $ruangan_allowed) ? $_POST['ruangan'] : 'ASM 1';
        $keterangan_final = "[Ruangan: $ruangan] " . trim($_POST['keterangan']);

        // Handle upload bukti
        if (empty($_FILES['bukti']['tmp_name'])) {
            throw new Exception('Bukti wajib disertakan.');
        }
        $allowed_types = ['image/jpeg','image/png','image/jpg','image/webp','application/pdf'];
        $file_type = mime_content_type($_FILES['bukti']['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau PDF.');
        }
        if ($_FILES['bukti']['size'] > 5 * 1024 * 1024) {
            throw new Exception('Ukuran file melebihi 5 MB.');
        }
        $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $upload_dir = __DIR__ . '/uploads/bukti/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $filename = 'bukti_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $upload_dir . $filename)) {
            throw new Exception('Gagal menyimpan file bukti.');
        }

        $data = [
            'nama'        => trim($_POST['nama']),
            'kelas'       => trim($_POST['kelas']),
            'id_fasilitas'=> $_POST['id_fasilitas'],
            'jumlah'      => max(1, (int)$_POST['jumlah']),
            'keterangan'  => $keterangan_final,
            'tgl_aduan'   => $_POST['tgl_aduan'] ?? date('Y-m-d'),
            'bukti'       => $filename
        ];

        $id_aduan = addAduan($data);
        if ($id_aduan) {
            header("Location: index.php?success=1&id=" . urlencode($id_aduan));
            exit;
        }
        throw new Exception('Gagal menyimpan aduan.');

    // ── Update Status (dengan log) ────────────────────────────
    } elseif ($act === 'update_status' && isAdmin()) {
        $allowed_status = ['S001','S002','S003'];
        if (!in_array($_POST['id_status'], $allowed_status)) {
            throw new Exception('Status tidak valid.');
        }

        $id_aduan  = $_POST['id_aduan']  ?? '';
        $id_status = $_POST['id_status'] ?? '';
        $catatan   = trim($_POST['catatan'] ?? '');

        // Nama admin dari session
        $nama_pic = $_SESSION['admin'] ?? 'Admin';

        if (updateStatusWithLog($id_aduan, $id_status, $nama_pic, $catatan)) {
            setFlash('Status berhasil diubah', 'success');
        } else {
            throw new Exception('Gagal memperbarui status.');
        }

    // ── Hapus Aduan ───────────────────────────────────────────
    } elseif ($act === 'delete' && isAdmin()) {
        if (!preg_match('/^A\d{3}$/', $_GET['id'] ?? '')) {
            throw new Exception('ID aduan tidak valid.');
        }
        if (deleteAduan($_GET['id'])) {
            setFlash('Data berhasil dihapus', 'success');
        } else {
            throw new Exception('Gagal menghapus aduan.');
        }

    // ── Hapus Rekap ───────────────────────────────────────────
    } elseif ($act === 'delete_rekap' && isAdmin()) {
        $bulan = $_GET['bulan'] ?? null;
        $tahun = $_GET['tahun'] ?? null;
        if (!$bulan || !$tahun) throw new Exception('Parameter bulan/tahun tidak valid.');
        if (deleteRekapByPeriod($bulan, $tahun)) {
            setFlash('Riwayat rekap berhasil dihapus.', 'success');
        } else {
            throw new Exception('Gagal menghapus rekap.');
        }

    // ── Generate Rekap ────────────────────────────────────────
    } elseif ($act === 'generate_rekap' && isAdmin()) {
        $bulan  = $_GET['bulan'] ?? date('m');
        $tahun  = $_GET['tahun'] ?? date('Y');
        $result = generateRekapBulanan($bulan, $tahun);
        setFlash($result['message'], $result['success'] ? 'success' : 'warning');
        header("Location: rekap.php?bulan=$bulan&tahun=$tahun");
        exit;

    // ── Login ─────────────────────────────────────────────────
    } elseif ($act === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($username) || empty($password)) {
            throw new Exception('Username dan password wajib diisi.');
        }
        $stmt = $pdo->prepare("SELECT * FROM akun WHERE username = ?");
        $stmt->execute([$username]);
        $akun = $stmt->fetch();
        if ($akun && $akun['pw'] === $password) {
            $_SESSION['admin'] = $akun['username'];
            header("Location: admin.php");
            exit;
        }
        setFlash('Username atau password salah!', 'error');
        header("Location: login.php");
        exit;

    // ── Logout ────────────────────────────────────────────────
    } elseif ($act === 'logout') {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    }

} catch (Exception $e) {
    setFlash('Error: ' . $e->getMessage(), 'error');
}

$redirect = isAdmin() ? 'admin.php' : 'index.php';
if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'proses.php') === false) {
    $redirect = $_SERVER['HTTP_REFERER'];
}
header("Location: $redirect");
exit;