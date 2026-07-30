<?php
// Authentication Functions

// Include security functions (will be loaded by config.php)
if (file_exists(__DIR__ . '/security_functions.php')) {
    require_once __DIR__ . '/security_functions.php';
}

// User login
function loginUser($email, $password) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, user_id, role, name, email, password, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (verifyPassword($password, $user['password'])) {
            // Check if account is active
            if ($user['status'] !== 'active') {
                closeDBConnection($conn);
                return ['success' => false, 'message' => 'Your account is ' . $user['status'] . '. Please contact the administrator.'];
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_id_str'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
            closeDBConnection($conn);
            return ['success' => true, 'role' => $user['role']];
        }
    }
    
    closeDBConnection($conn);
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

// User logout
function logoutUser() {
    session_unset();
    session_destroy();
    redirect('login.php');
}

// Register student
function registerStudent($data) {
    $conn = getDBConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    
    // Check if student number already exists
    $stmt = $conn->prepare("SELECT id FROM student_profiles WHERE student_number = ?");
    $stmt->bind_param("s", $data['student_number']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Student number already registered.'];
    }
    
    // Generate user ID
    $user_id = generateUserId('STU');
    
    // Hash password
    $hashed_password = hashPassword($data['password']);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (user_id, role, name, email, password, status) VALUES (?, 'student', ?, ?, ?, 'active')");
    $stmt->bind_param("ssss", $user_id, $data['name'], $data['email'], $hashed_password);
    
    if ($stmt->execute()) {
        $user_db_id = $conn->insert_id;
        
        // Insert student profile
        $stmt = $conn->prepare("INSERT INTO student_profiles (user_id, student_number, course, year_level, contact_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $user_db_id, $data['student_number'], $data['course'], $data['year_level'], $data['contact_number']);
        
        if ($stmt->execute()) {
            closeDBConnection($conn);
            return ['success' => true, 'message' => 'Registration successful! You can now login.'];
        } else {
            // Rollback user insertion
            $conn->query("DELETE FROM users WHERE id = $user_db_id");
            closeDBConnection($conn);
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    } else {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

// Add counselor (admin only)
function addCounselor($data) {
    $conn = getDBConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    
    // Generate user ID
    $user_id = generateUserId('COUNSELOR');
    
    // Hash password
    $hashed_password = hashPassword($data['password']);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (user_id, role, name, email, password, status) VALUES (?, 'counselor', ?, ?, ?, 'active')");
    $stmt->bind_param("ssss", $user_id, $data['name'], $data['email'], $hashed_password);
    
    if ($stmt->execute()) {
        $user_db_id = $conn->insert_id;
        
        // Insert counselor profile
        $stmt = $conn->prepare("INSERT INTO counselor_profiles (user_id, specialization, contact_number) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_db_id, $data['specialization'], $data['contact_number']);
        
        if ($stmt->execute()) {
            closeDBConnection($conn);
            return ['success' => true, 'message' => 'Counselor added successfully!'];
        } else {
            // Rollback user insertion
            $conn->query("DELETE FROM users WHERE id = $user_db_id");
            closeDBConnection($conn);
            return ['success' => false, 'message' => 'Failed to add counselor. Please try again.'];
        }
    } else {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Failed to add counselor. Please try again.'];
    }
}

// Get user profile by ID
function getUserProfile($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, user_id, role, name, email, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Get additional profile data based on role
        if ($user['role'] === 'student') {
            $stmt = $conn->prepare("SELECT student_number, course, year_level, contact_number, profile_picture FROM student_profiles WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $profile_result = $stmt->get_result();
            if ($profile_result->num_rows === 1) {
                $user = array_merge($user, $profile_result->fetch_assoc());
            }
        } elseif ($user['role'] === 'counselor') {
            $stmt = $conn->prepare("SELECT specialization, contact_number, profile_picture FROM counselor_profiles WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $profile_result = $stmt->get_result();
            if ($profile_result->num_rows === 1) {
                $user = array_merge($user, $profile_result->fetch_assoc());
            }
        }
        
        closeDBConnection($conn);
        return $user;
    }
    
    closeDBConnection($conn);
    return null;
}

// Update user profile
function updateUserProfile($user_id, $data) {
    $conn = getDBConnection();
    
    // Get user role
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'User not found.'];
    }
    
    // Update basic user info
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $data['name'], $data['email'], $user_id);
    
    if ($stmt->execute()) {
        // Update role-specific profile
        if ($user['role'] === 'student') {
            $stmt = $conn->prepare("UPDATE student_profiles SET student_number = ?, course = ?, year_level = ?, contact_number = ? WHERE user_id = ?");
            $stmt->bind_param("sisii", $data['student_number'], $data['course'], $data['year_level'], $data['contact_number'], $user_id);
        } elseif ($user['role'] === 'counselor') {
            $stmt = $conn->prepare("UPDATE counselor_profiles SET specialization = ?, contact_number = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $data['specialization'], $data['contact_number'], $user_id);
        }
        
        if ($stmt->execute()) {
            closeDBConnection($conn);
            return ['success' => true, 'message' => 'Profile updated successfully!'];
        }
    }
    
    closeDBConnection($conn);
    return ['success' => false, 'message' => 'Failed to update profile. Please try again.'];
}

// Change password
function changePassword($user_id, $current_password, $new_password) {
    $conn = getDBConnection();
    
    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'User not found.'];
    }
    
    // Verify current password
    if (!verifyPassword($current_password, $user['password'])) {
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }
    
    // Hash new password
    $hashed_password = hashPassword($new_password);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        closeDBConnection($conn);
        return ['success' => true, 'message' => 'Password changed successfully!'];
    }
    
    closeDBConnection($conn);
    return ['success' => false, 'message' => 'Failed to change password. Please try again.'];
}
?>
