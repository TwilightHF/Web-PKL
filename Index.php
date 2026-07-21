<?php
require_once 'auth.php';
$role = strtoupper($_SESSION['role'] ?? '');
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <title>Dashboard Fulfillment - NETOPS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
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
                <h3>Selamat pagi, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></h3>

                <!-- Summary Cards -->
                <div class="row g-3 mt-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Total Task</h6>
                                <h2 class="text-primary" id="totalTask">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Open Task</h6>
                                <h2 class="text-danger" id="openTask">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Issue</h6>
                                <h2 class="text-warning" id="issueTask">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Confirmation</h6>
                                <h2 class="text-success" id="confirmTask">0</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Priority Order -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header fw-bold">Priority Order &gt; 20 Hari</div>
                    <div class="card-body">
                        <table id="priorityTable" class="table table-hover align-middle" style="width:100%">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mt-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header fw-bold">Task Open by Program</div>
                            <div class="card-body">
                                <canvas id="pieChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header fw-bold">Task Open by Regional</div>
                            <div class="card-body">
                                <canvas id="barChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Site On Air -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header fw-bold">New Site On Air</div>
                    <div class="card-body">
                        <table id="onAirTable" class="table table-hover" style="width:100%">
                            <thead></thead>
                            <tbody></tbody>
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
    const GAS_URL = "https://script.google.com/macros/s/AKfycbxuXndEYpie-gQJXBet3-hbt0HvntCarFiwEGJ_03O980gUjl5LYiHil9h7Nx6Zf01wVA/exec";

    // Role user (dari session PHP) dikirim ke Apps Script sebagai
    // query param, dipakai untuk filter kategori + wilayah data
    const USER_ROLE = "<?= htmlspecialchars($role, ENT_QUOTES) ?>";

    let priorityDataTable = null;
    let onAirDataTable = null;
    let pieChartInstance = null;
    let barChartInstance = null;

    async function loadDashboardData() {
        try {
            const url = GAS_URL + "?role=" + encodeURIComponent(USER_ROLE);
            const res = await fetch(url);
            const response = await res.json();

            if (response.success) {
                renderSummaryCards(response);
                renderPriorityTable(response.tasks || []);
                renderOnAirTable(response.onAir || []);
                renderCharts(response.task_by_program || {}, response.task_by_region || {});
            }
        } catch (err) {
            console.error("Gagal memuat data:", err);
        }
    }

    function renderSummaryCards(r) {
        document.getElementById('totalTask').textContent = r.total ?? 0;
        document.getElementById('openTask').textContent = r.open ?? 0;
        document.getElementById('issueTask').textContent = r.issue ?? 0;
        document.getElementById('confirmTask').textContent = r.confirm ?? 0;
    }

    function renderPriorityTable(rows) {
        if (priorityDataTable) priorityDataTable.destroy();

        priorityDataTable = new DataTable('#priorityTable', {
            data: rows,
            pageLength: 10,
            scrollX: true,
            searching: true,
            ordering: true,
            columns: [
                { title: 'No', data: null, render: (d, t, r, meta) => meta.row + 1 },
                { title: 'ID Task', data: 'id', defaultContent: '-' },
                { title: 'Tipe', data: 'tipe', defaultContent: '-' },
                { title: 'Customer', data: 'customer', defaultContent: '-' },
                { title: 'Area', data: 'area', defaultContent: '-' },
                { title: 'Status', data: 'status', defaultContent: '-' }
            ]
        });
    }

    function renderOnAirTable(rows) {
        if (onAirDataTable) onAirDataTable.destroy();

        onAirDataTable = new DataTable('#onAirTable', {
            data: rows,
            pageLength: 10,
            scrollX: true,
            columns: [
                { title: 'Tanggal On Air', data: 'tanggal' },
                { title: 'Site Name', data: 'siteName' },
                { title: 'NIM', data: 'nim' },
                { title: 'Program', data: 'program' }
            ]
        });
    }

    function renderCharts(byProgram, byRegion) {
        if (pieChartInstance) { pieChartInstance.destroy(); pieChartInstance = null; }
        if (barChartInstance) { barChartInstance.destroy(); barChartInstance = null; }

        // Pie Chart
        pieChartInstance = new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(byProgram),
                datasets: [{
                    data: Object.values(byProgram),
                    backgroundColor: ['#ef4444', '#3b82f6', '#eab308', '#22c55e', '#8b5cf6', '#ec4899']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Bar Charts
        barChartInstance = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(byRegion),
                datasets: [{
                    label: 'Task Open',
                    data: Object.values(byRegion),
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    window.onload = loadDashboardData;

    // Load Sidebar
    fetch('sidebar.html').then(res => res.text()).then(html => {
        document.getElementById('sidebar-container').innerHTML = html;
    });
    </script>
</body>
</html>