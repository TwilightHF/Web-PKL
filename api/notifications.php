<?php
// api/notifications.php
// Proxy antara browser (notification.js) dan Google Apps Script,
// mengambil notifikasi yang relevan untuk role user yang sedang login.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$role = strtoupper($_SESSION['role'] ?? '');

// URL Apps Script yang SAMA dengan login.php / create_task.php
// (script gabungan yang punya getNotifications()).
$url = "https://script.google.com/macros/s/AKfycbw8rgzuIDBB9ZV1XOxPJDLboRZkGwjRWGeTKEOvMwgJiy6-KjUDf3vgj6RGr2rR2-TkyA/exec"
     . "?notifications=1&role=" . urlencode($role);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response  = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    error_log("notifications GAS request error: " . $curlError);
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Tidak dapat menghubungi server.']);
    exit;
}

$result = json_decode($response, true);

if ($result === null) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Respon server tidak valid.']);
    exit;
}

echo json_encode($result);