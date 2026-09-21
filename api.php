<?php
session_start();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. Aksi Auth (Login / Logout / Check Status)
if ($action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $uid = isset($input['uid']) ? $input['uid'] : '';
    $pass = isset($input['pass']) ? $input['pass'] : '';

    if ($uid === 'laoshi' && $pass === 'nihao') {
        $_SESSION['user'] = 'laoshi';
        echo json_encode(['status' => 'success', 'message' => 'Login berhasil!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'UID atau Password salah!']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Berhasil logout!']);
    exit;
}

if ($action === 'check_auth') {
    $isLoggedIn = isset($_SESSION['user']) && $_SESSION['user'] === 'laoshi';
    echo json_encode(['logged_in' => $isLoggedIn, 'user' => $isLoggedIn ? $_SESSION['user'] : null]);
    exit;
}

// 2. Operasi Database JSON (GET)
if ($method === 'GET') {
    $file = isset($_GET['file']) ? $_GET['file'] : 'kata.json';
    // Mencegah traversal directory
    $file = basename($file);
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode([]);
    }
    exit;
}

// 3. Operasi Database JSON (POST - Perlu Login)
if ($method === 'POST') {
    if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'laoshi') {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak! Anda harus login sebagai laoshi.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $file = isset($input['file']) ? basename($input['file']) : 'kata.json';
    $data = isset($input['data']) ? $input['data'] : [];

    if (file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['status' => 'success', 'message' => 'Database berhasil disimpan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file JSON!']);
    }
    exit;
}
?>