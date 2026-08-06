<?php
// api/inbox.php
// Proxy antara browser (inbox.php, report.php) dan Google Apps Script.
// Menangani dua hal:
//   - GET  : ambil daftar SEMUA task (role diambil dari session, bukan dari client)
//   - POST : update task (status/catatan/lampiran)
// Tujuan: URL Apps Script tidak pernah terlihat di browser, dan role
// tidak bisa dipalsukan lewat query string oleh user.
//
// CATATAN PENTING:
// Apps Script (backup.gs) yang sama juga dipakai oleh api/dashboard.php.
// doGet() di sana mengembalikan field "tasks" untuk Priority Order
// dashboard (hanya task dengan TTD > 20 hari, field terbatas) DAN field
// "allTasks" untuk daftar lengkap semua task (dipakai inbox & report).
// Proxy ini SENGAJA mengambil "allTasks" lalu mengemasnya ulang sebagai
// "tasks" di response miliknya sendiri, supaya frontend inbox.php &
// report.php (yang mengharapkan field "tasks" = daftar lengkap) tidak
// perlu diubah sama sekali.

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

// URL Apps Script (deployment yang sama juga dipakai api/dashboard.php).
const GAS_URL_INBOX = "https://script.google.com/macros/s/AKfycbz0VkjLBQXk2KCa5ko5v8lkfvbAev7kT58p50NoAgPv6z-wqqQ3j--c2Q_cSTgUr8yntQ/exec";

$method = $_SERVER['REQUEST_METHOD'];

// ---------------------------------------------------------------
// GET: ambil daftar SEMUA task sesuai role user yang sedang login
// ---------------------------------------------------------------
if ($method === 'GET') {
    $url = GAS_URL_INBOX . "?role=" . urlencode($role);

    $response = @file_get_contents($url);

    if ($response === false) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Gagal menghubungi Apps Script (inbox).']);
        exit;
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Response Apps Script tidak valid.']);
        exit;
    }

    if (empty($data['success'])) {
        // Teruskan apa adanya kalau Apps Script sendiri melaporkan error
        echo json_encode($data);
        exit;
    }

    // Ambil "allTasks" (daftar lengkap) dan kemas ulang sebagai "tasks"
    // supaya kontrak data ke frontend inbox.php / report.php tetap sama.
    echo json_encode([
        'success' => true,
        'tasks'   => $data['allTasks'] ?? []
    ]);
    exit;
}

// ---------------------------------------------------------------
// POST: update task (status / catatan / lampiran)
// ---------------------------------------------------------------
if ($method === 'POST') {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload) || ($payload['action'] ?? '') !== 'update') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Payload tidak valid.']);
        exit;
    }

    if (empty($payload['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID task wajib diisi.']);
        exit;
    }

    // Catatan: validasi tambahan bisa ditambahkan di sini, misalnya
    // memastikan role user ini memang berhak mengubah task dengan id
    // tersebut (butuh data tambahan dari Apps Script/Sheet untuk itu).

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: text/plain;charset=utf-8",
            'content' => json_encode($payload),
            'timeout' => 30,
        ]
    ]);

    $response = @file_get_contents(GAS_URL_INBOX, false, $ctx);

    if ($response === false) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Gagal mengirim update ke Apps Script (inbox).']);
        exit;
    }

    echo $response;
    exit;
}

// ---------------------------------------------------------------
// Method lain tidak diizinkan
// ---------------------------------------------------------------
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan.']);