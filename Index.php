<?php
require_once 'auth.php';
$role = strtoupper($_SESSION['role'] ?? '');
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <title>Dashboard Fulfillment</title>
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
    const GAS_URL = "https://script.google.com/macros/s/AKfycbyJP0RhndjbMzwWW7rXumBBqsRDLGy4F2Ise630Z9jaSxzqmg_4AoBIBnQ4-w9YW8HUpw/exec";
    const USER_ROLE = <?= json_encode($role) ?>; // MSO / MBB / SS / ED, dikirim dari session PHP

    let priorityDataTable = null;
    let onAirDataTable = null;
    let pieChartInstance = null;
    let barChartInstance = null;

    function statusBadge(status) {
        const s = (status || '').toLowerCase();
        let cls = 'bg-secondary';
        if (s === 'open') cls = 'bg-danger';
        else if (s === 'issue') cls = 'bg-warning text-dark';
        else if (s === 'closed') cls = 'bg-success';
        return `<span class="badge ${cls}">${status || '-'}</span>`;
    }

    async function loadDashboardData() {
        try {
            const url = `${GAS_URL}?role=${encodeURIComponent(USER_ROLE)}`;
            const res = await fetch(url);

            if (!res.ok) {
                throw new Error(`HTTP ${res.status} - cek deploy Apps Script (Who has access: Anyone)`);
            }

            const raw = await res.text();
            let response;
            try {
                response = JSON.parse(raw);
            } catch (parseErr) {
                console.error("Response bukan JSON, isinya:", raw.slice(0, 300));
                throw new Error("Response dari Apps Script bukan JSON. Cek URL deploy / permission-nya.");
            }

            console.log("Data diterima:", response);

            if (!response.success) {
                throw new Error(response.message || "Gagal mengambil data.");
            }

            renderSummaryCards(response);
            renderPriorityTable(response.priority_order || []);
            renderCharts(response.task_by_program || {}, response.task_by_region || {});
            renderOnAirTable(response.on_air || []);

        } catch (err) {
            console.error("Error loadDashboardData:", err);
            const tbody = document.querySelector('#priorityTable tbody');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-3">
                    Gagal memuat data: ${err.message}
                </td></tr>`;
            }
        }
    }

    function renderSummaryCards(response) {
        document.getElementById('totalTask').textContent = response.total ?? 0;
        document.getElementById('openTask').textContent = response.open ?? 0;
        document.getElementById('issueTask').textContent = response.issue ?? 0;
        document.getElementById('confirmTask').textContent = response.confirm ?? 0;
    }

    function renderPriorityTable(rows) {
        if (priorityDataTable) {
            priorityDataTable.destroy();
            document.querySelector('#priorityTable').innerHTML = '<thead></thead><tbody></tbody>';
        }

        priorityDataTable = new DataTable('#priorityTable', {
            data: rows,
            columns: [
                { title: 'No', data: null, render: (d, t, r, meta) => meta.row + 1, orderable: false },
                { title: 'Uniq', data: 'uniq', defaultContent: '-' },
                { title: 'TTD', data: 'ttd', defaultContent: '-', render: d => d === null ? '-' : `${d} hari` },
                { title: 'Site ID', data: 'id', defaultContent: '-' },
                { title: 'Site Name', data: 'site_name', defaultContent: '-' },
                { title: 'Status Deploy', data: 'status_deploy', defaultContent: '-' },
                { title: 'Sow Order', data: 'sow_order', defaultContent: '-' },
                { title: 'Mitra Final', data: 'mitra_final', defaultContent: '-' },
                { title: 'Milestone', data: 'milestone', defaultContent: '-' },
                { title: 'Status Final TIF', data: 'status', defaultContent: '-', render: d => statusBadge(d) }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[2, 'desc']], // urut TTD terbesar dulu
            language: {
                search: "Cari:",
                lengthMenu: "_MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: { previous: "Sebelumnya", next: "Berikutnya" },
                zeroRecords: "Tidak ada data yang cocok"
            }
        });
    }

    function renderCharts(byProgram, byRegion) {
        if (pieChartInstance) pieChartInstance.destroy();
        pieChartInstance = new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(byProgram),
                datasets: [{ data: Object.values(byProgram) }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        if (barChartInstance) barChartInstance.destroy();
        barChartInstance = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(byRegion),
                datasets: [{ label: 'Open Task', data: Object.values(byRegion) }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function renderOnAirTable(rows) {
        if (onAirDataTable) {
            onAirDataTable.destroy();
            document.querySelector('#onAirTable').innerHTML = '<thead></thead><tbody></tbody>';
        }

        onAirDataTable = new DataTable('#onAirTable', {
            data: rows,
            columns: [
                { title: 'Tanggal On Air', data: 'oa_date', defaultContent: '-' },
                { title: 'Site ID', data: 'id', defaultContent: '-' },
                { title: 'NIM', data: 'nim', defaultContent: '-' },
                { title: 'Site Name', data: 'site_name', defaultContent: '-' },
                { title: 'Program', data: 'tipe', defaultContent: '-' }
            ],
            paging: false,
            searching: false,
            info: false,
            order: []
        });
    }

    window.onload = loadDashboardData;

    // Sidebar
    fetch('sidebar.html').then(res => res.text()).then(html => {
        document.getElementById('sidebar-container').innerHTML = html;
    });
    </script>
</body>
</html>