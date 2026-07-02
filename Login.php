<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $url = "https://script.google.com/macros/s/AKfycbwYIkZB84QURA54m4IqhmKsRhm5Uq6v4NZykrrRRqAmU2TsvsPhSbE4Ixem1subUyUQqQ/exec";

    $postData = http_build_query([
        "username" => $username,
        "password" => $password
    ]);

    $options = [
        "http" => [
            "header"  => "Content-Type: application/x-www-form-urlencoded",
            "method"  => "POST",
            "content" => $postData,
            "timeout" => 30
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response !== false) {

        $result = json_decode($response, true);

        if (!empty($result['success'])) {

            $_SESSION['username'] = $result['username'];
            $_SESSION['email']    = $result['email'];
            $_SESSION['nama']     = $result['nama'];
            $_SESSION['loker']    = $result['loker'];
            $_SESSION['role']     = $result['role'];

            header("Location: index.php");
            exit;
        }
    }

    $error = "Username atau Password salah.";
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