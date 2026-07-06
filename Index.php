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
        <div id="sidebar-container"></div>
 
        <div class="content flex-grow-1">
 
            <!-- Navbar -->
            <nav class="navbar bg-white shadow-sm px-4 py-3">
                <span class="navbar-brand fw-bold fs-4">Summary</span>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <i class="bi bi-bell fs-5"></i>
                    <img src="https://i.pravatar.cc/40" class="rounded-circle" width="38" height="38">
                    <div>
                        <div class="fw-semibold small"><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></div>
                        <small class="text-muted"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></small>
                    </div>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin logout?')">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </nav>
 
            <div class="container-fluid p-4">
 
                <h3>
                    Selamat pagi, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?><br>
                    <small><?= htmlspecialchars($_SESSION['role'] ?? '') ?></small>
                </h3>
 
                <!-- Summary Cards -->
                <div class="row g-3 mt-3">
                    <?php
                    $api = "https://script.google.com/macros/s/AKfycbynNmRaZaE60vlYXShe8LURR2ipoC-LXs-IrCHpg7bBnfiyPu97Xz0GU5wBZLoEBRxKcg/exec";
                    $json = @file_get_contents($api);
                    $response = json_decode($json, true) ?? [];

                    $total  = $response["total"] ?? 0;
                    $open   = $response["open"] ?? 0;
                    $issue  = $response["progress"] ?? 0;
                    $closed = $response["closed"] ?? 0;
                    ?>
                    
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Total Task</h6>
                                <h2 class="text-primary"><?= $total ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Open Task</h6>
                                <h2 class="text-danger"><?= $open ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Issue</h6>
                                <h2 class="text-warning"><?= $issue ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Closed</h6>
                                <h2 class="text-success"><?= $closed ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Priority Alert -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header fw-bold">Priority Alert</div>
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
                                <tbody id="priorityTableBody"></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted" id="priorityInfo">Menampilkan 1 - 10 dari 0 Data</small>
                            <nav>
                                <ul class="pagination mb-0" id="priorityPagination"></ul>
                            </nav>
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
                                foreach($recent as $i => $task){ ?>
                                <tr>
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($task['customer'] ?? '') ?></td>
                                    <td class="text-end">
                                        <span class="badge bg-info"><?= htmlspecialchars($task['status'] ?? '') ?></span>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>

    <script>
    let allTasks = [];

    async function loadAllData() {
        const tbody = document.getElementById('priorityTableBody');
        tbody.innerHTML = `<tr><td colspan="5" class="text-center">Sedang memuat data...</td></tr>`;

        try {
            const res = await fetch("https://script.google.com/macros/s/AKfycbwd0KS3yqXh152ifNHNYpNLLjDqrQDyS30Yta5LkrEUkJwuNENbpFHKA0M-9NKJjbqzwQ/exec");
            
            if (!res.ok) throw new Error("Gagal koneksi");

            const data = await res.json();
            
            console.log("Data diterima:", data); // Debug di console

            if (data.tasks && data.tasks.length > 0) {
                allTasks = data.tasks;
                renderPriorityTable(1);
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada data task</td></tr>`;
            }
        } catch (e) {
            console.error("Error:", e);
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data Priority Alert</td></tr>`;
        }
    }

    function renderPriorityTable(page) {
        const limit = 10;
        const start = (page - 1) * limit;
        const paginated = allTasks.slice(start, start + limit);
        const tbody = document.getElementById('priorityTableBody');
        
        tbody.innerHTML = '';

        if (paginated.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>`;
            return;
        }

        paginated.forEach(task => {
            const badge = getStatusClass(task.status);
            tbody.innerHTML += `
                <tr>
                    <td>${task.id || '-'}</td>
                    <td>${task.prioritas || '-'}</td>
                    <td>${task.customer || '-'}</td>
                    <td>${task.area || '-'}</td>
                    <td><span class="badge bg-${badge}">${task.status || '-'}</span></td>
                </tr>`;
        });

        document.getElementById('priorityInfo').textContent = 
            `Menampilkan ${start+1} - ${Math.min(start+limit, allTasks.length)} dari ${allTasks.length} Data`;

        renderPagination(page);
    }

    function getStatusClass(s) {
        if (!s) return "secondary";
        const st = s.toLowerCase();
        if (st.includes("open")) return "danger";
        if (st.includes("progress") || st.includes("issue")) return "warning";
        if (st.includes("closed")) return "success";
        return "primary";
    }

    function renderPagination(current) {
        const totalPage = Math.ceil(allTasks.length / 10);
        let html = '';

        html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${current-1});return false">Previous</a></li>`;

        for (let i = Math.max(1, current-2); i <= Math.min(totalPage, current+2); i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i});return false">${i}</a></li>`;
        }

        html += `<li class="page-item ${current >= totalPage ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${current+1});return false">Next</a></li>`;

        document.getElementById('priorityPagination').innerHTML = html;
    }

    function changePage(page) {
        if (page < 1 || page > Math.ceil(allTasks.length / 10)) return;
        renderPriorityTable(page);
    }

    // Jalankan
    window.onload = loadAllData;

    // Sidebar
    fetch('sidebar.html')
        .then(res => res.text())
        .then(html => {
            document.getElementById('sidebar-container').innerHTML = html;
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                if (link.href === window.location.href) link.classList.add('active');
            });
        });
    </script>
</body>
</html>