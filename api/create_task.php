<?php
// api/create_task.php
// Proxy antara browser (tombol "+ Buat Task Baru" di inbox.php) dan
// Google Apps Script. Pakai deployment yang SAMA dengan dashboard.php
// & notifications.php, karena doCreateTask() ditambahkan di script
// (backup.gs) yang sama, dan menulis ke spreadsheet yang sama juga
// yang dibaca Dashboard, Report, Inbox, dan sistem Notifikasi.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

$siteId  = trim($payload['site_id'] ?? '');
$program = trim($payload['program'] ?? '');

if ($siteId === '' || $program === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Site ID dan Program wajib diisi.']);
    exit;
}

// URL Apps Script yang SAMA dengan api/dashboard.php & api/notifications.php.
const GAS_URL_DASHBOARD = "https://script.google.com/macros/s/AKfycbxuXndEYpie-gQJXBet3-hbt0HvntCarFiwEGJ_03O980gUjl5LYiHil9h7Nx6Zf01wVA/exec";

$postData = http_build_query([
    "action"         => "create_task",
    "site_id"        => $siteId,
    "site_name"      => trim($payload['site_name'] ?? ''),
    "nim_order"      => trim($payload['nim_order'] ?? ''),
    "program"        => $program,
    "program_chart"  => trim($payload['program_chart'] ?? ''),
    "region"         => trim($payload['region'] ?? ''),
    "responsibility" => trim($payload['responsibility'] ?? ''),
    "status"         => trim($payload['status'] ?? ''),
    "ttd_days"       => trim((string) ($payload['ttd_days'] ?? '0')),
    "oa_date"        => trim($payload['oa_date'] ?? ''),
]);

$ch = curl_init(GAS_URL_DASHBOARD);
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
    error_log("Create task GAS request error: " . $curlError);
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Tidak dapat menghubungi server. Coba lagi.']);
    exit;
}

$result = json_decode($response, true);

if ($result === null) {
    error_log("Create task GAS returned non-JSON (HTTP $httpCode): " . substr($response, 0, 500));
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Respon server tidak valid.']);
    exit;
}

echo json_encode($result);