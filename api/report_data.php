<?php
// api/report_data.php
// Proxy antara browser (report.php) dan Google Apps Script.
// Sengaja dibuat TERPISAH dari api/inbox.php supaya tidak mengubah
// perilaku halaman "Inbox Task" yang sudah berjalan - endpoint ini
// khusus memanggil mode `report=1` pada script backup.gs (deployment
// yang SAMA dengan yang dipakai api/dashboard.php), yang mengembalikan
// seluruh task mentah dengan field yang sudah dipetakan 1:1 ke kolom
// asli sheet "All Order" (lihat buildReportTasks() di backup.gs).

// Sengaja TIDAK pakai require_once 'auth.php' (lihat penjelasan yang
// sama di api/dashboard.php & api/inbox.php) - endpoint API harus
// selalu balas JSON, bukan redirect ke halaman HTML.
session_start();

$role = strtoupper($_SESSION['role'] ?? '');

header('Content-Type: application/json');

if (!$role) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// URL Apps Script deployment yang SAMA dengan api/dashboard.php,
// karena buildReportTasks() ditambahkan pada script (backup.gs) yang
// sama dengan yang melayani endpoint Dashboard.
const GAS_URL_DASHBOARD = "https://script.google.com/macros/s/AKfycbxPuLWeJV1uWoYMUeUePLkvAd4k3ZxsRkljo8iw4AUVUV5cn-wd8yhlfg-pVPqyfGxf/exec";

$url = GAS_URL_DASHBOARD . "?report=1&role=" . urlencode($role);

$response = @file_get_contents($url);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Gagal menghubungi Apps Script (report).']);
    exit;
}

// Apps Script sudah mengembalikan JSON, jadi cukup diteruskan apa adanya.
echo $response;