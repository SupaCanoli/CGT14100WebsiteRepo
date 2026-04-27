<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed.'
    ]);
    exit;
}

$fieldNames = ['discord', 'instagram', 'bluesky', 'phone', 'email'];
$clean = [];

foreach ($fieldNames as $name) {
    $value = trim((string)($_POST[$name] ?? ''));
    if ($value === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please fill out all contact fields before continuing.'
        ]);
        exit;
    }
    $clean[$name] = $value;
}

if (!filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

$record = [
    'timestamp' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user' => [
        'username' => $_SESSION['logged_in_user'] ?? 'guest',
        'session_id' => session_id()
    ],
    'contact' => $clean
];

$encoded = json_encode($record, JSON_UNESCAPED_SLASHES);
if ($encoded === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not prepare your submission.'
    ]);
    exit;
}

$targetFile = __DIR__ . DIRECTORY_SEPARATOR . 'contact_submissions.jsonl';
$result = @file_put_contents($targetFile, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);

if ($result === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not save your contact info on the server.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Your contact info has been sent. Thank you for your support!'
]);