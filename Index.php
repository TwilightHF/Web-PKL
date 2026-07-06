<?php
require_once 'auth.php';
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
    <head>
        <title>Dashboard Fulfillment</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
 
    <body>
        <div class="d-flex">
 
            <!-- Sidebar -->
            <div id="sidebar-container"></div>
 
            <!-- Content -->
            <div class="content flex-grow-1">
 
                <!-- Navbar -->
                <nav class="navbar bg-white shadow-sm px-4">
                    <span class="navbar-brand">Summary</span>
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
 
                <!-- Main Content -->
                <div class="container-fluid p-4">
 
                    <h3>
                        Selamat pagi, <?= htmlspecialchars($_SESSION['nama']) ?><br>
                        <small><?= htmlspecialchars($_SESSION['role']) ?></small>
                    </h3>
 
                   <!-- Summary Cards -->
                   <div class="row g-3 mt-3">

                        <?php
                        $api = "https://script.google.com/macros/s/AKfycbynNmRaZaE60vlYXShe8LURR2ipoC-LXs-IrCHpg7bBnfiyPu97Xz0GU5wBZLoEBRxKcg/exec";

                        $json = @file_get_contents($api);

                        $response = [];
                        $data = [];

                        $total = 0;
                        $open = 0;
                        $issue = 0;
                        $closed = 0;

                        $statusChart = [];
                        $areaChart = [];

                        if ($json !== false) {

                            $response = json_decode($json, true);

                            if (is_array($response) && isset($response["tasks"])) {

                                $data = $response["tasks"];

                                $data = $response["tasks"];

                                $total  = $response["total"]  ?? 0;
                                $open   = $response["open"]   ?? 0;
                                $issue  = $response["progress"] ?? 0;
                                $closed = $response["closed"] ?? 0;

                                $statusChart = $response["task_by_status"] ?? [];
                                $areaChart   = $response["task_by_area"] ?? [];

                                foreach ($data as $task) {
                                    $label = trim($task["status"] ?? "");

                                    if ($label == "") {
                                        $label = "Unknown";
                                    }

                                    if (!isset($statusChart[$label])) {
                                        $statusChart[$label] = 0;
                                    }

                                    $statusChart[$label]++;

                                    $area = trim($task["area"] ?? "");

                                    if ($area == "") {
                                        $area = "Unknown";
                                    }

                                    if (!isset($areaChart[$area])) {
                                        $areaChart[$area] = 0;
                                    }

                                    $areaChart[$area]++;
                                }
                            }
                        }

                        ?>
                        
                        <!-- Total Task -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Total Task</h6>
                                    <h2 class="text-primary"><?= $total ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Open Task -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Open Task</h6>
                                    <h2 class="text-danger"><?= $open ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- On Progress -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Issue</h6>
                                    <h2 class="text-warning"><?= $issue ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Closed -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Closed</h6>
                                    <h2 class="text-success"><?= $closed ?></h2>
                                </div>
                            </div>
                        </div>
 
                    <!-- Priority Alert -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            Priority Alert
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID Task</th>
                                            <th>Tipe</th>
                                            <th>Customer</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                  <tbody>
<?php

// ============================
// Pagination Priority Alert
// ============================

// Jumlah data per halaman
$limit = 10;

// Halaman aktif
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

// Total data
$totalData = count($data);

// Total halaman
$totalPage = ceil($totalData / $limit);

// Data mulai
$start = ($page - 1) * $limit;

// Ambil 10 data
$rows = array_slice($data, $start, $limit);

// Tampilkan data
foreach($rows as $task){

    if (!isset($task['prioritas'])) continue;

    $badge = "secondary";

    switch(strtolower($task['status'])){
        case "open":
            $badge="danger";
            break;

        case "issue":
            $badge="warning";
            break;

        case "closed":
            $badge="success";
            break;
    }

?>
                                    <tr>
                                        <td><?= htmlspecialchars($task['id']) ?></td>
                                        <td><?= htmlspecialchars($task['prioritas']) ?></td>
                                        <td><?= htmlspecialchars($task['customer']) ?></td>
                                        <td><?= htmlspecialchars($task['area']) ?></td>
                                        <td><span class="badge bg-<?= $badge ?>">
                                             <?= htmlspecialchars($task['status']) ?>
                                        </span>
                                    </td>
                                    </tr>

                                  <?php
                                  }
                                  ?>
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-between align-items-center mt-3">

<?php

$from = ($totalData > 0) ? $start + 1 : 0;
$to = ($totalData > 0) ? min($start + $limit, $totalData) : 0;

?>

<small class="text-muted">
Menampilkan <?= $from ?> - <?= $to ?> dari <?= $totalData ?> Data
</small>

<nav>

<ul class="pagination mb-0">

<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= max(1,$page-1) ?>">
        Previous
    </a>
</li>

<?php

$startPage = max(1, $page - 2);
$endPage = min($totalPage, $page + 2);

if($startPage > 1){

    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';

    if($startPage > 2){
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

}

for($i=$startPage;$i<=$endPage;$i++){

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
                    </div>
 
                    <!-- Charts -->
                    <div class="row mt-4">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header fw-bold">Task by Status</div>
                                <div class="card-body">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header fw-bold">Task by Area</div>
                                <div class="card-body">
                                    <canvas id="areaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Recent Activity -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Recent Activity</span>
                            <a href="#" class="text-decoration-none">Lihat semua</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <tbody>
                                    <?php
                                    $recent = $response["recent_activity"] ?? [];
                                    $count = 0;
                                    foreach($recent as $task){
                                    ?>

                                    <tr>
                                        <td><?= $count+1 ?></td>
                                        <td><?= htmlspecialchars($task['customer']) ?></td>
                                        <td class="text-end">
                                        <span class="badge bg-info">
                                            <?= htmlspecialchars($task['status']) ?>
                                        </span>
                                        </td>
                                    </tr>

                                    <?php
                                    $count++;
                                    if($count>=8) break;
                                    }
                                    ?>

                                    </tbody>
                            </table>
                        </div>
                    </div>
 
                </div>
                <!-- End Main Content -->
 
            </div>
            <!-- End Content -->
 
        </div>
        <!-- End d-flex -->
        <header>
            <!-- place navbar here -->
        </header>
        <footer>
            <!-- place footer here -->
        </footer>
        <!-- Bootstrap JavaScript Bundle (includes Popper) -->
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        window.dashboardData = {
            total: <?= $total ?>,
            open: <?= $open ?>,
            issue: <?= $issue ?>,
            closed: <?= $closed ?>,
            task_by_status: <?= json_encode($statusChart) ?>,
            task_by_area: <?= json_encode($areaChart) ?>
        };

        </script>
        <script src="script.js"></script>
        <script>
            fetch('sidebar.html')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('sidebar-container').innerHTML = html;
                    // Auto-highlight link yang aktif
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