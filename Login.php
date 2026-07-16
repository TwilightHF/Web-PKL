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

    // STEP 6: validasi input, jangan langsung percaya $_POST ada isinya
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {

        $error = "Username dan password wajib diisi.";

    } else {

        $url = "https://script.google.com/macros/s/AKfycbxAZTqbo-Clir4cemPgYiC4hWYQUumHGMbnxS8OUevw2TyxxiF_t3Qyw5Q56hWa2Eq1uQ/exec";

        // STEP 2: sertakan action=login secara eksplisit, supaya Apps Script
        // tidak perlu menebak jenis request dari format body-nya.
        $postData = http_build_query([
            "action"   => "login",
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

        } elseif ($httpCode !== 200) {
            // STEP 3: request sampai ke server tapi HTTP status bukan 200
            // (mis. 302 karena deployment belum "Anyone can access", 403, 500, dst).
            // Sebelumnya kasus ini lolos begitu saja ke json_decode() dan berakhir
            // sebagai "Username atau Password salah" yang menyesatkan.
            error_log("Login GAS HTTP error ($httpCode): " . substr($response, 0, 500));
            $error = "Server autentikasi sedang bermasalah (HTTP $httpCode). Coba lagi nanti.";

        } else {
            $result = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // STEP 4: null dari json_decode() bisa juga berarti response memang
                // literal string "null", bukan cuma "bukan JSON". json_last_error()
                // memastikan ini benar-benar kegagalan parsing JSON.
                error_log("Login GAS JSON error: " . json_last_error_msg() . " | Response: " . substr($response, 0, 500));
                $error = "Server autentikasi mengembalikan respons tidak valid. Coba lagi.";

            } elseif (!empty($result['success'])) {

                // STEP 5: regenerasi session ID setelah login berhasil,
                // untuk mencegah session fixation.
                session_regenerate_id(true);

                $_SESSION['username'] = $result['username'];
                $_SESSION['nama']     = $result['nama'];
                $_SESSION['role']     = $result['role'] ?? 'user';
                $_SESSION['loker']    = $result['loker'] ?? '';

                header("Location: index.php");
                exit;

            } elseif (!empty($result['error'])) {
                // Tampilkan error asli dari Apps Script (mis. "Sheet tidak ditemukan"),
                // bukan selalu ditimpa jadi "Username atau Password salah".
                $error = $result['error'];
            } else {
                $error = "Username atau Password salah.";
            }
        }
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
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-4">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary login-btn">
                    LOGIN
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>