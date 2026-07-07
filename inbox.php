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
                          placeholder="Masukkan catatan...">
                          </textarea>

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

const API_URL = "https://script.google.com/macros/s/AKfycbwd0KS3yqXh152ifNHNYpNLLjDqrQDyS30Yta5LkrEUkJwuNENbpFHKA0M-9NKJjbqzwQ/exec";
let allTasks = [];
let currentPage = 1;
const rowsPerPage = 5;
async function loadTasks() {

    const keyword = document.getElementById("searchInput").value;

    const status = document.getElementById("statusFilter").value;

    const url =
        API_URL +
        "?search=" + encodeURIComponent(keyword) +
        "&status=" + encodeURIComponent(status);

    const res = await fetch(url);

    const data = await res.json();
    
    console.log(data);


  allTasks = data.tasks;
currentPage = 1;

renderTable();
renderPagination();

}

function renderTable(){

    const tbody = document.getElementById("taskTableBody");

    const start = (currentPage-1)*rowsPerPage;
    const end = start + rowsPerPage;

    const pageData = allTasks.slice(start,end);

    let html="";

    pageData.forEach(task=>{

        html += `
        <tr>
            <td>${task.id}</td>
            <td>${task.tipe}</td>
            <td>${task.customer}</td>
            <td>${task.area}</td>
            <td>${task.sla}</td>
            <td>${task.sisa_waktu}</td>
            <td>${task.prioritas}</td>
            <td>${task.status}</td>
         <td>
         
<button 
class="btn btn-primary btn-sm"
onclick='showDetail(${JSON.stringify(task)})'>
Detail
</button>
</td>
        </tr>`;
    });


    tbody.innerHTML = html;


    document.getElementById("tableInfo").innerHTML =
        `Menampilkan ${start+1}-${Math.min(end,allTasks.length)} dari ${allTasks.length} data`;

}  // <-- INI YANG KURANG

function showDetail(task){

    document.getElementById("detail-id").innerHTML = task.id;
    document.getElementById("detail-tipe").innerHTML = task.tipe;
    document.getElementById("detail-customer").innerHTML = task.customer;
    document.getElementById("detail-area").innerHTML = task.area;
    document.getElementById("detail-prioritas").innerHTML = task.prioritas;
    document.getElementById("detail-status").innerHTML = task.status;
    document.getElementById("detail-sla").innerHTML = task.sla;
    document.getElementById("detail-sisa-waktu").innerHTML = task.sisa_waktu;
    document.getElementById("detail-dibuat").innerHTML = task.dibuat ?? "-";


    document.getElementById("selectedTaskId").value = task.id;

}

function renderPagination(){

    const pagination = document.getElementById("pagination");

    pagination.innerHTML="";

    const totalPage = Math.ceil(allTasks.length / rowsPerPage);


    for(let i=1;i<=totalPage;i++){

        pagination.innerHTML += `
        <li class="page-item ${i===currentPage?'active':''}">
            <button class="page-link" onclick="changePage(${i})">
                ${i}
            </button>
        </li>
        `;

    }

}


function changePage(page){

    currentPage = page;

    renderTable();
    renderPagination();

}

// Cari
document.getElementById("btnCari").addEventListener("click", function () {

    loadTasks();

});


// Filter
document.getElementById("btnFilter").addEventListener("click", function () {

    loadTasks();

});


// Pertama kali halaman dibuka
window.onload = function () {

    loadTasks();

}

</script>
    </body>
</html>