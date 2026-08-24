<?php
// Security Functions

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Add CSRF token to form
function addCSRFToken() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// Validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone number format
function validatePhoneNumber($phone) {
    // Allow various phone number formats
    return preg_match('/^[\d\s\-\+\(\)]{10,20}$/', $phone);
}

// Validate student number format
function validateStudentNumber($student_number) {
    // Adjust pattern based on your institution's format
    return preg_match('/^[A-Z0-9\-]{5,20}$/i', $student_number);
}

// Sanitize filename for uploads
function sanitizeFilename($filename) {
    // Remove dangerous characters
    $filename = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $filename);
    // Remove any path information
    $filename = basename($filename);
    return $filename;
}

// Validate file upload
function validateFileUpload($file, $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'], $max_size = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'File upload error.'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'message' => 'File size exceeds maximum limit.'];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['valid' => false, 'message' => 'Invalid file type.'];
    }
    
    // Check file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['valid' => false, 'message' => 'Invalid file extension.'];
    }
    
    return ['valid' => true, 'message' => 'File is valid.'];
}

// Rate limiting (basic implementation)
function checkRateLimit($identifier, $max_attempts = 5, $time_window = 300) {
    $rate_limit_file = __DIR__ . '/../rate_limit_' . md5($identifier) . '.json';
    
    if (!file_exists($rate_limit_file)) {
        return true;
    }
    
    $data = json_decode(file_get_contents($rate_limit_file), true);
    
    // Clean old entries
    $current_time = time();
    $data = array_filter($data, function($entry) use ($current_time, $time_window) {
        return ($current_time - $entry['timestamp']) < $time_window;
    });
    
    if (count($data) >= $max_attempts) {
        return false;
    }
    
    return true;
}

// Record rate limit attempt
function recordRateLimitAttempt($identifier) {
    $rate_limit_file = __DIR__ . '/../rate_limit_' . md5($identifier) . '.json';
    
    $data = [];
    if (file_exists($rate_limit_file)) {
        $data = json_decode(file_get_contents($rate_limit_file), true);
    }
    
    $data[] = [
        'timestamp' => time(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    file_put_contents($rate_limit_file, json_encode($data));
}

// Security headers
function addSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Log security events
function logSecurityEvent($event_type, $description, $user_id = null) {
    $log_file = __DIR__ . '/../security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = sprintf(
        "[%s] %s | IP: %s | User ID: %s | User Agent: %s | %s\n",
        $timestamp,
        $event_type,
        $ip,
        $user_id ?? 'N/A',
        $user_agent,
        $description
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Validate password strength
function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long.'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter.'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter.'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number.'];
    }
    
    return ['valid' => true, 'message' => 'Password is strong.'];
}

// Escape JSON output
function jsonEscape($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

// Check for SQL injection patterns
function detectSQLInjection($input) {
    $patterns = [
        '/\bUNION\b.*\bSELECT\b/i',
        '/\bOR\b.*\b1\s*=\s*1\b/i',
        '/\bAND\b.*\b1\s*=\s*1\b/i',
        '/\bDROP\b.*\bTABLE\b/i',
        '/\bINSERT\b.*\bINTO\b/i',
        '/\bUPDATE\b.*\bSET\b/i',
        '/\bDELETE\b.*\bFROM\b/i',
        '/--/',
        '/\/\*/',
        '/\*.*\*\//',
        '/\bEXEC\b/i',
        '/\bXP_CMDSHELL\b/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

// Check for XSS patterns
function detectXSS($input) {
    $patterns = [
        '/<script[^>]*>.*<\/script>/i',
        '/<iframe[^>]*>.*<\/iframe>/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<.*?>/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

// Helper function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Secure input validation
function secureInputValidation($input, $type = 'general') {
    // Check for common attack patterns
    if (detectSQLInjection($input)) {
        logSecurityEvent('SQL_INJECTION_ATTEMPT', 'Potential SQL injection detected', $_SESSION['user_id'] ?? null);
        return false;
    }
    
    if (detectXSS($input)) {
        logSecurityEvent('XSS_ATTEMPT', 'Potential XSS attack detected', $_SESSION['user_id'] ?? null);
        return false;
    }
    
    // Type-specific validation
    switch ($type) {
        case 'email':
            return validateEmail($input);
        case 'phone':
            return validatePhoneNumber($input);
        case 'student_number':
            return validateStudentNumber($input);
        case 'password':
            $strength = validatePasswordStrength($input);
            return $strength['valid'];
        default:
            return true;
    }
}
?>
