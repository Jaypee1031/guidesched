<?php
// API Configuration & Helper Utilities
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Ensure error output doesn't corrupt JSON response
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Database configuration
define('API_DB_HOST', 'localhost');
define('API_DB_USER', 'root');
define('API_DB_PASS', '');
define('API_DB_NAME', 'guidesched');

function getApiDBConnection() {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli(API_DB_HOST, API_DB_USER, API_DB_PASS, API_DB_NAME);
        if ($conn->connect_errno) {
            jsonError('Database connection error: ' . $conn->connect_error, 503);
        }
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Throwable $e) {
        jsonError('Database exception: ' . $e->getMessage(), 503);
    }
}

function closeApiDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

function getJsonInput() {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

function jsonSuccess($data = [], $message = 'Success', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError($message = 'An error occurred', $code = 400, $data = null) {
    http_response_code($code);
    $response = [
        'success' => false,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sanitize($input) {
    if (is_string($input)) {
        return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
    }
    return $input;
}
