<?php
require_once 'auth.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Fulfillment</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <!-- Bootstrap CSS v5.3.8 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="d-flex">

        <div id="sidebar-container"></div>

            <div class="content">

                <nav class="navbar bg-white shadow-sm px-4">

            <span class="navbar-brand fw-semibold">
                Inbox Task
            </span>
            <div class="ms-auto d-flex align-items-center">
                <i class="bi bi-bell fs-5 me-4"></i>

                    <img src="https://i.pravatar.cc/40" class="rounded-circle me-2">

                    <div class="me-3">
                        <div class="fw-semibold"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($_SESSION['role']) ?></small>
                    </div>

                    <a href="logout.php"
                    class="btn btn-outline-danger btn-sm"
                    onclick="return confirm('Yakin ingin logout?')">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>

                </div>

        <!-- paste kode temanmu di sini -->
        <div class="container-fluid p-4">

            <div class="container-fluid p-4">

            <!-- Judul -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h3 class="mb-0 fw-bold">Inbox Task</h3>
                    <small class="text-muted">
                        Kelola seluruh task yang masuk ke tim NetOps
                    </small>
                </div>

                <div>
                    <button class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i>
                        Refresh
                    </button>
                </div>

            </div>

            <!-- Card Filter -->
            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <div class="row g-3 align-items-end">

                        <!-- Search -->
                        <div class="col-lg-4">

                            <label class="form-label fw-semibold">
                                Search
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    id="searchInput"
                                    class="form-control"
                                    placeholder="Cari ID Task atau Customer">

                            </div>

                        </div>

                        <!-- Status -->
                        <div class="col-lg-2">

                            <label class="form-label fw-semibold">
                                Status
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Semua Status
                                </option>

                                <option>
                                    Open
                                </option>

                                <option>
                                    On Progress
                                </option>

                                <option>
                                    Waiting
                                </option>

                                <option>
                                    Closed
                                </option>

                            </select>

                        </div>

                        <!-- Tipe -->
                        <div class="col-lg-2">

                            <label class="form-label fw-semibold">
                                Tipe
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Semua Tipe
                                </option>

                                <option>
                                    Incident
                                </option>

                                <option>
                                    Request
                                </option>

                                <option>
                                    Maintenance
                                </option>

                            </select>

                        </div>

                        <!-- Prioritas -->
                        <div class="col-lg-2">

                            <label class="form-label fw-semibold">
                                Prioritas
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Semua Prioritas
                                </option>

                                <option>
                                    High
                                </option>

                                <option>
                                    Medium
                                </option>

                                <option>
                                    Low
                                </option>

                            </select>

                        </div>

                        <!-- SLA -->
                        <div class="col-lg-1">

                            <label class="form-label fw-semibold">
                                SLA
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Semua
                                </option>

                                <option>
                                    Dalam SLA
                                </option>

                                <option>
                                    Lewat SLA
                                </option>

                            </select>

                        </div>

                        <!-- Filter -->
                        <div class="col-lg-1 d-grid">

                            <button class="btn btn-primary">

                                <i class="bi bi-funnel"></i>

                                Filter

                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <br>

        <!-- Card Table -->
        <div class="card shadow-sm border-0">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>
                        <h5 class="mb-0 fw-bold">Daftar Task</h5>
                        <small class="text-muted">
                            Menampilkan seluruh task yang tersedia
                        </small>
                    </div>

                 <button class="btn btn-primary">

                                <i class="bi bi-funnel"></i>

                             + buat taks baru

                            </button>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle text-center" id="taskTable">

                        <thead class="table-light">

                            <tr>

                                <th>ID Task</th>
                                <th>Tipe</th>
                                <th>Customer</th>
                                <th>Area</th>
                                <th>SLA</th>
                                <th>Sisa Waktu</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>

                        </thead>

<?php
// =========================================================
// AMBIL DATA DARI GOOGLE APPS SCRIPT (pakai cURL, bukan file_get_contents)
// Dengan cache lokal: kalau request ke Google gagal/timeout,
// halaman tetap tampil pakai data terakhir yang berhasil diambil.
// =========================================================
$url       = "https://script.google.com/macros/s/AKfycbyvFfCD6V-DuaKkypPH9OWL21BuTG8kq3wGWh8fSmZdVrCikpcvjOax1gS1vDBLe_Bvbw/exec";
$cacheFile = __DIR__ . "/cache_tasks.json";
$cacheMaxAge = 300; // detik, boleh diubah sesuai kebutuhan

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // penting! Apps Script sering redirect
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);    // gagal connect cepat, jangan nunggu lama
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // hanya kalau memang ada masalah SSL di XAMPP lokal

$json      = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

$data      = json_decode($json, true);
$fromCache = false;

if ($json === false || !isset($data['tasks'])) {
    // request gagal atau struktur tidak sesuai -> coba pakai cache lama
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (isset($cached['tasks'])) {
            $data      = $cached;
            $fromCache = true;
        }
    }
} else {
    // request sukses -> simpan sebagai cache untuk request berikutnya
    file_put_contents($cacheFile, $json);
}

// Variabel pagination didefinisikan DI LUAR blok if,
// supaya tidak pernah "undefined" walau data gagal diambil
$limit = 5;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$rows      = [];
$totalData = 0;
$totalPage = 1;
$start     = 0;

if (isset($data['tasks'])) {
    // key yang benar adalah 'tasks', bukan 'data'
    // dicek dari $data, bukan $json, supaya data hasil cache tetap terpakai

    $totalData = count($data['tasks']);
    $totalPage = max(1, ceil($totalData / $limit));
    $start     = ($page - 1) * $limit;
    $rows      = array_slice($data['tasks'], $start, $limit);
}
?>

<tbody>
<?php
if (count($rows) > 0) {

    foreach ($rows as $row) {

        // Badge Prioritas
        $priorityClass = "secondary";
        if ($row['prioritas'] == "High") {
            $priorityClass = "danger";
        } elseif ($row['prioritas'] == "Medium") {
            $priorityClass = "warning";
        } elseif ($row['prioritas'] == "Low") {
            $priorityClass = "success";
        }

        // Badge Status
        $statusClass = "secondary";
        if ($row['status'] == "Open") {
            $statusClass = "danger";
        } elseif ($row['status'] == "On Progress") {
            $statusClass = "primary";
        } elseif ($row['status'] == "Waiting") {
            $statusClass = "warning";
        } elseif ($row['status'] == "Closed") {
            $statusClass = "success";
        }

        echo "<tr>";
        // 'id' bukan 'id_task' -> sesuai field dari Apps Script
        echo "<td>" . htmlspecialchars($row['id'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['tipe'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['customer'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['area'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['sla'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['sisa_waktu'] ?? '-') . "</td>";
        echo "<td><span class='badge bg-{$priorityClass}'>" . htmlspecialchars($row['prioritas'] ?? '-') . "</span></td>";
        echo "<td><span class='badge bg-{$statusClass}'>" . htmlspecialchars($row['status'] ?? '-') . "</span></td>";
        echo "<td>
                <button class='btn btn-sm btn-outline-primary'>
                    <i class='bi bi-eye'></i>
                </button>
              </td>";
        echo "</tr>";
    }

} else {
    echo "<tr><td colspan='9' class='text-center'>Tidak ada data</td></tr>";
}
?>
</tbody>

                    </table>

                </div>

                <?php if ($fromCache): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        Tidak bisa terhubung ke server saat ini, menampilkan data cache terakhir
                        (<?= file_exists($cacheFile) ? date("d M Y H:i:s", filemtime($cacheFile)) : '-' ?>).
                    </div>
                <?php elseif (!isset($data['tasks'])): ?>
                    <div class="alert alert-danger mt-3 mb-0">
                        <?php if ($curlError): ?>
                            Gagal mengambil data dari server: <?= htmlspecialchars($curlError) ?>
                        <?php elseif ($json === false): ?>
                            Gagal menghubungi Google Apps Script.
                        <?php elseif (json_last_error() !== JSON_ERROR_NONE): ?>
                            Response bukan JSON valid: <?= htmlspecialchars(json_last_error_msg()) ?>
                        <?php else: ?>
                            Struktur data dari server tidak sesuai (field 'tasks' tidak ditemukan).
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->

                <div class="d-flex justify-content-between align-items-center mt-3">

                    <small class="text-muted">
                        <?php
                        $from = $totalData > 0 ? $start + 1 : 0;
                        $to   = min($start + $limit, $totalData);
                        ?>
                        Menampilkan <?= $from ?> - <?= $to ?> dari <?= $totalData ?> Task
                    </small>

                    <nav>

 <ul class="pagination mb-0">

<!-- Previous -->
<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= max(1,$page-1) ?>">
        Previous
    </a>
</li>

<?php

$startPage = max(1, $page - 2);
$endPage   = min($totalPage, $page + 2);

if($startPage > 1){
    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';

    if($startPage > 2){
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
}

for($i=$startPage; $i<=$endPage; $i++){
?>

<li class="page-item <?= ($i==$page)?'active':'' ?>">
    <a class="page-link" href="?page=<?= $i ?>">
        <?= $i ?>
    </a>
</li>

<?php
}

if($endPage < $totalPage){

    if($endPage < $totalPage-1){
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    echo '<li class="page-item"><a class="page-link" href="?page='.$totalPage.'">'.$totalPage.'</a></li>';
}
?>

<!-- Next -->
<li class="page-item <?= ($page >= $totalPage) ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= min($totalPage,$page+1) ?>">
        Next
    </a>
</li>

</ul>

                    </nav>

                </div>

            </div>

        </div>

        <br>

        <!-- Detail Task -->
        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">
                    Detail Task
                </h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <!-- Informasi Task -->
                    <div class="col-lg-6">

                        <h6 class="fw-bold mb-3">
                            Informasi Task
                        </h6>

                        <table class="table table-borderless">

                            <tr>
                                <th width="35%">ID Task</th>
                                <td>: TK-001</td>
                            </tr>

                            <tr>
                                <th>Tipe</th>
                                <td>: Incident</td>
                            </tr>

                            <tr>
                                <th>Customer</th>
                                <td>: PT ABC</td>
                            </tr>

                            <tr>
                                <th>Area</th>
                                <td>: Jakarta</td>
                            </tr>

                            <tr>
                                <th>Prioritas</th>
                                <td>
                                    :
                                    <span class="badge bg-danger">
                                        High
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Status</th>
                                <td>
                                    :
                                    <span class="badge bg-danger">
                                        Open
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>SLA</th>
                                <td>: 4 Jam</td>
                            </tr>

                            <tr>
                                <th>Sisa Waktu</th>
                                <td>
                                    <span class="text-danger fw-bold">
                                        -
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Dibuat</th>
                                <td>: 21 Juli 2025 08:30</td>
                            </tr>

                        </table>

                    </div>

                    <!-- Update Task -->
                    <div class="col-lg-6">

                        <h6 class="fw-bold mb-3">
                            Update Task
                        </h6>

                        <div class="mb-3">

                            <label class="form-label">
                                Update Status
                            </label>

                            <select class="form-select">

                                <option>Open</option>

                                <option selected>
                                    On Progress
                                </option>

                                <option>
                                    Waiting
                                </option>

                                <option>
                                    Closed
                                </option>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Catatan
                            </label>

                            <textarea
                                class="form-control"
                                rows="5"
                                placeholder="Masukkan catatan update task..."></textarea>

                        </div>

                        <div class="mb-4">

                            <label class="form-label">
                                Upload Lampiran
                            </label>

                            <input
                                type="file"
                                class="form-control">

                        </div>

                        <div class="text-end">

                            <button class="btn btn-secondary me-2">

                                <i class="bi bi-x-circle"></i>

                                Batal

                            </button>

                            <button class="btn btn-primary">

                                <i class="bi bi-check-circle"></i>

                                Simpan

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        </div>

        </div>

    </div>

</div>
        <script>
            fetch('sidebar.php')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('sidebar-container').innerHTML = html;
                    const links = document.querySelectorAll('.sidebar .nav-link');
                    links.forEach(link => {
                        if (link.href === window.location.href) {
                            link.classList.add('active');
                        }
                    });
                });
        </script>
    </body>
</html>