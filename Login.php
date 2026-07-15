<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $url = "https://script.google.com/macros/s/AKfycbzB026p6CF6Eitn3HGrsRGh9sEa3ph8jv0yq6Ei8eiPS1oBT96ZcDMPzAQbV_nH8fm-FA/exec";

    $postData = http_build_query([
        "username" => $username,
        "password" => $password
    ]);

    // Menggunakan cURL (lebih andal daripada file_get_contents untuk POST
    // ke Google Apps Script, terutama terkait penanganan redirect & SSL
    // yang sering gagal diam-diam di beberapa hosting).
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        // Request ke Apps Script gagal total (jaringan/SSL/timeout)
        error_log("Login GAS request error: " . $curlError);
        $error = "Tidak dapat menghubungi server autentikasi. Coba lagi.";
    } else {
        $result = json_decode($response, true);

        if (!empty($result['success'])) {
            $_SESSION['username'] = $result['username'];
            $_SESSION['nama']     = $result['nama'];
            $_SESSION['role']     = $result['role'] ?? 'user';
            $_SESSION['loker']    = $result['loker'] ?? '';

            header("Location: index.php");
            exit;
        }

        // Log respons mentah bila format tidak sesuai dugaan (misal HTML
        // halaman error Google, bukan JSON) agar mudah didiagnosis.
        if ($result === null) {
            error_log("Login GAS returned non-JSON (HTTP $httpCode): " . substr($response, 0, 500));
        }

        $error = "Username atau Password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NETOPS Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { height:100vh; overflow:hidden; font-family:'Segoe UI',sans-serif; }
        .wrapper { display:flex; height:100vh; }
        .left-side { width:70%; background:#f8f9fa; display:flex; align-items:center; justify-content:center; }
        .right-side { width:30%; background:#162843; color:white; display:flex; align-items:center; justify-content:center; }
        .login-box { width:85%; max-width:380px; }
        .form-control { border-radius:12px; height:52px; }
        .login-btn { width:100%; height:52px; border-radius:12px; font-size:18px; font-weight:bold; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="left-side">
        <div class="text-center">
            <h1 class="display-3 fw-bold text-primary">NETOPS</h1>
            <p class="lead text-muted">Network Operation Center</p>
        </div>
    </div>

    <div class="right-side">
        <div class="login-box">
            <h2 class="mb-4 text-center">Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username (admin)" required>
                </div>
                <div class="mb-4">
                    <input type="password" name="password" class="form-control" placeholder="Password (admin123)" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary login-btn">
                    LOGIN
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-light">Default: admin / admin123</small>
            </div>
        </div>
    </div>
</div>

</body>
</html>