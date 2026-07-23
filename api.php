<?php
// api.php - Endpoint AJAX (JSON only)
require_once 'functions.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$act = $_GET['act'] ?? '';

// 🔧 Fungsi: Buat riwayat simulasi (tanpa tabel baru)
function buatRiwayatSimulasi($tgl_aduan, $status, $nama_pelapor) {
    $riwayat = [];
    
    // Tentukan step berdasarkan status
    $steps = [];
    if ($status === 'S003' || $status === 'Selesai') {
        $steps = [
            ['label'=>'Dilaporkan', 'icon'=>'📋', 'pic'=>$nama_pelapor ?: 'Sistem', 'catatan'=>'Laporan diterima'],
            ['label'=>'Diproses', 'icon'=>'🔧', 'pic'=>'Admin', 'catatan'=>'Sedang ditindaklanjuti'],
            ['label'=>'Selesai', 'icon'=>'✅', 'pic'=>'Admin', 'catatan'=>'Perbaikan selesai']
        ];
    } elseif ($status === 'S002' || $status === 'Proses') {
        $steps = [
            ['label'=>'Dilaporkan', 'icon'=>'📋', 'pic'=>$nama_pelapor ?: 'Sistem', 'catatan'=>'Laporan diterima'],
            ['label'=>'Diproses', 'icon'=>'🔧', 'pic'=>'Admin', 'catatan'=>'Sedang ditindaklanjuti']
        ];
    } else {
        $steps = [
            ['label'=>'Dilaporkan', 'icon'=>'📋', 'pic'=>$nama_pelapor ?: 'Sistem', 'catatan'=>'Laporan diterima']
        ];
    }
    
    // Bagi waktu antar step
    $start = strtotime($tgl_aduan);
    $now = time();
    $total = count($steps);
    $interval = $total > 1 ? ($now - $start) / ($total - 1) : 0;
    
    foreach ($steps as $i => $s) {
        $waktu = $total > 1 ? $start + ($interval * $i) : $start;
        $riwayat[] = [
            'label_status' => $s['label'],
            'icon' => $s['icon'],
            'nama_pic' => $s['pic'],
            'catatan' => $s['catatan'],
            'created_at' => date('Y-m-d H:i:s', $waktu)
        ];
    }
    
    return $riwayat;
}

// ✅ Endpoint: cek_status (untuk lacak aduan di index.php)
if ($act === 'cek_status') {
    $keyword = trim($_GET['q'] ?? '');
    if (empty($keyword)) {
        http_response_code(400);
        echo json_encode(['found' => false, 'message' => 'Kata kunci kosong']);
        exit;
    }
    
    try {
        $results = searchAduan($keyword);
        if (!empty($results) && is_array($results)) {
            $data = [];
            foreach ($results as $row) {
                // Konversi status text ke kode
                $kode = match($row['status'] ?? '') {
                    'Selesai' => 'S003', 'Proses' => 'S002', default => 'S001'
                };
                
                $data[] = [
                    'id_aduan' => $row['id_aduan'],
                    'tgl_aduan' => $row['tgl_aduan'],
                    'fasilitas' => $row['fasilitas'],
                    'status' => $row['status'] ?? 'Dilaporkan',
                    'jumlah' => (int)($row['jumlah_fasilitas'] ?? 1),
                    'keterangan' => $row['keterangan_aduan'] ?? '',
                    'nama' => $row['nama'] ?? '',
                    'kelas' => $row['kelas'] ?? '',
                    'riwayat' => buatRiwayatSimulasi($row['tgl_aduan'], $kode, $row['nama'] ?? '') // ✅ INI KUNCINYA
                ];
            }
            echo json_encode(['found' => true, 'count' => count($data), 'data' => $data]);
        } else {
            echo json_encode(['found' => false, 'message' => 'Tidak ditemukan']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['found' => false, 'message' => 'Server error']);
    }
    exit;
}

// ✅ Endpoint: get_log (untuk modal riwayat di admin.php)
if ($act === 'get_log') {
    $id = $_GET['id'] ?? '';
    if (!preg_match('/^A\d{3}$/', $id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit;
    }
    
    try {
        $aduan = getAduanById($id);
        if (!$aduan) {
            echo json_encode(['success' => false, 'message' => 'Aduan tidak ditemukan']);
            exit;
        }
        
        $kode = match($aduan['status'] ?? '') {
            'Selesai' => 'S003', 'Proses' => 'S002', default => 'S001'
        };
        
        $riwayat = buatRiwayatSimulasi($aduan['tgl_aduan'], $kode, $aduan['nama'] ?? '');
        echo json_encode(['success' => true, 'data' => $riwayat]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
?>