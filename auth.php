<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Sinkronkan foto profil dari file pemetaan permanen (uploads/avatars/avatar_map.json).
// Session bisa hilang/reset (logout, ganti device, session expired), tapi foto
// yang sudah pernah diupload harus tetap ada selama akun itu login lagi -
// makanya sumber kebenarannya bukan session, tapi file map ini.
$avatarMapFile = __DIR__ . '/uploads/avatars/avatar_map.json';
if (file_exists($avatarMapFile)) {
    $avatarMap = json_decode(file_get_contents($avatarMapFile), true) ?: [];
    if (!empty($avatarMap[$_SESSION['username']])) {
        $_SESSION['avatar'] = $avatarMap[$_SESSION['username']];
    }
}