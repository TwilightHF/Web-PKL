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
                        <span>Andy Pratama</span>
                    </div>
                </nav>
 
                <!-- Main Content -->
                <div class="container-fluid p-4">
 
                    <h3>Selamat pagi, User</h3>
 
                   <!-- Summary Cards -->
                   <div class="row g-3 mt-3">

                        <?php
                        // Fungsi untuk mengambil data dari Google Apps Script
                        function getDashboardData() {
                            $url = "https://script.google.com/macros/s/AKfycbyXNXBuvTJS3fnnCe-CB0DSdUbQafPGym8y8zeqpFku8WMYg6gbL5it91PwHMVdxvHMKg/exec";
                            $json = @file_get_contents($url);
                            
                            if ($json === false) {
                                return [
                                    'total' => 'Error',
                                    'open' => 'Error',
                                    'progress' => 'Error',
                                    'closed' => 'Error'
                                ];
                            }
                            $data = json_decode($json, true);
                            return [
                                'total'    => $data['total'] ?? 0,
                                'open'     => $data['open'] ?? 0,
                                'progress' => $data['progress'] ?? 0,
                                'closed'   => $data['closed'] ?? 0
                            ];
                        }
                        $data = getDashboardData();
                        ?>
                        
                        <!-- Total Task -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Total Task</h6>
                                    <h2 class="text-primary"><?= $data['total'] ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Open Task -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Open Task</h6>
                                    <h2 class="text-danger"><?= $data['open'] ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- On Progress -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">On Progress</h6>
                                    <h2 class="text-warning"><?= $data['progress'] ?></h2>
                                </div>
                            </div>
                        </div>

                        <!-- Closed -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Closed</h6>
                                    <h2 class="text-success"><?= $data['closed'] ?></h2>
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
                                        <tr>
                                            <td>T-1245</td>
                                            <td>Gangguan</td>
                                            <td>PT ABC</td>
                                            <td>JKT</td>
                                            <td><span class="badge bg-danger">OPEN</span></td>
                                        </tr>
                                        <tr>
                                            <td>T-1248</td>
                                            <td>Instalasi</td>
                                            <td>CV Sukses</td>
                                            <td>BKS</td>
                                            <td><span class="badge bg-success">CLOSED</span></td>
                                        </tr>
                                    </tbody>
                                </table>
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
                                    <tr>
                                        <td width="80">10:12</td>
                                        <td>Task T-1245 assigned to Andi Pratama</td>
                                        <td class="text-end"><span class="badge bg-primary">NEW</span></td>
                                    </tr>
                                    <tr>
                                        <td>09:55</td>
                                        <td>Task T-1239 closed by Andi Pratama</td>
                                        <td class="text-end"><span class="badge bg-success">CLOSED</span></td>
                                    </tr>
                                    <tr>
                                        <td>09:30</td>
                                        <td>New task T-1250 created from BOT Telegram</td>
                                        <td class="text-end"><span class="badge bg-primary">NEW</span></td>
                                    </tr>
                                    <tr>
                                        <td>09:12</td>
                                        <td>Task T-1242 status changed to On Progress</td>
                                        <td class="text-end"><span class="badge bg-info">UPDATE</span></td>
                                    </tr>
                                    <tr>
                                        <td>08:45</td>
                                        <td>Task T-1238 closed by Budi Santoso</td>
                                        <td class="text-end"><span class="badge bg-success">CLOSED</span></td>
                                    </tr>
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
            crossorigin="anonymous"
        ></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="script.js"></script>
        <script>
            fetch('sidebar.php')
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