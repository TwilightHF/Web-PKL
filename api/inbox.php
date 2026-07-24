<?php
// api/inbox.php
// Proxy antara browser (inbox.php) dan Google Apps Script.
// Menangani dua hal:
//   - GET  : ambil daftar task (role diambil dari session, bukan dari client)
//   - POST : update task (status/catatan/lampiran)
// Tujuan: URL Apps Script tidak pernah terlihat di browser, dan role
// tidak bisa dipalsukan lewat query string oleh user.

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

// URL Apps Script khusus INBOX (berbeda dari deployment dashboard).
const GAS_URL_INBOX = "https://script.google.com/macros/s/AKfycby5vA-fwR-hvQnsdgqenk_GLjjtQ1AgcItWamnRwbv_qmRSJuZaHizAj64RFXZydu6AmA/exec";

$method = $_SERVER['REQUEST_METHOD'];

// ---------------------------------------------------------------
// GET: ambil daftar task sesuai role user yang sedang login
// ---------------------------------------------------------------
if ($method === 'GET') {
    $url = GAS_URL_INBOX . "?role=" . urlencode($role);

    $response = @file_get_contents($url);

    if ($response === false) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Gagal menghubungi Apps Script (inbox).']);
        exit;
    }

    echo $response;
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