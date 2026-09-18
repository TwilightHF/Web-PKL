<?php
// api/change_password.php
// Proxy antara browser (setting.php > tab Keamanan) dan Google Apps Script.
// Username SELALU diambil dari session server, bukan dari input client,
// supaya user tidak bisa ganti password akun orang lain lewat request
// yang dipalsukan.

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

$oldPassword = trim($payload['old_password'] ?? '');
$newPassword = trim($payload['new_password'] ?? '');

if ($oldPassword === '' || $newPassword === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Password lama dan password baru wajib diisi.']);
    exit;
}

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Password baru minimal 6 karakter.']);
    exit;
}

if ($oldPassword === $newPassword) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Password baru tidak boleh sama dengan password lama.']);
    exit;
}

// URL Apps Script yang SAMA dengan yang dipakai login.php - karena
// doChangePassword() ditambahkan pada deployment (backup.gs) yang sama.
$url = "https://script.google.com/macros/s/AKfycbw8rgzuIDBB9ZV1XOxPJDLboRZkGwjRWGeTKEOvMwgJiy6-KjUDf3vgj6RGr2rR2-TkyA/exec";

$postData = http_build_query([
    "action"       => "change_password",
    "username"     => $_SESSION['username'],
    "old_password" => $oldPassword,
    "new_password" => $newPassword,
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
    error_log("Change password GAS request error: " . $curlError);
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Tidak dapat menghubungi server autentikasi. Coba lagi.']);
    exit;
}

$result = json_decode($response, true);

if ($result === null) {
    error_log("Change password GAS returned non-JSON (HTTP $httpCode): " . substr($response, 0, 500));
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Respon server tidak valid.']);
    exit;
}

// Kalau berhasil, sekalian sinkronkan session (jaga-jaga ada field lain
// yang dipakai di masa depan). Password sendiri tidak pernah disimpan
// di session.
echo json_encode($result);