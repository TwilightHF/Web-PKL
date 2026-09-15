<?php
require_once 'auth.php';

$successMsg = "";
$errorMsg   = "";

// Direktori penyimpanan foto profil (terpisah per user, disimpan di
// server - path relatif ini yang dipakai di navbar semua halaman)
$avatarDir = __DIR__ . '/uploads/avatars/';
if (!is_dir($avatarDir)) {
    @mkdir($avatarDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $nama  = trim($_POST['nama'] ?? '');
    $loker = trim($_POST['loker'] ?? '');

    if ($nama === '') {
        $errorMsg = "Nama tidak boleh kosong.";
    } else {
        $_SESSION['nama']  = $nama;
        $_SESSION['loker'] = $loker;

        // Upload foto profil (opsional - kalau tidak diisi, foto lama tetap dipakai)
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                $errorMsg = "Format foto harus JPG, PNG, atau WEBP.";
            } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                $errorMsg = "Ukuran foto maksimal 2MB.";
            } else {
                $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $_SESSION['username'] ?? 'user');
                $filename = 'avatar_' . $safeUser . '_' . time() . '.' . $ext;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarDir . $filename)) {
                    $_SESSION['avatar'] = 'uploads/avatars/' . $filename;

                    // Simpan juga ke file pemetaan permanen (username -> path foto)
                    // supaya foto tidak hilang saat session baru terbentuk (logout/relog).
                    $avatarMapFile = $avatarDir . 'avatar_map.json';
                    $avatarMap = file_exists($avatarMapFile)
                        ? (json_decode(file_get_contents($avatarMapFile), true) ?: [])
                        : [];
                    $avatarMap[$_SESSION['username']] = $_SESSION['avatar'];
                    file_put_contents($avatarMapFile, json_encode($avatarMap), LOCK_EX);
                } else {
                    $errorMsg = "Gagal mengunggah foto. Coba lagi.";
                }
            }
        }

        if (!$errorMsg) {
            $successMsg = "Profil berhasil diperbarui.";
        }
    }
}

$avatarSrc = !empty($_SESSION['avatar'])
    ? htmlspecialchars($_SESSION['avatar'])
    : "https://i.pravatar.cc/150?u=" . urlencode($_SESSION['username'] ?? 'user');
?>

<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <title>Settings - NETOPS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        .avatar-upload-wrap {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }
        .avatar-upload-wrap img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 .125rem .5rem rgba(0,0,0,.15);
        }
        .avatar-edit-btn {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #0d6efd;
            color: #fff;
            border: 3px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .2s;
        }
        .avatar-edit-btn:hover { background: #0b5ed7; }
        .avatar-edit-btn input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }
        .field-readonly {
            background: #f8f9fa !important;
        }
        .settings-nav-pill {
            border-radius: 10px;
            padding: 10px 14px;
            color: #495057;
            font-weight: 500;
        }
        .settings-nav-pill.active {
            background: rgba(13,110,253,.1);
            color: #0d6efd;
        }
        .settings-nav-pill:hover { background: #f8f9fa; }
        .coming-soon-badge { font-size: .7rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div id="sidebar-container"></div>

        <div class="content flex-grow-1">
            <!-- Navbar (sama seperti halaman lain) -->
            <nav class="navbar bg-white shadow-sm px-4 py-3">
                <span class="navbar-brand fw-bold fs-4">Settings</span>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <i class="bi bi-bell fs-5"></i>
                    <img src="<?= $avatarSrc ?>" class="rounded-circle" width="38" height="38" style="object-fit:cover;">
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
                <h3 class="mb-1">Pengaturan Akun</h3>
                <small class="text-muted d-block mb-4">Kelola informasi profil dan preferensi akun kamu.</small>

                <?php if ($successMsg): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($successMsg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($errorMsg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Sub-navigasi settings -->
                    <div class="col-lg-3">
                        <div class="card shadow-sm">
                            <div class="card-body p-2">
                                <div class="settings-nav-pill active d-flex align-items-center gap-2 mb-1" data-tab="tabProfil">
                                    <i class="bi bi-person-circle"></i> Profil
                                </div>
                                <div class="settings-nav-pill d-flex align-items-center gap-2 mb-1" data-tab="tabKeamanan">
                                    <i class="bi bi-shield-lock"></i> Keamanan
                                </div>
                                <div class="settings-nav-pill d-flex align-items-center gap-2 text-muted" style="cursor:not-allowed;">
                                    <i class="bi bi-bell"></i> Notifikasi
                                    <span class="badge bg-secondary-subtle text-secondary ms-auto coming-soon-badge">Segera hadir</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Profil -->
                    <div class="col-lg-9" id="tabProfil">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header fw-bold">
                                    <i class="bi bi-person-circle me-1"></i> Profil
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center mb-4 mb-md-0">
                                            <div class="avatar-upload-wrap">
                                                <img id="avatarPreview" src="<?= $avatarSrc ?>" alt="Foto profil">
                                                <label class="avatar-edit-btn" title="Ganti foto">
                                                    <i class="bi bi-camera-fill"></i>
                                                    <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/webp">
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-3">JPG, PNG, atau WEBP.<br>Maks 2MB.</small>
                                        </div>

                                        <div class="col-md-9">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Nama Lengkap</label>
                                                    <input type="text" name="nama" class="form-control"
                                                           value="<?= htmlspecialchars($_SESSION['nama'] ?? '') ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Username</label>
                                                    <input type="text" class="form-control field-readonly"
                                                           value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Role</label>
                                                    <input type="text" class="form-control field-readonly"
                                                           value="<?= htmlspecialchars($_SESSION['role'] ?? '') ?>" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Jabatan / Loker</label>
                                                    <input type="text" name="loker" class="form-control"
                                                           value="<?= htmlspecialchars($_SESSION['loker'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-outline-secondary">Batal</button>
                                <button type="submit" name="save_profile" class="btn btn-primary">
                                    <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Keamanan -->
                    <div class="col-lg-9" id="tabKeamanan" style="display:none;">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">
                                <i class="bi bi-shield-lock me-1"></i> Data Akun
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Email</label>
                                        <input type="text" class="form-control field-readonly"
                                               value="<?= htmlspecialchars($_SESSION['email'] ?? '-') ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Username</label>
                                        <input type="text" class="form-control field-readonly"
                                               value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" disabled>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">Email &amp; username hanya bisa diubah oleh admin.</small>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">
                                <i class="bi bi-key me-1"></i> Ganti Password
                            </div>
                            <div class="card-body">
                                <div id="pwdAlert" class="alert d-none" role="alert"></div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Password Lama</label>
                                        <div class="input-group">
                                            <input type="password" id="oldPassword" class="form-control" autocomplete="current-password">
                                            <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="oldPassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Password Baru</label>
                                        <div class="input-group">
                                            <input type="password" id="newPassword" class="form-control" autocomplete="new-password">
                                            <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="newPassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Minimal 6 karakter.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Konfirmasi Password Baru</label>
                                        <div class="input-group">
                                            <input type="password" id="confirmPassword" class="form-control" autocomplete="new-password">
                                            <button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="confirmPassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="btnBatalPwd">Batal</button>
                            <button type="button" class="btn btn-primary" id="btnSimpanPwd">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Preview foto profil sebelum disimpan
    document.getElementById('avatarInput').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2MB.');
            e.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = ev => {
            document.getElementById('avatarPreview').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });

    // ---- Tab switching (Profil / Keamanan) ----
    document.querySelectorAll('.settings-nav-pill[data-tab]').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.settings-nav-pill[data-tab]').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');

            document.querySelectorAll('#tabProfil, #tabKeamanan').forEach(el => el.style.display = 'none');
            document.getElementById(pill.dataset.tab).style.display = '';
        });
    });

    // ---- Toggle tampil/sembunyi password ----
    document.querySelectorAll('.toggle-pwd').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const icon = btn.querySelector('i');
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            icon.className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    });

    function showPwdAlert(message, isError) {
        const box = document.getElementById('pwdAlert');
        box.className = 'alert ' + (isError ? 'alert-danger' : 'alert-success');
        box.textContent = message;
    }

    // ---- Submit ganti password ----
    document.getElementById('btnSimpanPwd').addEventListener('click', async () => {
        const oldPassword = document.getElementById('oldPassword').value.trim();
        const newPassword = document.getElementById('newPassword').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();

        if (!oldPassword || !newPassword || !confirmPassword) {
            showPwdAlert('Semua kolom password wajib diisi.', true);
            return;
        }
        if (newPassword.length < 6) {
            showPwdAlert('Password baru minimal 6 karakter.', true);
            return;
        }
        if (newPassword !== confirmPassword) {
            showPwdAlert('Konfirmasi password baru tidak cocok.', true);
            return;
        }

        const btn = document.getElementById('btnSimpanPwd');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const res = await fetch('api/change_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ old_password: oldPassword, new_password: newPassword })
            });

            const result = await res.json();

            if (result.success) {
                showPwdAlert('Password berhasil diubah.', false);
                document.getElementById('oldPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
            } else {
                showPwdAlert(result.error || 'Gagal mengubah password.', true);
            }
        } catch (err) {
            console.error('Gagal mengubah password:', err);
            showPwdAlert('Tidak dapat menghubungi server. Coba lagi.', true);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    document.getElementById('btnBatalPwd').addEventListener('click', () => {
        document.getElementById('oldPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('pwdAlert').className = 'alert d-none';
    });

    // Load Sidebar (pola sama seperti index.php & report.php)
    fetch('sidebar.html').then(res => res.text()).then(html => {
        document.getElementById('sidebar-container').innerHTML = html;
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            if (link.getAttribute('href') === 'setting.php') {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>