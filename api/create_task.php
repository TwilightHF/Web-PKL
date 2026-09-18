<?php
// api/create_task.php
// Proxy antara browser (inbox.php > modal "Buat Task Baru") dan Google
// Apps Script (doCreateTask, action "create_task").
//
// PENTING: "All Order" HANYA sheet tampilan gabungan (di-protect, formula
// menarik dari sheet sumber). Task baru HARUS ditulis ke salah satu sheet
// sumber asli (NL / RBL / NY NIM) - dipilih user lewat dropdown "Sheet
// Tujuan" di form, dikirim sebagai field target_sheet.
//
// Apps Script membaca field lewat e.parameter, yang HANYA terisi kalau
// body request berupa application/x-www-form-urlencoded (bukan JSON).

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

$ALLOWED_SHEETS = ['NL', 'RBL', 'NY NIM'];

if (!$payload || empty($payload['site_id']) || empty($payload['program'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Site ID dan Program wajib diisi.']);
    exit;
}

if (empty($payload['target_sheet']) || !in_array($payload['target_sheet'], $ALLOWED_SHEETS, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Sheet tujuan wajib dipilih (NL, RBL, atau NY NIM).']);
    exit;
}

// URL Apps Script yang SAMA dengan login.php & change_password.php
// (script gabungan yang punya doLogin, doChangePassword, doCreateTask,
// dan notifikasi sekaligus).
$url = "https://script.google.com/macros/s/AKfycbw8rgzuIDBB9ZV1XOxPJDLboRZkGwjRWGeTKEOvMwgJiy6-KjUDf3vgj6RGr2rR2-TkyA/exec";

$postData = http_build_query([
    "action"         => "create_task",
    "target_sheet"   => $payload['target_sheet'],
    "site_id"        => $payload['site_id'],
    "program"        => $payload['program'],
    "site_name"      => $payload['site_name'] ?? '',
    "nim_order"      => $payload['nim_order'] ?? '',
    "program_chart"  => $payload['program_chart'] ?? '',
    "region"         => $payload['region'] ?? '',
    "responsibility" => $payload['responsibility'] ?? '',
    "status"         => $payload['status'] ?? '',
    "ttd_days"       => $payload['ttd_days'] ?? '0',
    "oa_date"        => $payload['oa_date'] ?? '',
]);

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
    error_log("create_task GAS request error: " . $curlError);
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Tidak dapat menghubungi server. Coba lagi.']);
    exit;
}

$result = json_decode($response, true);

if ($result === null) {
    error_log("create_task GAS returned non-JSON (HTTP $httpCode): " . substr($response, 0, 500));
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Respon server tidak valid.']);
    exit;
}

echo json_encode($result);