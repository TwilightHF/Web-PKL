<?php
// api/notifications.php
// Proxy antara browser (bell icon di navbar semua halaman) dan Google
// Apps Script. Role diambil dari session server, sama seperti proxy
// lainnya (dashboard.php, inbox.php, report_data.php) - supaya user
// hanya melihat notifikasi yang memang relevan untuk role-nya.

session_start();

$role = strtoupper($_SESSION['role'] ?? '');

header('Content-Type: application/json');

if (!$role) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// URL Apps Script deployment yang SAMA dengan api/dashboard.php &
// api/report_data.php (satu script backup.gs yang sama).
const GAS_URL_DASHBOARD = "https://script.google.com/macros/s/AKfycbxuXndEYpie-gQJXBet3-hbt0HvntCarFiwEGJ_03O980gUjl5LYiHil9h7Nx6Zf01wVA/exec";

$url = GAS_URL_DASHBOARD . "?notifications=1&role=" . urlencode($role);

$response = @file_get_contents($url);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Gagal menghubungi Apps Script (notifications).']);
    exit;
}

echo $response;