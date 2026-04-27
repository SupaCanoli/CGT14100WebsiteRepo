<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$password = trim((string)($_POST['password'] ?? ''));

if ($username === '' || $password === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Both username and password are required.';
    exit;
}

$_SESSION['logged_in_user'] = $username;
$_SESSION['logged_in_at'] = date('c');

$record = [
    'timestamp' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'username' => $username,
    'session_id' => session_id()
];

$encoded = json_encode($record, JSON_UNESCAPED_SLASHES);
if ($encoded !== false) {
    $targetFile = __DIR__ . DIRECTORY_SEPARATOR . 'login_submissions.jsonl';
    @file_put_contents($targetFile, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
}

header('Location: home.html');
exit;
