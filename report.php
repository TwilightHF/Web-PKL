<?php
require_once 'auth.php';
$role = strtoupper($_SESSION['role'] ?? '');
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <title>Report - NETOPS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        /* ====== Khusus halaman Report ====== */

        .report-summary-card .icon-box {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .report-summary-card h6 {
            color: #6c757d;
            font-size: .82rem;
            margin-bottom: 4px;
        }

        .report-summary-card h3 {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .report-summary-card .trend {
            font-size: .78rem;
        }

        .bg-soft-primary { background: rgba(13,110,253,.12); color: #0d6efd; }
        .bg-soft-danger  { background: rgba(239,68,68,.12);  color: #dc3545; }
        .bg-soft-info    { background: rgba(59,130,246,.12); color: #0dcaf0; }
        .bg-soft-success { background: rgba(34,197,94,.12);  color: #198754; }
        .bg-soft-purple  { background: rgba(139,92,246,.15); color: #8b5cf6; }
        .bg-soft-warning { background: rgba(245,158,11,.15); color: #fd7e14; }

        .chart-box-sm { position: relative; height: 260px; }
        .chart-box-md { position: relative; height: 280px; }

        #doughnutCenterText {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }
        #doughnutCenterText .num { font-size: 1.6rem; font-weight: 700; display: block; line-height: 1; }
        #doughnutCenterText .lbl { font-size: .75rem; color: #6c757d; }

        .legend-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-legend-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: .85rem;
        }

        .top-customer-table td, .top-customer-table th {
            font-size: .88rem;
        }

        #periodPicker {
            cursor: pointer;
            background: white;
        }

        .table-sm-badge {
            font-size: .72rem;
            padding: 5px 10px;
        }
    </style>

    <!-- DataTables (dipakai untuk Task Detail Report, konsisten dgn inbox.php) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css">
</head>
<body>
    <div class="d-flex">
        <div id="sidebar-container"></div>

        <div class="content flex-grow-1">
            <!-- Navbar -->
            <nav class="navbar bg-white shadow-sm px-4 py-3">
                <span class="navbar-brand fw-bold fs-4">Report</span>
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

                <!-- Judul + Filter Bar -->
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                    <div>
                        <h3 class="mb-0 fw-bold">Report</h3>
                        <small class="text-muted">Pantau performa task dan analisis berdasarkan berbagai metrik.</small>
                    </div>

                    <div class="d-flex flex-wrap align-items-end gap-2">
                        <div>
                            <label class="form-label small fw-semibold mb-1 d-block">&nbsp;</label>
                            <div class="input-group" style="min-width:230px;">
                                <span class="input-group-text bg-white"><i class="bi bi-calendar3"></i></span>
                                <input type="date" id="dateFrom" class="form-control">
                                <span class="input-group-text bg-white">-</span>
                                <input type="date" id="dateTo" class="form-control">
                            </div>
                        </div>

                        <div>
                            <label class="form-label small text-muted mb-1">Area</label>
                            <select id="filterArea" class="form-select form-select-sm">
                                <option value="">Semua</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label small text-muted mb-1">SLA</label>
                            <select id="filterSla" class="form-select form-select-sm">
                                <option value="">Semua</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label small text-muted mb-1">Prioritas</label>
                            <select id="filterPrioritas" class="form-select form-select-sm">
                                <option value="">Semua</option>
                            </select>
                        </div>

                        <button id="btnExport" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3">
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card shadow-sm h-100 report-summary-card">
                            <div class="card-body d-flex gap-2 align-items-start">
                                <div class="icon-box bg-soft-primary"><i class="bi bi-clipboard-data"></i></div>
                                <div>
                                    <h6>Total Task</h6>
                                    <h3 id="sumTotal">0</h3>
                                    <div class="trend text-success" id="sumTotalTrend">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card shadow-sm h-100 report-summary-card">
                            <div class="card-body d-flex gap-2 align-items-start">
                                <div class="icon-box bg-soft-danger"><i class="bi bi-inbox"></i></div>
                                <div>
                                    <h6>Open Task</h6>
                                    <h3 id="sumOpen">0</h3>
                                    <div class="trend text-danger" id="sumOpenTrend">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card shadow-sm h-100 report-summary-card">
                            <div class="card-body d-flex gap-2 align-items-start">
                                <div class="icon-box bg-soft-info"><i class="bi bi-arrow-repeat"></i></div>
                                <div>
                                    <h6>On Progress</h6>
                                    <h3 id="sumProgress">0</h3>
                                    <div class="trend text-info" id="sumProgressTrend">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card shadow-sm h-100 report-summary-card">
                            <div class="card-body d-flex gap-2 align-items-start">
                                <div class="icon-box bg-soft-success"><i class="bi bi-check-circle"></i></div>
                                <div>
                                    <h6>Closed Task</h6>
                                    <h3 id="sumClosed">0</h3>
                                    <div class="trend text-success" id="sumClosedTrend">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card shadow-sm h-100 report-summary-card">
                            <div class="card-body d-flex gap-2 align-items-start">
                                <div class="icon-box bg-soft-purple"><i class="bi bi-shield-check"></i></div>
                                <div>
                                    <h6>SLA Compliance</h6>
                                    <h3 id="sumSlaCompliance">0%</h3>
                                    <div class="trend text-success" id="sumSlaTrend">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card shadow-sm h-100 report-summary-card">
                            <div class="card-body d-flex gap-2 align-items-start">
                                <div class="icon-box bg-soft-warning"><i class="bi bi-clock-history"></i></div>
                                <div>
                                    <h6>Rata-rata Waktu Selesai</h6>
                                    <h3 id="sumAvgTime">-</h3>
                                    <div class="trend text-muted" id="sumAvgTrend">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Status Donut / Trend / Priority -->
                <div class="row mt-4 g-3">
                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header fw-bold">Task by Status</div>
                            <div class="card-body">
                                <div class="chart-box-sm">
                                    <canvas id="statusDoughnut"></canvas>
                                    <div id="doughnutCenterText">
                                        <span class="num" id="doughnutTotalNum">0</span>
                                        <span class="lbl">Total</span>
                                    </div>
                                </div>
                                <div id="statusLegend" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Task Trend</span>
                                <select id="trendGrouping" class="form-select form-select-sm" style="width:auto;">
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>
                            </div>
                            <div class="card-body">
                                <div class="chart-box-sm">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header fw-bold">Task by Priority</div>
                            <div class="card-body">
                                <div class="chart-box-sm">
                                    <canvas id="priorityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Area / SLA over time / Top customer -->
                <div class="row mt-3 g-3">
                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header fw-bold">Task by Area</div>
                            <div class="card-body">
                                <div class="chart-box-sm">
                                    <canvas id="areaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header fw-bold">SLA Compliance Over Time</div>
                            <div class="card-body">
                                <div class="chart-box-sm">
                                    <canvas id="slaTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header fw-bold">Task by Customer (Top 5)</div>
                            <div class="card-body">
                                <table class="table top-customer-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th class="text-end">Total Task</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topCustomerBody">
                                        <tr><td colspan="2" class="text-muted text-center">Memuat data...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Detail Report -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="fw-bold">Task Detail Report</span>
                        <div class="d-flex gap-2">
                            <div class="input-group input-group-sm" style="width:260px;">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" id="detailSearch" class="form-control" placeholder="Search Task ID, Customer, Area...">
                            </div>
                            <button id="btnToggleQuickFilter" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </div>

                    <div id="quickFilterRow" class="card-body border-bottom py-2 d-none">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select id="quickStatus" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="quickTipe" class="form-select form-select-sm">
                                    <option value="">Semua Tipe</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button id="btnResetFilter" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle"></i> Reset Semua Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="detailTable" style="width:100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID Task</th>
                                        <th>Tipe</th>
                                        <th>Customer</th>
                                        <th>Area</th>
                                        <th>Prioritas</th>
                                        <th>SLA</th>
                                        <th>Status</th>
                                        <th>Waktu Dibuat</th>
                                        <th>Waktu Selesai</th>
                                        <th>SLA Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="detailTableBody">
                                    <tr><td colspan="11" class="text-center text-muted py-4">Memuat data...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted" id="detailTableInfo"></small>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="detailPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Detail Task -->
    <div class="modal fade" id="taskDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless mb-0">
                        <tr><th width="40%">ID Task</th><td id="mdId">-</td></tr>
                        <tr><th>Tipe</th><td id="mdTipe">-</td></tr>
                        <tr><th>Customer</th><td id="mdCustomer">-</td></tr>
                        <tr><th>Area</th><td id="mdArea">-</td></tr>
                        <tr><th>Prioritas</th><td id="mdPrioritas">-</td></tr>
                        <tr><th>SLA</th><td id="mdSla">-</td></tr>
                        <tr><th>Status</th><td id="mdStatus">-</td></tr>
                        <tr><th>Sisa Waktu</th><td id="mdSisaWaktu">-</td></tr>
                        <tr><th>Waktu Dibuat</th><td id="mdDibuat">-</td></tr>
                        <tr><th>Catatan</th><td id="mdCatatan">-</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="appToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="appToastBody">-</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    // ============================================================
    // Load sidebar & tandai menu aktif
    // ============================================================
    fetch('sidebar.html').then(res => res.text()).then(html => {
        document.getElementById('sidebar-container').innerHTML = html;
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            if (link.getAttribute('href') === 'report.php') {
                link.classList.add('active');
            }
        });
    });

    // ============================================================
    // Sumber data: pakai endpoint proxy yang sudah ada (api/inbox.php),
    // role diambil otomatis dari session PHP di server (bukan dari client).
    // Semua agregasi report (summary card, chart, top customer, dsb)
    // dihitung di sisi client dari task mentah ini.
    // ============================================================
    const API_URL = "api/inbox.php";

    let allTasksRaw = [];   // semua task hasil fetch dari server
    let filteredTasks = []; // hasil setelah filter (periode, area, sla, prioritas, search, quick filter)
    let currentPage = 1;
    const rowsPerPage = 10;

    let doughnutChart = null;
    let trendChart = null;
    let priorityChart = null;
    let areaChart = null;
    let slaTrendChart = null;

    function escapeHtml(v) {
        if (v === null || v === undefined) return "";
        return String(v)
            .replace(/&/g, "&amp;").replace(/</g, "&lt;")
            .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function showToast(message, isError = false) {
        const toastEl = document.getElementById("appToast");
        const toastBody = document.getElementById("appToastBody");
        toastBody.textContent = message;
        toastEl.classList.remove("bg-success", "bg-danger");
        toastEl.classList.add(isError ? "bg-danger" : "bg-success");
        new bootstrap.Toast(toastEl, { delay: 3000 }).show();
    }

    // ---- Normalisasi status mentah -> salah satu dari: Open, On Progress, Waiting, Closed ----
    function normalizeStatus(raw) {
        const s = (raw || "").toString().toLowerCase();
        if (s.includes("close")) return "Closed";
        if (s.includes("progress")) return "On Progress";
        if (s.includes("wait")) return "Waiting";
        if (s.includes("issue")) return "Issue";
        if (s === "") return "Open";
        return "Open";
    }

    // ---- Parse tanggal dari field 'dibuat' (format bervariasi tergantung sheet) ----
    function parseTaskDate(value) {
        if (!value) return null;
        const d = new Date(value);
        return isNaN(d.getTime()) ? null : d;
    }

    function formatDateID(d) {
        if (!d) return "-";
        return d.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });
    }

    // ============================================================
    // FETCH DATA
    // ============================================================
    async function fetchData() {
        try {
            const res = await fetch(API_URL);
            const raw = await res.text();
            let data;
            try {
                data = JSON.parse(raw);
            } catch (e) {
                throw new Error("Response API bukan JSON yang valid.");
            }

            if (!data.success) {
                throw new Error(data.error || "Gagal memuat data.");
            }

            allTasksRaw = Array.isArray(data.tasks) ? data.tasks : [];

            populateFilterOptions();
            applyFilters();

        } catch (err) {
            console.error("Gagal memuat data report:", err);
            showToast("Gagal memuat data report: " + err.message, true);
            document.getElementById("detailTableBody").innerHTML =
                `<tr><td colspan="11" class="text-center text-danger py-4">Gagal memuat data.</td></tr>`;
        }
    }

    function fillSelect(id, values, defaultLabel) {
        const select = document.getElementById(id);
        const prev = select.value;
        let html = `<option value="">${escapeHtml(defaultLabel)}</option>`;
        values.forEach(v => html += `<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`);
        select.innerHTML = html;
        if (prev && values.includes(prev)) select.value = prev;
    }

    function uniqueValues(field) {
        return Array.from(new Set(
            allTasksRaw.map(t => (t[field] || "").toString().trim()).filter(v => v !== "")
        )).sort((a, b) => a.localeCompare(b, "id"));
    }

    function populateFilterOptions() {
        fillSelect("filterArea", uniqueValues("area"), "Semua");
        fillSelect("filterSla", uniqueValues("sla"), "Semua");
        fillSelect("filterPrioritas", uniqueValues("prioritas"), "Semua");
        fillSelect("quickTipe", uniqueValues("tipe"), "Semua Tipe");

        const statuses = Array.from(new Set(allTasksRaw.map(t => normalizeStatus(t.status))));
        fillSelect("quickStatus", statuses, "Semua Status");
    }

    // ============================================================
    // FILTERING (100% client-side)
    // ============================================================
    function applyFilters() {
        const dateFrom = document.getElementById("dateFrom").value ? new Date(document.getElementById("dateFrom").value) : null;
        const dateTo = document.getElementById("dateTo").value ? new Date(document.getElementById("dateTo").value) : null;
        const area = document.getElementById("filterArea").value.toLowerCase();
        const sla = document.getElementById("filterSla").value.toLowerCase();
        const prioritas = document.getElementById("filterPrioritas").value.toLowerCase();
        const search = document.getElementById("detailSearch").value.toLowerCase().trim();
        const quickStatus = document.getElementById("quickStatus").value.toLowerCase();
        const quickTipe = document.getElementById("quickTipe").value.toLowerCase();

        filteredTasks = allTasksRaw.filter(t => {
            const d = parseTaskDate(t.dibuat);
            if (dateFrom && d && d < dateFrom) return false;
            if (dateTo && d && d > new Date(dateTo.getTime() + 86399999)) return false;

            if (area && (t.area || "").toLowerCase() !== area) return false;
            if (sla && (t.sla || "").toLowerCase() !== sla) return false;
            if (prioritas && (t.prioritas || "").toLowerCase() !== prioritas) return false;
            if (quickStatus && normalizeStatus(t.status).toLowerCase() !== quickStatus) return false;
            if (quickTipe && (t.tipe || "").toLowerCase() !== quickTipe) return false;

            if (search) {
                const hay = [t.id, t.tipe, t.customer, t.area, t.prioritas, t.status]
                    .map(v => (v || "").toString().toLowerCase()).join(" ");
                if (!hay.includes(search)) return false;
            }
            return true;
        });

        currentPage = 1;
        renderAll();
    }

    function renderAll() {
        renderSummaryCards();
        renderStatusDoughnut();
        renderTrendChart();
        renderPriorityChart();
        renderAreaChart();
        renderSlaTrendChart();
        renderTopCustomers();
        renderDetailTable();
        renderDetailPagination();
    }

    // ============================================================
    // SUMMARY CARDS
    // ============================================================
    function renderSummaryCards() {
        const total = filteredTasks.length;
        let open = 0, progress = 0, closed = 0, onTrack = 0, slaKnown = 0;
        let sisaSum = 0, sisaCount = 0;

        filteredTasks.forEach(t => {
            const st = normalizeStatus(t.status);
            if (st === "Open" || st === "Issue" || st === "Waiting") open++;
            if (st === "On Progress") progress++;
            if (st === "Closed") closed++;

            const slaVal = (t.sla || "").toLowerCase();
            if (slaVal) {
                slaKnown++;
                if (slaVal.includes("on track")) onTrack++;
            }

            const sisa = parseFloat(t.sisa_waktu);
            if (!isNaN(sisa)) { sisaSum += sisa; sisaCount++; }
        });

        document.getElementById("sumTotal").textContent = total;
        document.getElementById("sumOpen").textContent = open;
        document.getElementById("sumProgress").textContent = progress;
        document.getElementById("sumClosed").textContent = closed;

        const compliance = slaKnown > 0 ? Math.round((onTrack / slaKnown) * 1000) / 10 : 0;
        document.getElementById("sumSlaCompliance").textContent = compliance + "%";

        const avgDays = sisaCount > 0 ? Math.round((sisaSum / sisaCount) * 10) / 10 : null;
        document.getElementById("sumAvgTime").textContent = avgDays !== null ? avgDays + " Hari" : "-";
    }

    // ============================================================
    // CHART: Task by Status (doughnut)
    // ============================================================
    function renderStatusDoughnut() {
        const counts = { Open: 0, "On Progress": 0, Waiting: 0, Closed: 0, Issue: 0 };
        filteredTasks.forEach(t => {
            const st = normalizeStatus(t.status);
            counts[st] = (counts[st] || 0) + 1;
        });

        const labels = Object.keys(counts).filter(k => counts[k] > 0);
        const data = labels.map(l => counts[l]);
        const colorMap = { Open: "#ef4444", "On Progress": "#3b82f6", Waiting: "#f59e0b", Closed: "#22c55e", Issue: "#a855f7" };
        const colors = labels.map(l => colorMap[l] || "#6c757d");

        if (doughnutChart) doughnutChart.destroy();
        doughnutChart = new Chart(document.getElementById("statusDoughnut"), {
            type: "doughnut",
            data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: { legend: { display: false } }
            }
        });

        const total = data.reduce((a, b) => a + b, 0);
        document.getElementById("doughnutTotalNum").textContent = total;

        let legendHtml = "";
        const totalSafe = total || 1;
        labels.forEach((l, i) => {
            const pct = Math.round((data[i] / totalSafe) * 1000) / 10;
            legendHtml += `
                <div class="status-legend-row">
                    <span><span class="legend-dot" style="background:${colors[i]}"></span>${escapeHtml(l)}</span>
                    <span class="text-muted">${data[i]} (${pct}%)</span>
                </div>`;
        });
        document.getElementById("statusLegend").innerHTML = legendHtml || `<div class="text-muted small text-center">Tidak ada data</div>`;
    }

    // ============================================================
    // CHART: Task Trend (per hari/minggu/bulan berdasarkan tanggal dibuat)
    // ============================================================
    function groupKeyForDate(d, grouping) {
        if (!d) return "Tidak diketahui";
        if (grouping === "monthly") {
            return d.toLocaleDateString("id-ID", { month: "short", year: "numeric" });
        }
        if (grouping === "weekly") {
            const onejan = new Date(d.getFullYear(), 0, 1);
            const week = Math.ceil((((d - onejan) / 86400000) + onejan.getDay() + 1) / 7);
            return "W" + week + " " + d.getFullYear();
        }
        return d.toLocaleDateString("id-ID", { day: "2-digit", month: "short" });
    }

    function renderTrendChart() {
        const grouping = document.getElementById("trendGrouping").value;
        const buckets = {}; // key -> {Open, 'On Progress', Closed}

        filteredTasks.forEach(t => {
            const d = parseTaskDate(t.dibuat);
            const key = groupKeyForDate(d, grouping);
            if (!buckets[key]) buckets[key] = { Open: 0, "On Progress": 0, Closed: 0, _sortDate: d ? d.getTime() : 0 };
            const st = normalizeStatus(t.status);
            if (st === "Open" || st === "Issue" || st === "Waiting") buckets[key].Open++;
            else if (st === "On Progress") buckets[key]["On Progress"]++;
            else if (st === "Closed") buckets[key].Closed++;
        });

        const keys = Object.keys(buckets).sort((a, b) => buckets[a]._sortDate - buckets[b]._sortDate);

        if (trendChart) trendChart.destroy();
        trendChart = new Chart(document.getElementById("trendChart"), {
            type: "line",
            data: {
                labels: keys,
                datasets: [
                    { label: "Open", data: keys.map(k => buckets[k].Open), borderColor: "#ef4444", backgroundColor: "rgba(239,68,68,.1)", tension: .3 },
                    { label: "On Progress", data: keys.map(k => buckets[k]["On Progress"]), borderColor: "#3b82f6", backgroundColor: "rgba(59,130,246,.1)", tension: .3 },
                    { label: "Closed", data: keys.map(k => buckets[k].Closed), borderColor: "#22c55e", backgroundColor: "rgba(34,197,94,.1)", tension: .3 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: "top", labels: { boxWidth: 10 } } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // ============================================================
    // CHART: Task by Priority (horizontal bar)
    // ============================================================
    function renderPriorityChart() {
        const counts = {};
        filteredTasks.forEach(t => {
            const p = (t.prioritas || "Tidak diketahui").toString();
            counts[p] = (counts[p] || 0) + 1;
        });

        const labels = Object.keys(counts);
        const data = labels.map(l => counts[l]);
        const colorMap = { Tinggi: "#ef4444", High: "#ef4444", Medium: "#f59e0b", Normal: "#22c55e", Low: "#22c55e" };
        const colors = labels.map(l => colorMap[l] || "#3b82f6");

        if (priorityChart) priorityChart.destroy();
        priorityChart = new Chart(document.getElementById("priorityChart"), {
            type: "bar",
            data: { labels, datasets: [{ data, backgroundColor: colors }] },
            options: {
                indexAxis: "y",
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // ============================================================
    // CHART: Task by Area (bar)
    // ============================================================
    function renderAreaChart() {
        const counts = {};
        filteredTasks.forEach(t => {
            const a = (t.area || "Tidak diketahui").toString();
            counts[a] = (counts[a] || 0) + 1;
        });

        const labels = Object.keys(counts).sort((a, b) => counts[b] - counts[a]).slice(0, 8);
        const data = labels.map(l => counts[l]);

        if (areaChart) areaChart.destroy();
        areaChart = new Chart(document.getElementById("areaChart"), {
            type: "bar",
            data: { labels, datasets: [{ label: "Task", data, backgroundColor: "#3b82f6" }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // ============================================================
    // CHART: SLA Compliance Over Time
    // ============================================================
    function renderSlaTrendChart() {
        const grouping = document.getElementById("trendGrouping").value;
        const buckets = {}; // key -> {onTrack, known, _sortDate}

        filteredTasks.forEach(t => {
            const d = parseTaskDate(t.dibuat);
            const key = groupKeyForDate(d, grouping);
            if (!buckets[key]) buckets[key] = { onTrack: 0, known: 0, _sortDate: d ? d.getTime() : 0 };
            const slaVal = (t.sla || "").toLowerCase();
            if (slaVal) {
                buckets[key].known++;
                if (slaVal.includes("on track")) buckets[key].onTrack++;
            }
        });

        const keys = Object.keys(buckets).sort((a, b) => buckets[a]._sortDate - buckets[b]._sortDate);
        const data = keys.map(k => buckets[k].known > 0 ? Math.round((buckets[k].onTrack / buckets[k].known) * 1000) / 10 : null);

        if (slaTrendChart) slaTrendChart.destroy();
        slaTrendChart = new Chart(document.getElementById("slaTrendChart"), {
            type: "line",
            data: {
                labels: keys,
                datasets: [{ label: "SLA Compliance (%)", data, borderColor: "#22c55e", backgroundColor: "rgba(34,197,94,.1)", tension: .3, spanGaps: true }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { min: 0, max: 100, ticks: { callback: v => v + "%" } } }
            }
        });
    }

    // ============================================================
    // TABLE: Top 5 Customer
    // ============================================================
    function renderTopCustomers() {
        const counts = {};
        filteredTasks.forEach(t => {
            const c = (t.customer || "Tidak diketahui").toString();
            counts[c] = (counts[c] || 0) + 1;
        });

        const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]).slice(0, 5);

        if (sorted.length === 0) {
            document.getElementById("topCustomerBody").innerHTML =
                `<tr><td colspan="2" class="text-muted text-center">Tidak ada data</td></tr>`;
            return;
        }

        document.getElementById("topCustomerBody").innerHTML = sorted.map(([name, count]) => `
            <tr>
                <td>${escapeHtml(name)}</td>
                <td class="text-end fw-semibold">${count}</td>
            </tr>
        `).join("");
    }

    // ============================================================
    // TABLE: Task Detail Report (dengan pagination client-side)
    // ============================================================
    function slaBadge(slaVal) {
        const v = (slaVal || "").toLowerCase();
        if (v.includes("on track")) return `<span class="badge bg-success table-sm-badge">On Track</span>`;
        if (v.includes("off track") || v.includes("over")) return `<span class="badge bg-danger table-sm-badge">Over SLA</span>`;
        return `<span class="badge bg-secondary table-sm-badge">-</span>`;
    }

    function statusBadge(statusRaw) {
        const st = normalizeStatus(statusRaw);
        const map = { Open: "danger", "On Progress": "primary", Waiting: "warning", Closed: "success", Issue: "dark" };
        return `<span class="badge bg-${map[st] || 'secondary'} table-sm-badge">${escapeHtml(st)}</span>`;
    }

    function renderDetailTable() {
        const start = (currentPage - 1) * rowsPerPage;
        const pageRows = filteredTasks.slice(start, start + rowsPerPage);
        const tbody = document.getElementById("detailTableBody");

        if (pageRows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data yang cocok dengan filter.</td></tr>`;
        } else {
            tbody.innerHTML = pageRows.map(t => `
                <tr>
                    <td>${escapeHtml(t.id)}</td>
                    <td>${escapeHtml(t.tipe)}</td>
                    <td>${escapeHtml(t.customer)}</td>
                    <td>${escapeHtml(t.area)}</td>
                    <td>${escapeHtml(t.prioritas)}</td>
                    <td>${escapeHtml(t.sla)}</td>
                    <td>${statusBadge(t.status)}</td>
                    <td>${escapeHtml(t.dibuat) || "-"}</td>
                    <td>-</td>
                    <td>${slaBadge(t.sla)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-view-detail" data-id="${escapeHtml(t.id)}">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `).join("");
        }

        document.querySelectorAll(".btn-view-detail").forEach(btn => {
            btn.addEventListener("click", () => openDetailModal(btn.dataset.id));
        });

        const total = filteredTasks.length;
        const shownFrom = total === 0 ? 0 : start + 1;
        const shownTo = Math.min(start + rowsPerPage, total);
        document.getElementById("detailTableInfo").textContent =
            `Menampilkan ${shownFrom}-${shownTo} dari ${total} task`;
    }

    function renderDetailPagination() {
        const totalPages = Math.max(1, Math.ceil(filteredTasks.length / rowsPerPage));
        const pag = document.getElementById("detailPagination");
        let html = "";

        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a></li>`;

        const maxButtons = 5;
        let startP = Math.max(1, currentPage - Math.floor(maxButtons / 2));
        let endP = Math.min(totalPages, startP + maxButtons - 1);
        startP = Math.max(1, endP - maxButtons + 1);

        for (let p = startP; p <= endP; p++) {
            html += `<li class="page-item ${p === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
        }

        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a></li>`;

        pag.innerHTML = html;

        pag.querySelectorAll(".page-link").forEach(link => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                const page = parseInt(link.dataset.page, 10);
                if (!isNaN(page) && page >= 1 && page <= totalPages) {
                    currentPage = page;
                    renderDetailTable();
                    renderDetailPagination();
                }
            });
        });
    }

    function openDetailModal(id) {
        const t = filteredTasks.find(x => (x.id || "") === id) || allTasksRaw.find(x => (x.id || "") === id);
        if (!t) return;

        document.getElementById("mdId").textContent = t.id || "-";
        document.getElementById("mdTipe").textContent = t.tipe || "-";
        document.getElementById("mdCustomer").textContent = t.customer || "-";
        document.getElementById("mdArea").textContent = t.area || "-";
        document.getElementById("mdPrioritas").textContent = t.prioritas || "-";
        document.getElementById("mdSla").textContent = t.sla || "-";
        document.getElementById("mdStatus").textContent = normalizeStatus(t.status);
        document.getElementById("mdSisaWaktu").textContent = t.sisa_waktu || "-";
        document.getElementById("mdDibuat").textContent = t.dibuat || "-";
        document.getElementById("mdCatatan").textContent = t.catatan || "-";

        new bootstrap.Modal(document.getElementById("taskDetailModal")).show();
    }

    // ============================================================
    // EXPORT CSV (dari data yang sedang difilter/ditampilkan)
    // ============================================================
    function exportCsv() {
        if (filteredTasks.length === 0) {
            showToast("Tidak ada data untuk diexport.", true);
            return;
        }

        const headers = ["ID Task", "Tipe", "Customer", "Area", "Prioritas", "SLA", "Status", "Waktu Dibuat"];
        const rows = filteredTasks.map(t => [
            t.id, t.tipe, t.customer, t.area, t.prioritas, t.sla, normalizeStatus(t.status), t.dibuat
        ]);

        const csvContent = [headers, ...rows]
            .map(row => row.map(v => `"${(v ?? "").toString().replace(/"/g, '""')}"`).join(","))
            .join("\n");

        const blob = new Blob(["\uFEFF" + csvContent], { type: "text/csv;charset=utf-8;" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "report_netops_" + new Date().toISOString().slice(0, 10) + ".csv";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // ============================================================
    // EVENT LISTENERS
    // ============================================================
    ["dateFrom", "dateTo", "filterArea", "filterSla", "filterPrioritas", "quickStatus", "quickTipe"]
        .forEach(id => document.getElementById(id).addEventListener("change", applyFilters));

    document.getElementById("detailSearch").addEventListener("input", applyFilters);

    document.getElementById("trendGrouping").addEventListener("change", () => {
        renderTrendChart();
        renderSlaTrendChart();
    });

    document.getElementById("btnToggleQuickFilter").addEventListener("click", () => {
        document.getElementById("quickFilterRow").classList.toggle("d-none");
    });

    document.getElementById("btnResetFilter").addEventListener("click", () => {
        document.getElementById("dateFrom").value = "";
        document.getElementById("dateTo").value = "";
        document.getElementById("filterArea").value = "";
        document.getElementById("filterSla").value = "";
        document.getElementById("filterPrioritas").value = "";
        document.getElementById("quickStatus").value = "";
        document.getElementById("quickTipe").value = "";
        document.getElementById("detailSearch").value = "";
        applyFilters();
    });

    document.getElementById("btnExport").addEventListener("click", exportCsv);

    // ============================================================
    // INIT
    // ============================================================
    fetchData();
    </script>
</body>
</html>