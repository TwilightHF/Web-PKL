<?php
require_once 'auth.php';
$role = strtoupper($_SESSION['role'] ?? '');
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

            <!-- Navbar -->
            <nav class="navbar bg-white shadow-sm px-4 py-3">
            <div class="container-fluid">
                
                <!-- Brand / Page Title -->
                <span class="navbar-brand fw-bold fs-4 text-dark">
                    Summary
                </span>

                <!-- Right Side -->
                <div class="ms-auto d-flex align-items-center gap-3">

                    <!-- Notification -->
                    <i class="bi bi-bell fs-5 text-muted" style="cursor: pointer;"></i>

                    <!-- Profile -->
                    <div class="d-flex align-items-center gap-2">
                        <img 
                            src="https://i.pravatar.cc/40" 
                            alt="Profile" 
                            class="rounded-circle" 
                            width="38" 
                            height="38">

                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($_SESSION['role']) ?></small>
                        </div>
                    </div>

                    <!-- Logout -->
                    <a href="logout.php" 
                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
                    onclick="return confirm('Yakin ingin logout?')">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </a>

                </div>
            </div>
        </nav>

        <!-- Konten utama -->
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
                   <button
                        type="button"
                        id="btnRefresh"
                        class="btn btn-primary">

                        <i class="bi bi-arrow-clockwise"></i>
                        Refresh

                    </button>
                </div>

            </div>

            <!-- Card Filter -->
            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <form id="filterForm">

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
                                    name="search"
                                        class="form-control"
                                        placeholder="Cari ID Task atau Customer"
                                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

                                   <button 
                                        type="button"
                                        id="btnCari"
                                        class="btn btn-primary">
                                        Cari
                                    </button>

                                </div>

                            </div>

                            <!-- Status -->
                            <div class="col-lg-2">

                                <label class="form-label fw-semibold">
                                    Status
                                </label>

                            <select class="form-select" name="status" id="statusFilter">

                                <option value="">Semua Status</option>
                                <option value="Open">Open</option>
                                <option value="Issue">Issue</option>
                                <option value="Closed">Closed</option>

                            </select>

                            </div>

                            <!-- Tipe -->
                            <div class="col-lg-2">

                                <label class="form-label fw-semibold">
                                    Tipe
                                </label>

                                <select class="form-select" name="tipe" id="tipeFilter">

                                    <option value="">Semua Tipe</option>
                                    <!-- Opsi diisi otomatis lewat JS dari data yang sudah dimuat -->

                                </select>

                            </div>

                            <!-- Prioritas -->
                            <div class="col-lg-2">

                                <label class="form-label fw-semibold">
                                    Prioritas
                                </label>

                                <select class="form-select" name="prioritas" id="prioritasFilter">

                                    <option value="">Semua Prioritas</option>
                                    <!-- Opsi diisi otomatis lewat JS dari data yang sudah dimuat -->

                                </select>

                            </div>

                            <!-- SLA -->
                            <div class="col-lg-1">

                                <label class="form-label fw-semibold">
                                    SLA
                                </label>

                                <select class="form-select" name="sla" id="slaFilter">

                                    <option value="">Semua</option>
                                    <!-- Opsi diisi otomatis lewat JS dari data yang sudah dimuat -->

                                </select>

                            </div>

                            <!-- Tombol Filter -->
                            <div class="col-lg-1 d-grid">

                              <button 
                                type="button"
                                id="btnFilter"
                                class="btn btn-primary">

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
                            <tbody id="taskTableBody">

                            </tbody>
                        </table>

                    </div>

                    <!-- Pagination -->

                    <div class="d-flex justify-content-between align-items-center mt-3">

                        <small class="text-muted">

                          <small id="tableInfo"></small>
                        </small>
                        <nav>
                            <ul class="pagination mb-0" id="pagination"></ul>
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
                                   <td id="detail-id">-</td>
                                </tr>

                                <tr>
                                    <th>Tipe</th>
                                 <td id="detail-tipe">-</td>
                                </tr>

                                <tr>
                                    <th>Customer</th>
                                    <td id="detail-customer">-</td>
                                </tr>

                                <tr>
                                    <th>Area</th>
                                    <td id="detail-area">-</td>
                                </tr>

                                <tr>
                                    <th>Prioritas</th>
                                  <td id="detail-prioritas">-</td>
                                </tr>

                                <tr>
                                    <th>Status</th>
                                 <td id="detail-status">-</td>
                                </tr>

                                <tr>
                                    <th>SLA</th>
                                    <td id="detail-sla">-</td>
                                </tr>

                                <tr>
                                    <th>Sisa Waktu</th>
                                    <td id="detail-sisa-waktu">-</td>
                                </tr>

                                <tr>
                                    <th>Dibuat</th>
                                    <td id="detail-dibuat">-</td>
                                </tr>

                            </table>

                        </div>

                        <!-- Update Task -->
                        <div class="col-lg-6">
                            <input type="hidden" id="selectedTaskId">
                            <h6 class="fw-bold mb-3">
                                Update Task
                            </h6>

                            <div class="mb-3">

                                <label class="form-label">
                                    Update Status
                                </label>

                             <select
                                id="updateStatus"
                                class="form-select">

                                <option value="Open">Open</option>
                                <option value="Issue">Issue</option>
                                <option value="Closed">Closed</option>

                            </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Catatan
                                </label>

                              <textarea
                              id="updateCatatan"
                              class="form-control"
                              rows="5"
                              placeholder="Masukkan catatan..."></textarea>

                            </div>

                            <div class="mb-4">

                                <label class="form-label">
                                    Upload Lampiran
                                </label>

                                <input
                                    type="file"
                                    id="updateLampiran"
                                    class="form-control">

                                <small class="text-muted">Opsional. Maks. sekitar 5-10MB (batasan Apps Script/Drive).</small>

                            </div>

                            <div class="text-end">

                                <button type="button" id="btnBatal" class="btn btn-secondary me-2">

                                    <i class="bi bi-x-circle"></i>

                                    Batal

                                </button>
                                <button
                                type="button"
                                id="btnSimpan"
                                class="btn btn-primary">

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

    <!-- Toast notifikasi -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="appToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="appToastBody">-</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (dibutuhkan untuk Toast) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

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

    // Sekarang menunjuk ke proxy PHP lokal, BUKAN langsung ke Apps Script.
    // URL Apps Script asli disimpan di server (api/inbox.php) dan tidak
    // pernah dikirim ke browser. Role juga tidak dikirim dari client lagi -
    // api/inbox.php mengambilnya sendiri dari $_SESSION di server, sehingga
    // tidak bisa dipalsukan lewat query string.
    const API_URL = "api/inbox.php";

    // Dipakai HANYA untuk namespace key localStorage (supaya cache per role
    // tidak tercampur di browser yang sama). TIDAK dipakai lagi untuk
    // otorisasi/filter data - itu sekarang murni ditentukan server
    // (api/inbox.php) dari $_SESSION.
    const USER_ROLE = "<?= htmlspecialchars($role, ENT_QUOTES) ?>";

    // allTasksRaw = SEMUA data task hasil fetch dari server (tidak difilter).
    // allTasks    = hasil SARINGAN dari allTasksRaw sesuai filter aktif saat ini,
    //               inilah yang dipakai untuk tabel & pagination.
    // Filter/search TIDAK PERNAH fetch ke server lagi setelah data awal dimuat -
    // semuanya cuma menyaring array allTasksRaw yang sudah ada di memory.
    let allTasksRaw = [];
    let allTasks = [];
    let currentPage = 1;
    const rowsPerPage = 10;

    // ---- Helper: cegah XSS saat menampilkan data ke HTML ----
    function escapeHtml(value) {
        if (value === null || value === undefined) return "";
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // ---- Helper: tampilkan notifikasi toast ----
    function showToast(message, isError = false) {
        const toastEl = document.getElementById("appToast");
        const toastBody = document.getElementById("appToastBody");
        toastBody.textContent = message;
        toastEl.classList.remove("bg-success", "bg-danger");
        toastEl.classList.add(isError ? "bg-danger" : "bg-success");
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }

    // ---- Ubah error fetch generik jadi pesan yang lebih jelas untuk user ----
    function explainFetchError(err) {
        if (err instanceof TypeError && /failed to fetch/i.test(err.message)) {
            return "Tidak bisa menghubungi server (kemungkinan deployment Apps Script " +
                   "belum di-redeploy versi terbaru, atau akses belum diset 'Anyone'). " +
                   "Cek tab Console (F12) untuk detail CORS.";
        }
        return err.message;
    }

    // ============================================================
    // CACHE (localStorage) — supaya saat halaman dibuka lagi, data
    // langsung tampil instan tanpa menunggu fetch ke server
    // ============================================================
    const CACHE_KEY = "netops_inbox_task_cache_" + USER_ROLE;

    function saveCache(tasks) {
        try {
            localStorage.setItem(CACHE_KEY, JSON.stringify({
                tasks: tasks,
                savedAt: Date.now()
            }));
        } catch (e) {
            console.warn("Gagal menyimpan cache:", e);
        }
    }

    function readCache() {
        try {
            const raw = localStorage.getItem(CACHE_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            console.warn("Gagal membaca cache:", e);
            return null;
        }
    }

    function clearCache() {
        try {
            localStorage.removeItem(CACHE_KEY);
        } catch (e) { /* noop */ }
    }

    // ============================================================
    // FETCH DATA DARI SERVER
    // Ini SATU-SATUNYA tempat yang boleh manggil fetch(GET) ke Apps
    // Script untuk ambil daftar task. Dipanggil HANYA saat:
    // 1) Halaman pertama kali dibuka
    // 2) Tombol Refresh diklik
    // Filter/search TIDAK memanggil fungsi ini sama sekali.
    // ============================================================
    async function fetchTasksFromServer(options = {}) {
        const { silent = false, useCache = false } = options;

        hideLoadError();

        let shownFromCache = false;
        if (useCache) {
            const cached = readCache();
            if (cached && Array.isArray(cached.tasks)) {
                allTasksRaw = cached.tasks;
                populateFilterOptions(allTasksRaw);
                applyFiltersAndRender();

                const ageMinutes = Math.round((Date.now() - cached.savedAt) / 60000);
                document.getElementById("tableInfo").innerHTML +=
                    ` <span class="text-muted">(data cache, ${ageMinutes < 1 ? 'baru saja' : ageMinutes + ' menit lalu'})</span>`;

                shownFromCache = true;
            }
        }

        if (!shownFromCache && !silent) {
            setTableLoading();
        }

        try {
            // Tidak ada parameter apa pun yang perlu dikirim dari client -
            // role diambil dari session PHP di server (api/inbox.php),
            // kita SELALU ambil semua data (yang sudah difilter server sesuai role),
            // lalu filter tambahan (search/status/tipe/dst) dilakukan di client.
            const res = await fetch(API_URL);

            const rawText = await res.text();

            console.log("Status HTTP:", res.status);
            console.log("Response mentah dari API:", rawText);

            if (!res.ok) {
                throw new Error("Server merespons dengan status " + res.status);
            }

            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseErr) {
                throw new Error(
                    "Response API bukan JSON yang valid. " +
                    "Kemungkinan URL Apps Script salah, atau deployment-nya " +
                    "belum diset 'Who has access: Anyone'. Cek tab Console/Network di browser."
                );
            }

            let tasks;
            if (Array.isArray(data)) {
                tasks = data;
            } else if (Array.isArray(data.tasks)) {
                tasks = data.tasks;
            } else if (data.error) {
                throw new Error("API mengembalikan error: " + data.error);
            } else {
                throw new Error(
                    "Format data tidak dikenali. Field 'tasks' tidak ditemukan di response. " +
                    "Cek Console (F12) untuk lihat response mentahnya."
                );
            }

            allTasksRaw = tasks;

            populateFilterOptions(allTasksRaw);
            applyFiltersAndRender();

            saveCache(tasks);

        } catch (err) {
            console.error("Gagal memuat data task:", err);

            const friendlyMessage = explainFetchError(err);

            if (shownFromCache) {
                showLoadError(
                    "Gagal memperbarui data terbaru dari server (" + friendlyMessage + "). " +
                    "Data yang ditampilkan berasal dari cache sebelumnya."
                );
            } else {
                allTasksRaw = [];
                allTasks = [];
                renderTable();
                renderPagination();
                showLoadError(friendlyMessage);
                showToast("Gagal memuat data task.", true);
            }
        }
    }

    // ============================================================
    // ISI OPSI DROPDOWN Tipe / Prioritas / SLA SECARA OTOMATIS
    // berdasarkan nilai unik yang benar-benar ada di data (bukan
    // hardcode), supaya filter selalu cocok dengan data sebenarnya.
    // ============================================================
    function populateFilterOptions(tasks) {
        fillSelectWithUniqueValues("tipeFilter", tasks, "tipe", "Semua Tipe");
        fillSelectWithUniqueValues("prioritasFilter", tasks, "prioritas", "Semua Prioritas");
        fillSelectWithUniqueValues("slaFilter", tasks, "sla", "Semua");
    }

    function fillSelectWithUniqueValues(selectId, tasks, field, defaultLabel) {
        const select = document.getElementById(selectId);
        if (!select) return;

        const previousValue = select.value; // simpan pilihan sebelumnya kalau ada

        const uniqueValues = Array.from(
            new Set(
                tasks
                    .map(t => (t[field] || "").toString().trim())
                    .filter(v => v !== "")
            )
        ).sort((a, b) => a.localeCompare(b, "id"));

        let html = `<option value="">${escapeHtml(defaultLabel)}</option>`;
        uniqueValues.forEach(v => {
            html += `<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`;
        });

        select.innerHTML = html;

        // Kembalikan pilihan sebelumnya kalau masih ada di daftar opsi baru
        if (previousValue && uniqueValues.includes(previousValue)) {
            select.value = previousValue;
        }
    }

    // ============================================================
    // FILTER + SEARCH — 100% CLIENT-SIDE, TIDAK ADA FETCH KE SERVER
    // ============================================================
    function applyFiltersAndRender() {

        const keyword = document.getElementById("searchInput").value.toLowerCase().trim();
        const status = document.getElementById("statusFilter").value.toLowerCase().trim();
        const tipe = document.getElementById("tipeFilter").value.toLowerCase().trim();
        const prioritas = document.getElementById("prioritasFilter").value.toLowerCase().trim();
        const sla = document.getElementById("slaFilter").value.toLowerCase().trim();

        allTasks = allTasksRaw.filter(task => {

            const idL         = (task.id || "").toLowerCase();
            const tipeL       = (task.tipe || "").toLowerCase();
            const customerL   = (task.customer || "").toLowerCase();
            const areaL       = (task.area || "").toLowerCase();
            const prioritasL  = (task.prioritas || "").toLowerCase();
            const sisaL       = (task.sisa_waktu || "").toLowerCase();
            const slaL        = (task.sla || "").toLowerCase();
            const statusL     = (task.status || "").toLowerCase();

            // SEARCH: cocok kalau salah satu kolom mengandung keyword
            if (keyword) {
                const matchKeyword =
                    idL.includes(keyword) ||
                    tipeL.includes(keyword) ||
                    customerL.includes(keyword) ||
                    areaL.includes(keyword) ||
                    prioritasL.includes(keyword) ||
                    sisaL.includes(keyword) ||
                    slaL.includes(keyword) ||
                    statusL.includes(keyword);

                if (!matchKeyword) return false;
            }

            // FILTER: harus cocok PERSIS kalau filter diisi
            if (status && statusL !== status) return false;
            if (tipe && tipeL !== tipe) return false;
            if (prioritas && prioritasL !== prioritas) return false;
            if (sla && slaL !== sla) return false;

            return true;
        });

        currentPage = 1;
        renderTable();
        renderPagination();
    }

    function setTableLoading() {
        const tbody = document.getElementById("taskTableBody");
        tbody.innerHTML = `<tr><td colspan="9" class="text-muted py-4">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Memuat data...
        </td></tr>`;
    }

    function showLoadError(message) {
        let banner = document.getElementById("loadErrorBanner");
        if (!banner) {
            banner = document.createElement("div");
            banner.id = "loadErrorBanner";
            banner.className = "alert alert-danger mt-3 mb-0";
            document.getElementById("taskTable").closest(".card-body")
                .insertBefore(banner, document.getElementById("taskTable").closest(".table-responsive"));
        }
        banner.textContent = "Gagal memuat data: " + message;
        banner.style.display = "block";
    }

    function hideLoadError() {
        const banner = document.getElementById("loadErrorBanner");
        if (banner) banner.style.display = "none";
    }

    function renderTable() {

        const tbody = document.getElementById("taskTableBody");

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        const pageData = allTasks.slice(start, end);

        if (pageData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-muted py-4">Tidak ada data task.</td></tr>`;
            document.getElementById("tableInfo").innerHTML = `Menampilkan 0 dari ${allTasks.length} data`;
            return;
        }

        let html = "";

        pageData.forEach((task, idx) => {

            const globalIndex = start + idx;

            html += `
            <tr>
                <td>${escapeHtml(task.id)}</td>
                <td>${escapeHtml(task.tipe)}</td>
                <td>${escapeHtml(task.customer)}</td>
                <td>${escapeHtml(task.area)}</td>
                <td>${escapeHtml(task.sla)}</td>
                <td>${escapeHtml(task.sisa_waktu)}</td>
                <td>${escapeHtml(task.prioritas)}</td>
                <td>${escapeHtml(task.status)}</td>
             <td>
             <button 
                class="btn btn-primary btn-sm"
                type="button"
                data-index="${globalIndex}"
                onclick="showDetailByIndex(this)">
                Detail
             </button>
             </td>
            </tr>`;
        });

        tbody.innerHTML = html;

        document.getElementById("tableInfo").innerHTML =
            `Menampilkan ${start + 1}-${Math.min(end, allTasks.length)} dari ${allTasks.length} data`;

    }

    // Ambil task dari allTasks (hasil filter yang sedang tampil) berdasarkan index
    function showDetailByIndex(btn) {
        const index = parseInt(btn.getAttribute("data-index"), 10);
        const task = allTasks[index];
        if (task) showDetail(task);
    }

    function showDetail(task) {

        document.getElementById("detail-id").textContent = task.id ?? "-";
        document.getElementById("detail-tipe").textContent = task.tipe ?? "-";
        document.getElementById("detail-customer").textContent = task.customer ?? "-";
        document.getElementById("detail-area").textContent = task.area ?? "-";
        document.getElementById("detail-prioritas").textContent = task.prioritas ?? "-";
        document.getElementById("detail-status").textContent = task.status ?? "-";
        document.getElementById("detail-sla").textContent = task.sla ?? "-";
        document.getElementById("detail-sisa-waktu").textContent = task.sisa_waktu ?? "-";
        document.getElementById("detail-dibuat").textContent = task.dibuat ?? "-";

        document.getElementById("selectedTaskId").value = task.id ?? "";

        document.getElementById("updateStatus").value = task.status ?? "Open";
        document.getElementById("updateCatatan").value = task.catatan ?? "";

        const lampiran = document.getElementById("updateLampiran");
        if (lampiran) lampiran.value = "";
    }

    // Hitung daftar nomor halaman yang ditampilkan, dengan "..." untuk
    // bagian yang di-skip. Contoh hasil: [1,2,3,'...',12]
    function getPageNumbers(current, total, siblingCount = 1) {

        const totalVisible = siblingCount * 2 + 5;
        if (total <= totalVisible) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }

        const pages = [];

        const leftSibling = Math.max(current - siblingCount, 1);
        const rightSibling = Math.min(current + siblingCount, total);

        const showLeftEllipsis = leftSibling > 2;
        const showRightEllipsis = rightSibling < total - 1;

        pages.push(1);

        if (showLeftEllipsis) {
            pages.push('...');
        } else {
            for (let i = 2; i < leftSibling; i++) pages.push(i);
        }

        for (let i = leftSibling; i <= rightSibling; i++) {
            if (i !== 1 && i !== total) pages.push(i);
        }

        if (showRightEllipsis) {
            pages.push('...');
        } else {
            for (let i = rightSibling + 1; i < total; i++) pages.push(i);
        }

        pages.push(total);

        return pages;
    }

    function renderPagination() {

        const pagination = document.getElementById("pagination");

        pagination.innerHTML = "";

        const totalPage = Math.ceil(allTasks.length / rowsPerPage);

        if (totalPage <= 1) return;

        pagination.innerHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button class="page-link" type="button" onclick="changePage(${currentPage - 1})">
                &laquo;
            </button>
        </li>`;

        const pageNumbers = getPageNumbers(currentPage, totalPage);

        pageNumbers.forEach(p => {

            if (p === '...') {
                pagination.innerHTML += `
                <li class="page-item disabled">
                    <span class="page-link">&hellip;</span>
                </li>`;
            } else {
                pagination.innerHTML += `
                <li class="page-item ${p === currentPage ? 'active' : ''}">
                    <button class="page-link" type="button" onclick="changePage(${p})">
                        ${p}
                    </button>
                </li>`;
            }

        });

        pagination.innerHTML += `
        <li class="page-item ${currentPage === totalPage ? 'disabled' : ''}">
            <button class="page-link" type="button" onclick="changePage(${currentPage + 1})">
                &raquo;
            </button>
        </li>`;

    }

    function changePage(page) {

        currentPage = page;

        renderTable();
        renderPagination();

    }

    // ---- Helper: convert File -> base64 (tanpa prefix "data:...;base64,") ----
    function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result.split(",")[1]);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    // ============================================================
    // SIMPAN UPDATE TASK
    // Setelah sukses, TIDAK fetch ulang ke server. Cukup update objek
    // task yang bersangkutan langsung di allTasksRaw (dan cache),
    // lalu re-render dari data yang sudah ada di memory.
    // ============================================================
    async function saveTaskUpdate() {

        const id = document.getElementById("selectedTaskId").value;

        if (!id) {
            showToast("Pilih task terlebih dahulu sebelum menyimpan.", true);
            return;
        }

        const btnSimpan = document.getElementById("btnSimpan");
        const originalText = btnSimpan.innerHTML;
        btnSimpan.disabled = true;
        btnSimpan.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

        try {

            const newStatus = document.getElementById("updateStatus").value;
            const newCatatan = document.getElementById("updateCatatan").value;

            const payload = {
                action: "update",
                id: id,
                status: newStatus,
                catatan: newCatatan
            };

            const fileInput = document.getElementById("updateLampiran");
            const file = fileInput && fileInput.files[0];

            if (file) {
                const MAX_SIZE = 8 * 1024 * 1024; // 8MB
                if (file.size > MAX_SIZE) {
                    throw new Error("Ukuran file lampiran terlalu besar (maks. 8MB).");
                }

                const base64Data = await fileToBase64(file);
                payload.lampiran = {
                    data: base64Data,
                    mimeType: file.type,
                    fileName: file.name
                };
            }

            const res = await fetch(API_URL, {
                method: "POST",
                headers: { "Content-Type": "text/plain;charset=utf-8" },
                body: JSON.stringify(payload)
            });

            const rawText = await res.text();

            console.log("Status HTTP (update):", res.status);
            console.log("Response mentah (update):", rawText);

            if (!res.ok) {
                throw new Error("Server merespons dengan status " + res.status);
            }

            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseErr) {
                throw new Error(
                    "Response bukan JSON yang valid. Cek Console/Network untuk detail."
                );
            }

            if (!data.success) {
                throw new Error(data.error || "Update ditolak oleh server.");
            }

            // ---- Update data di memory (allTasksRaw), TANPA fetch ulang ----
            const idx = allTasksRaw.findIndex(t => t.id === id);
            if (idx !== -1) {
                allTasksRaw[idx] = {
                    ...allTasksRaw[idx],
                    status: newStatus,
                    catatan: newCatatan
                };
            }

            // Simpan perubahan ke cache juga, supaya konsisten kalau
            // halaman di-reload nanti sebelum sempat Refresh manual.
            saveCache(allTasksRaw);

            // Perbarui dropdown filter (siapa tahu status/tipe baru
            // memunculkan opsi yang belum ada) dan render ulang tabel
            // dengan filter yang SAMA seperti sebelumnya (tanpa reset).
            populateFilterOptions(allTasksRaw);
            applyFiltersAndRender();

            // Perbarui juga panel Detail Task supaya konsisten dengan data baru
            if (idx !== -1) {
                showDetail(allTasksRaw[idx]);
            }

            showToast("Task " + id + " berhasil diperbarui.");

        } catch (err) {
            console.error("Gagal menyimpan update task:", err);
            const friendlyMessage = explainFetchError(err);
            showToast("Gagal menyimpan perubahan task: " + friendlyMessage, true);
        } finally {
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = originalText;
        }
    }

    function resetDetailForm() {
        document.getElementById("detail-id").textContent = "-";
        document.getElementById("detail-tipe").textContent = "-";
        document.getElementById("detail-customer").textContent = "-";
        document.getElementById("detail-area").textContent = "-";
        document.getElementById("detail-prioritas").textContent = "-";
        document.getElementById("detail-status").textContent = "-";
        document.getElementById("detail-sla").textContent = "-";
        document.getElementById("detail-sisa-waktu").textContent = "-";
        document.getElementById("detail-dibuat").textContent = "-";
        document.getElementById("selectedTaskId").value = "";
        document.getElementById("updateStatus").value = "Open";
        document.getElementById("updateCatatan").value = "";
        const lampiran = document.getElementById("updateLampiran");
        if (lampiran) lampiran.value = "";
    }

    // ---- Event Listeners ----

    // Tombol Cari/Filter tetap ada dan berfungsi (klik langsung filter),
    // tapi sekarang TIDAK WAJIB dipencet lagi - lihat auto-filter di bawah.
    document.getElementById("btnCari").addEventListener("click", () => applyFiltersAndRender());
    document.getElementById("btnFilter").addEventListener("click", () => applyFiltersAndRender());

    // ============================================================
    // AUTO-FILTER (live) - langsung filter begitu diketik/diubah,
    // tanpa perlu pencet tombol Filter/Cari. 100% client-side.
    // ============================================================

    // Search: pakai debounce (jeda 300ms setelah berhenti mengetik) supaya
    // tidak filter ulang di setiap huruf yang diketik - lebih ringan kalau
    // datanya banyak, tapi tetap terasa instan.
    let searchDebounceTimer = null;
    document.getElementById("searchInput").addEventListener("input", () => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => applyFiltersAndRender(), 300);
    });

    // Dropdown (Status/Tipe/Prioritas/SLA): langsung filter begitu dipilih,
    // tidak perlu debounce karena "change" cuma terjadi sekali per pilihan.
    ["statusFilter", "tipeFilter", "prioritasFilter", "slaFilter"].forEach(id => {
        document.getElementById(id).addEventListener("change", () => applyFiltersAndRender());
    });

    // Refresh = satu-satunya tombol yang sengaja ambil data terbaru dari server.
    document.getElementById("btnRefresh").addEventListener("click", () => {
        clearCache();
        fetchTasksFromServer();
    });

    document.getElementById("btnSimpan").addEventListener("click", saveTaskUpdate);

    document.getElementById("btnBatal").addEventListener("click", resetDetailForm);

    // Cegah reload halaman kalau form filter di-submit lewat tombol Enter,
    // dan langsung filter di client (tidak fetch ke server).
    document.getElementById("filterForm").addEventListener("submit", function (e) {
        e.preventDefault();
        applyFiltersAndRender();
    });

    // Pertama kali halaman dibuka:
    // 1) Tampilkan data dari cache secara instan (kalau ada)
    // 2) Tetap ambil data terbaru dari server di background untuk menyegarkan cache
    window.onload = function () {
        fetchTasksFromServer({ useCache: true, silent: true });
    };

    </script>
</body>
</html>