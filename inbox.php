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

                    <tbody>

<?php

$url = "https://script.google.com/macros/s/AKfycbyKV0yj4KajDQbtfHRTAFxu2Hbyd_g99kK4sMMCl9S54BpPkWL8SfpleTfqrxRyVdPToA/exec";

$json = @file_get_contents($url);

if ($json === false) {
    die("Gagal mengambil data dari Apps Script.");
}


$data = json_decode($json, true);
if(isset($data['data'])):
    foreach($data['data'] as $row):
        echo "<pre>";
print_r($data);
echo "</pre>";
exit;
?>


<tr>
    <td><?= $row['id_task'] ?></td>
    <td><?= $row['tipe'] ?></td>
    <td><?= $row['customer'] ?></td>
    <td><?= $row['area'] ?></td>
    <td><?= $row['sla'] ?></td>
    <td><?= $row['sisa_waktu'] ?></td>

    <td>
<?php
$badge = "secondary";

if ($row['prioritas']=="High") $badge="danger";
elseif($row['prioritas']=="Medium") $badge="warning text-dark";
elseif($row['prioritas']=="Low") $badge="success";
?>

<span class="badge bg-<?= $badge ?>">
    <?= $row['prioritas'] ?>
</span>
            <?= $row['prioritas'] ?>
        </span>
    </td>

    <td>
      <?php
$status = $row['status'];

$class = "secondary";

if($status=="Open") $class="danger";
elseif($status=="On Progress") $class="primary";
elseif($status=="Waiting") $class="warning text-dark";
elseif($status=="Closed") $class="success";
?>

<span class="badge bg-<?= $class ?>">
    <?= $status ?>
</span>
            <?= $row['status'] ?>
        </span>
    </td>

    <td>
        <button class="btn btn-sm btn-outline-primary">
            <i class="bi bi-eye"></i>
        </button>
    </td>
</tr>

<?php
    endforeach;
endif;
?>

</tbody>

                    </table>

                </div>

                <!-- Pagination -->

                <div class="d-flex justify-content-between align-items-center mt-3">

                    <small class="text-muted">

                        Menampilkan 1 - 5 dari 128 Task

                    </small>

                    <nav>

                        <ul class="pagination mb-0">

                            <li class="page-item disabled">
                                <a class="page-link">
                                    Previous
                                </a>
                            </li>

                            <li class="page-item active">
                                <a class="page-link">
                                    1
                                </a>
                            </li>

                            <li class="page-item">
                                <a class="page-link">
                                    2
                                </a>
                            </li>

                            <li class="page-item">
                                <a class="page-link">
                                    3
                                </a>
                            </li>

                            <li class="page-item">
                                <a class="page-link">
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
                                    :
                                    <span class="text-danger fw-bold">
                                        00:45:10
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