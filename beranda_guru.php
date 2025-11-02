<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Proteksi: hanya guru_mapel dan wali_kelas yang boleh akses
$allowed_roles = ['guru_mapel', 'wali_kelas'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/db.php';

$guru_id = $_SESSION['id'];
$guru_nama = $_SESSION['nama'] ?? 'Guru';

// Helper nama hari
function hariID($timestamp = null) {
    $map = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
    return $map[intval(date('N', $timestamp ?? time()))] ?? '';
}

$hari_ini = hariID();
$tanggal_sql = date('Y-m-d');

// ==================== INFORMASI DARI ADMIN ====================
$sql_informasi = "
    SELECT * FROM informasi 
    WHERE (ditujukan = 'guru' OR ditujukan = 'umum')
    AND tanggal >= DATE_SUB(NOW(), INTERVAL 24 HOUR)  -- Hanya 24 jam terakhir
    ORDER BY tanggal DESC LIMIT 3";
$informasi_result = $conn->query($sql_informasi);
$informasi_list = $informasi_result ? $informasi_result->fetch_all(MYSQLI_ASSOC) : [];

// ==================== STATISTIK ====================

// 1) Total kelas yang diajar (berdasarkan jadwal)
$stmt = $conn->prepare("SELECT COUNT(*) AS total_kelas FROM jadwal WHERE id_guru = ?");
$stmt->bind_param("i", $guru_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_kelas = $res['total_kelas'] ?? 0;
$stmt->close();

// 2) Total jadwal hari ini
$stmt = $conn->prepare("SELECT COUNT(*) AS total_jadwal_hari FROM jadwal WHERE id_guru = ? AND hari = ?");
$stmt->bind_param("is", $guru_id, $hari_ini);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_jadwal_hari = $res['total_jadwal_hari'] ?? 0;
$stmt->close();

// 3) Total absensi hari ini
$stmt = $conn->prepare("SELECT COUNT(*) AS total_absensi FROM absensi WHERE id_guru = ? AND tanggal = ?");
$stmt->bind_param("is", $guru_id, $tanggal_sql);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_absensi = $res['total_absensi'] ?? 0;
$stmt->close();

// 4) Status absensi
$stmt = $conn->prepare("SELECT status_edit FROM absensi WHERE tanggal = ? LIMIT 1");
$stmt->bind_param("s", $tanggal_sql);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$status_absensi = $res['status_edit'] ?? 'open';
$stmt->close();

// 5) Info hadir hari ini
$hadir_hari_ini = "$total_absensi/$total_jadwal_hari Jadwal";

// 6) Jadwal berikutnya
$sql_jadwal_berikutnya = "
    SELECT j.*, m.nama_mapel 
    FROM jadwal j
    LEFT JOIN mapel m ON j.id_mapel = m.id
    WHERE j.id_guru = ? AND j.hari = ?
    ORDER BY j.jam_mulai ASC
    LIMIT 1
";
$stmt = $conn->prepare($sql_jadwal_berikutnya);
$stmt->bind_param("is", $guru_id, $hari_ini);
$stmt->execute();
$jadwal_berikutnya = $stmt->get_result()->fetch_assoc();
$jadwal_berikutnya_text = $jadwal_berikutnya ? $jadwal_berikutnya['nama_mapel'] : "Tidak ada jadwal";
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Guru</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #d9ebd0 0%, #c1dec8 100%);
        min-height: 100vh;
        padding: 20px;
        line-height: 1.5;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header */
    .header {
        background: linear-gradient(135deg, #f68c2e 0%, #ff9d4d 100%);
        color: white;
        padding: 24px 30px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(246, 140, 46, 0.3);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: #f68c2e;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }

    .header-info h3 {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 6px;
    }

   .avatar-container {
    position: relative;
    display: inline-block;
}

.header-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #f68c2e;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.edit-avatar-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #f68c2e 0%, #ff9d4d 100%);
    border: 2px solid white;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(246, 140, 46, 0.4);
}

.edit-avatar-btn:hover {
    transform: scale(1.1);
    background: linear-gradient(135deg, #e67e22 0%, #f68c2e 100%);
    box-shadow: 0 4px 12px rgba(246, 140, 46, 0.6);
}

/* Optional: Hover effect pada avatar */
.avatar-container:hover .header-avatar {
    transform: scale(1.05);
}
    /* Main Grid Layout - SAMA untuk desktop & mobile */
    .main-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.3s ease;
        border-left: 4px solid #4d6651;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: 24px;
        color: #4d6651;
    }

    .stat-content h4 {
        font-size: 14px;
        color: #6b8e70;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .stat-content p {
        font-size: 18px;
        color: #2c3e2f;
        font-weight: 600;
    }

    /* Status & Quick Access Row */
    .action-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Status Absensi */
    .status-absensi {
        background: <?= $status_absensi == 'open' ? 'linear-gradient(135deg, #28a745 0%, #34ce57 100%)' : 'linear-gradient(135deg, #6c757d 0%, #868e96 100%)' ?>;
        color: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 12px <?= $status_absensi == 'open' ? 'rgba(40, 167, 69, 0.3)' : 'rgba(108, 117, 125, 0.3)' ?>;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-icon {
        font-size: 24px;
        flex-shrink: 0;
    }

    .status-content h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
        opacity: 0.9;
    }

    .status-content p {
        font-size: 13px;
        opacity: 0.9;
    }

    /* Quick Access */
    .quick-access {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    .quick-access h4 {
        font-size: 16px;
        color: #2c3e2f;
        margin-bottom: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quick-access h4 i {
        color: #f68c2e;
    }

    .quick-buttons {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .quick-btn {
        height: 70px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: white;
        font-size: 20px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .quick-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .quick-btn span {
        font-size: 11px;
        font-weight: 600;
    }

    .btn-green { 
        background: linear-gradient(135deg, #4d6651 0%, #5a7a5e 100%);
    }
    
    .btn-orange { 
        background: linear-gradient(135deg, #f68c2e 0%, #ff9d4d 100%);
    }
    
    .btn-gray { 
        background: linear-gradient(135deg, #a0b5a0 0%, #b5c9b5 100%);
    }

    /* Informasi Section - KECIL & COMPACT */
    .informasi-section {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    .informasi-header {
        display: flex;
        align-items: center;
        justify-content: between;
        margin-bottom: 16px;
        cursor: pointer;
    }

    .informasi-header h4 {
        font-size: 16px;
        color: #2c3e2f;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .informasi-header h4 i {
        color: #f68c2e;
    }

    .informasi-toggle {
        color: #6b8e70;
        font-size: 12px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background 0.3s ease;
    }

    .informasi-toggle:hover {
        background: #f5f5f5;
    }

    .informasi-list {
        display: none; /* Default hidden */
        space-y: 12px;
    }

    .informasi-list.show {
        display: block;
    }

    .informasi-item {
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid #f68c2e;
    }

    .informasi-item:last-child {
        margin-bottom: 0;
    }

    .informasi-judul {
        font-size: 13px;
        font-weight: 600;
        color: #2c3e2f;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .informasi-isi {
        font-size: 12px;
        color: #6b8e70;
        line-height: 1.4;
    }

    .informasi-meta {
        font-size: 10px;
        color: #a0b5a0;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .informasi-badge {
        background: #4d6651;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 600;
    }

    .no-informasi {
        text-align: center;
        padding: 20px;
        color: #a0b5a0;
        font-size: 13px;
    }

    .no-informasi i {
        font-size: 32px;
        margin-bottom: 8px;
        display: block;
    }
    .floating-logout {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
}

.floating-btn {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 20px;
    box-shadow: 0 4px 16px rgba(220, 53, 69, 0.4);
    transition: all 0.3s ease;
}

.floating-btn:hover {
    transform: scale(1.1) translateY(-3px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.6);
    color: white;
}

    /* Responsive Design */
    @media (max-width: 768px) {
        body {
            padding: 15px;
        }

        .header {
            padding: 20px;
            border-radius: 16px;
        }

        .header-avatar {
            width: 60px;
            height: 60px;
            font-size: 28px;
        }

        .header-info h3 {
            font-size: 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .stat-card {
            padding: 20px;
        }

        .action-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        .floating-btn {
        width: 50px;
        height: 50px;
        font-size: 18px;
    }

        .status-absensi,
        .quick-access {
            padding: 18px;
        }

        .quick-buttons {
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .quick-btn {
            height: 65px;
            font-size: 18px;
        }

        .quick-btn span {
            font-size: 10px;
        }

        .informasi-section {
            padding: 18px;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 10px;
        }

        .header {
            padding: 16px;
            border-radius: 14px;
        }

        .header-avatar {
            width: 50px;
            height: 50px;
            font-size: 24px;
        }

        .header-info h3 {
            font-size: 18px;
        }

        .header-info p {
            font-size: 13px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
        }

        .stat-icon i {
            font-size: 20px;
        }

        .stat-content p {
            font-size: 16px;
        }

        .quick-btn {
            height: 60px;
            font-size: 16px;
        }

        .informasi-section {
            padding: 16px;
            border-radius: 14px;
        }
    }
</style>
</head>
<body>

<div class="container">
    <!-- Ambil foto profil -->
<?php
$stmt = $conn->prepare("SELECT foto_profil FROM guru_foto_profil WHERE guru_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $guru_id);
$stmt->execute();
$foto_result = $stmt->get_result();
$foto_data = $foto_result->fetch_assoc();
$foto_profil = $foto_data['foto_profil'] ?? null;
$stmt->close();
?>

<div class="header">
    <a href="profil.php" class="avatar-container" style="text-decoration: none;">
        <div class="header-avatar">
            <?php if ($foto_profil): ?>
                <img src="../uploads/profil/<?= $foto_profil ?>?t=<?= time() ?>" 
                     alt="Foto Profil" 
                     style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <?= strtoupper(substr($guru_nama, 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="edit-avatar-btn">
            <i class="fas fa-pencil-alt"></i>
        </div>
    </a>
    <div class="header-info">
        <h3><?= htmlspecialchars($guru_nama) ?></h3>
        <p><?= ucfirst($_SESSION['role']) ?> • <?= $hari_ini ?>, <?= date('d M Y') ?></p>
    </div>
</div>

<div class="floating-logout">
    <a href="logout.php" class="floating-btn" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
    </a>
</div>

    <!-- Main Grid - URUTAN SAMA desktop & mobile -->
    <div class="main-grid">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h4>Hadir Hari Ini</h4>
                    <p><?= $hadir_hari_ini ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h4>Jadwal Berikutnya</h4>
                    <p><?= htmlspecialchars($jadwal_berikutnya_text) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-content">
                    <h4>Total Kelas Diampu</h4>
                    <p><?= $total_kelas ?> Kelas</p>
                </div>
            </div>
        </div>

        <!-- Status & Quick Access -->
        <div class="action-row">
            <!-- Status Absensi -->
            <div class="status-absensi">
                <div class="status-icon">
                    <?= $status_absensi == 'open' ? '✅' : '❌' ?>
                </div>
                <div class="status-content">
                    <h4>STATUS ABSENSI: <?= strtoupper($status_absensi) ?></h4>
                    <p><?= $status_absensi == 'open' ? 'Guru dapat menginput absensi hari ini' : 'Periode absensi telah ditutup' ?></p>
                </div>
            </div>

            <!-- Di bagian Quick Access -->
<div class="quick-buttons">
    <a href="jadwal.php" class="quick-btn btn-orange">
        <i class="fas fa-calendar-alt"></i>
        <span>Jadwal</span>
    </a>
    <a href="notifikasi.php" class="quick-btn btn-gray">
        <i class="fas fa-bell"></i>
        <span>Notifikasi</span>
    </a>
</div>
            </div>
        </div>

        <!-- Informasi Section - KECIL & COMPACT -->
        <div class="informasi-section">
            <div class="informasi-header" onclick="toggleInformasi()">
                <h4><i class="fas fa-info-circle"></i> Informasi Terbaru</h4>
                <button class="informasi-toggle">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            
            <div class="informasi-list" id="informasiList">
                <?php if (count($informasi_list) > 0): ?>
                    <?php foreach ($informasi_list as $info): ?>
                        <div class="informasi-item">
                            <div class="informasi-judul">
                                <?= htmlspecialchars($info['judul']) ?>
                                <span class="informasi-badge"><?= strtoupper($info['ditujukan']) ?></span>
                            </div>
                            <div class="informasi-isi">
                                <?= nl2br(htmlspecialchars($info['isi'])) ?>
                            </div>
                            <div class="informasi-meta">
                                <i class="fas fa-clock"></i>
                                <?= date('d/m/Y H:i', strtotime($info['tanggal'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-informasi">
                        <i class="fas fa-bell-slash"></i>
                        <p>Tidak ada informasi baru</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleInformasi() {
        const informasiList = document.getElementById('informasiList');
        const toggleBtn = document.querySelector('.informasi-toggle i');
        
        informasiList.classList.toggle('show');
        
        if (informasiList.classList.contains('show')) {
            toggleBtn.className = 'fas fa-chevron-up';
        } else {
            toggleBtn.className = 'fas fa-chevron-down';
        }
    }

    // Optional: Auto show if there are new informations
    <?php if (count($informasi_list) > 0): ?>
    // informasiList.classList.add('show');
    // document.querySelector('.informasi-toggle i').className = 'fas fa-chevron-up';
    <?php endif; ?>
</script>

</body>
</html>
