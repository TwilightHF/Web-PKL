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
                <span>Andy Pratama</span>
            </div>
        </nav>

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

        <form method="GET">

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
                            name="search"
                            class="form-control"
                            placeholder="Cari ID Task atau Customer"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

                        <button class="btn btn-primary" type="submit">
                            Cari
                        </button>

                    </div>

                </div>

                <!-- Status -->
                <div class="col-lg-2">

                    <label class="form-label fw-semibold">
                        Status
                    </label>

                    <select class="form-select" name="status">

                        <option value="">Semua Status</option>
                        <option value="Open">Open</option>
                        <option value="On Progress">On Progress</option>
                        <option value="Waiting">Waiting</option>
                        <option value="Closed">Closed</option>

                    </select>

                </div>

                <!-- Tipe -->
                <div class="col-lg-2">

                    <label class="form-label fw-semibold">
                        Tipe
                    </label>

                    <select class="form-select" name="tipe">

                        <option value="">Semua Tipe</option>
                        <option value="Incident">Incident</option>
                        <option value="Request">Request</option>
                        <option value="Maintenance">Maintenance</option>

                    </select>

                </div>

                <!-- Prioritas -->
                <div class="col-lg-2">

                    <label class="form-label fw-semibold">
                        Prioritas
                    </label>

                    <select class="form-select" name="prioritas">

                        <option value="">Semua Prioritas</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>

                    </select>

                </div>

                <!-- SLA -->
                <div class="col-lg-1">

                    <label class="form-label fw-semibold">
                        SLA
                    </label>

                    <select class="form-select" name="sla">

                        <option value="">Semua</option>
                        <option value="Dalam SLA">Dalam SLA</option>
                        <option value="Lewat SLA">Lewat SLA</option>

                    </select>

                </div>

                <!-- Tombol Filter -->
                <div class="col-lg-1 d-grid">

                    <button class="btn btn-primary" type="submit">

                        <i class="bi bi-funnel"></i>

                        Filter

                    </button>

                </div>

            </div>

        </form>

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

<tbody>

<?php
// Ambil keyword dari form search
$search = $_GET['search'] ?? "";

$url = "https://script.google.com/macros/s/AKfycbwd0KS3yqXh152ifNHNYpNLLjDqrQDyS30Yta5LkrEUkJwuNENbpFHKA0M-9NKJjbqzwQ/exec";

if($search != ""){
    $url .= "?search=" . urlencode($search);
}
// Ambil data
$json = file_get_contents($url);

$data = json_decode($json, true);

// =======================
// Detail Task
// =======================
$detail = null;

if(isset($_GET['id'])){

    $id = $_GET['id'];

    foreach($data['tasks'] as $task){

        if($task['id'] == $id){

            $detail = $task;
            break;

        }

    }

}

// =======================
// Daftar Task
// =======================
if(isset($data['tasks'])){

    // Jumlah data per halaman
    $limit = 5;

    // Halaman aktif
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    if($page < 1){
        $page = 1;
    }

// Total data
$totalData = count($data['tasks']);

// Total halaman
$totalPage = ceil($totalData / $limit);

// Data mulai
$start = ($page - 1) * $limit;

// Ambil hanya 5 data
$rows = array_slice($data['tasks'], $start, $limit);

foreach($rows as $row){

        // Badge Prioritas
        $priorityClass = "secondary";

        if($row['prioritas']=="High"){
            $priorityClass="danger";
        }elseif($row['prioritas']=="Medium"){
            $priorityClass="warning";
        }elseif($row['prioritas']=="Low"){
            $priorityClass="success";
        }

        // Badge Status
        $statusClass="secondary";

        if($row['status']=="Open"){
            $statusClass="danger";
        }elseif($row['status']=="On Progress"){
            $statusClass="primary";
        }elseif($row['status']=="Waiting"){
            $statusClass="warning";
        }elseif($row['status']=="Closed"){
            $statusClass="success";
        }

        echo "<tr>";

        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['tipe']}</td>";
        echo "<td>{$row['customer']}</td>";
        echo "<td>{$row['area']}</td>";
        echo "<td>{$row['sla']}</td>";
        echo "<td>{$row['sisa_waktu']}</td>";

        echo "<td><span class='badge bg-$priorityClass'>{$row['prioritas']}</span></td>";

        echo "<td><span class='badge bg-$statusClass'>{$row['status']}</span></td>";

   echo "<td>
        <a href='inbox.php?id=".urlencode($row['id'])."' class='btn btn-sm btn-outline-primary'>
            <i class='bi bi-eye'></i>
        </a>
      </td>";
    }

}else{

    echo "<tr>";
    echo "<td colspan='9' class='text-center'>Tidak ada data</td>";
    echo "</tr>";

}

?>

</tbody>

                    </table>

                </div>

                <!-- Pagination -->

                <div class="d-flex justify-content-between align-items-center mt-3">

                    <small class="text-muted">

                       <?php
$from = ($totalData > 0) ? $start + 1 : 0;
$to = ($totalData > 0) ? min($start + $limit, $totalData) : 0;
?>

<small class="text-muted">
Menampilkan <?= $from ?> - <?= $to ?> dari <?= $totalData ?> Task
</small>

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
                               <td>: <?= $detail['id'] ?? '-' ?></td>
                            </tr>

                            <tr>
                                <th>Tipe</th>
                             <td>: <?= $detail['tipe'] ?? '-' ?></td>
                            </tr>

                            <tr>
                                <th>Customer</th>
                                <td>: <?= $detail['customer'] ?? '-' ?></td>
                            </tr>

                            <tr>
                                <th>Area</th>
                                <td>: <?= $detail['area'] ?? '-' ?></td>
                            </tr>

                            <tr>
                                <th>Prioritas</th>
                              <td>: <?= $detail['prioritas'] ?? '-' ?></td>
                            </tr>

                            <tr>
                                <th>Status</th>
                             <td>: <?= $detail['status'] ?? '-' ?></td>
                            </tr>

                            <tr>
                                <th>SLA</th>
                                <td>: <?= $detail['sla'] ?? '-' ?></td>
                            </tr>

                            <tr>
                                <th>Sisa Waktu</th>
                                <td>: <?= $detail['sisa_waktu'] ?? '-' ?></td>
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
            fetch('sidebar.html')
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