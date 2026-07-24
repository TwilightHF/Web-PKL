<?php
// api/dashboard.php
// Proxy antara browser (index.php) dan Google Apps Script.
// Tujuan: menyembunyikan URL Apps Script dari client, dan memastikan
// "role" yang dipakai untuk filter data BENAR-BENAR berasal dari
// session PHP (server), bukan dari input yang bisa dipalsukan user.

// Sengaja TIDAK pakai require_once 'auth.php', karena auth.php didesain
// untuk halaman HTML (redirect ke login.php kalau belum login). Endpoint
// API ini harus selalu balas JSON, bukan redirect ke halaman HTML -
// makanya cukup session_start() lalu cek $_SESSION sendiri di bawah.
session_start();

$role = strtoupper($_SESSION['role'] ?? '');

header('Content-Type: application/json');

if (!$role) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// URL Apps Script khusus DASHBOARD. Hanya ada di server, tidak pernah
// dikirim ke browser.
const GAS_URL_DASHBOARD = "https://script.google.com/macros/s/AKfycbxuXndEYpie-gQJXBet3-hbt0HvntCarFiwEGJ_03O980gUjl5LYiHil9h7Nx6Zf01wVA/exec";

$url = GAS_URL_DASHBOARD . "?role=" . urlencode($role);

$response = @file_get_contents($url);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Gagal menghubungi Apps Script (dashboard).']);
    exit;
}

// Apps Script sudah mengembalikan JSON, jadi cukup diteruskan apa adanya.
echo $response;