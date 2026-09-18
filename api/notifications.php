<?php
session_start();

header('Content-Type: application/json');

// Harus login dulu - role diambil dari session, BUKAN dari query string
// client, supaya user tidak bisa curang minta notifikasi role lain.
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$role = $_SESSION['role'] ?? '';

// URL Apps Script yang sama dengan yang dipakai Login.php / dashboard.
// Kalau di project ini sudah ada file konfigurasi bersama untuk GAS_URL,
// pindahkan konstanta ini ke sana supaya tidak duplikat di banyak file.
$gasUrl = "https://script.google.com/macros/s/AKfycbw8rgzuIDBB9ZV1XOxPJDLboRZkGwjRWGeTKEOvMwgJiy6-KjUDf3vgj6RGr2rR2-TkyA/exec";

$url = $gasUrl . "?notifications=1&role=" . urlencode($role);

// Sama seperti Login.php: pakai cURL, bukan file_get_contents, supaya
// lebih andal untuk request ke Google Apps Script.
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response  = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    error_log("Notifications GAS request error: " . $curlError);
    http_response_code(502);
    echo json_encode(["success" => false, "error" => "Tidak dapat menghubungi server notifikasi."]);
    exit;
}

$decoded = json_decode($response, true);

if ($decoded === null) {
    // Bukan JSON valid (misal Google mengembalikan halaman error/login HTML)
    error_log("Notifications GAS returned non-JSON (HTTP $httpCode): " . substr($response, 0, 500));
    http_response_code(502);
    echo json_encode(["success" => false, "error" => "Format respons tidak valid dari server notifikasi."]);
    exit;
}

// Teruskan apa adanya - strukturnya sudah cocok dengan yang diharapkan
// notification.js: { success, notifications: [{ timestamp, type, message, ... }] }
echo $response;