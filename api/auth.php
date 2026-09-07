<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();
$action = $input['action'] ?? ($_GET['action'] ?? 'login');

$conn = getApiDBConnection();

if ($method === 'POST') {
    if ($action === 'login') {
        $email = sanitize($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            jsonError('Email and password are required', 422);
        }

        $stmt = $conn->prepare("SELECT id, user_id, role, name, email, password, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows !== 1) {
            jsonError('Invalid email or password', 401);
        }

        $user = $res->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            jsonError('Invalid email or password', 401);
        }

        if ($user['status'] !== 'active') {
            jsonError('Your account is ' . $user['status'] . '. Please contact support.', 403);
        }

        // Fetch profile details
        $profile = [];
        if ($user['role'] === 'student') {
            $pStmt = $conn->prepare("SELECT student_number, course, year_level, contact_number, profile_picture FROM student_profiles WHERE user_id = ?");
            $pStmt->bind_param("i", $user['id']);
            $pStmt->execute();
            $pRes = $pStmt->get_result();
            if ($pRow = $pRes->fetch_assoc()) {
                $profile = $pRow;
            }
        } elseif ($user['role'] === 'counselor' || $user['role'] === 'admin') {
            $pStmt = $conn->prepare("SELECT specialization, contact_number, profile_picture FROM counselor_profiles WHERE user_id = ?");
            $pStmt->bind_param("i", $user['id']);
            $pStmt->execute();
            $pRes = $pStmt->get_result();
            if ($pRow = $pRes->fetch_assoc()) {
                $profile = $pRow;
            }
        }

        unset($user['password']);
        $userData = array_merge($user, $profile);

        closeApiDBConnection($conn);
        jsonSuccess(['user' => $userData], 'Login successful');

    } elseif ($action === 'register') {
        $name = sanitize($input['name'] ?? '');
        $email = sanitize($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $studentNumber = sanitize($input['student_number'] ?? '');
        $course = sanitize($input['course'] ?? '');
        $yearLevel = intval($input['year_level'] ?? 1);
        $contactNumber = sanitize($input['contact_number'] ?? '');

        if (empty($name) || empty($email) || empty($password) || empty($studentNumber)) {
            jsonError('All required fields must be provided', 422);
        }

        // Check if email already exists
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            jsonError('Email already registered', 409);
        }

        // Check if student number exists
        $chk2 = $conn->prepare("SELECT id FROM student_profiles WHERE student_number = ?");
        $chk2->bind_param("s", $studentNumber);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            jsonError('Student number already registered', 409);
        }

        // Generate student ID
        $userIdStr = 'STU' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $role = 'student';
        $status = 'active';

        $conn->begin_transaction();
        try {
            $insUser = $conn->prepare("INSERT INTO users (user_id, role, name, email, password, status) VALUES (?, ?, ?, ?, ?, ?)");
            $insUser->bind_param("ssssss", $userIdStr, $role, $name, $email, $hashed, $status);
            $insUser->execute();
            $newUserId = $conn->insert_id;

            $insProf = $conn->prepare("INSERT INTO student_profiles (user_id, student_number, course, year_level, contact_number) VALUES (?, ?, ?, ?, ?)");
            $insProf->bind_param("issis", $newUserId, $studentNumber, $course, $yearLevel, $contactNumber);
            $insProf->execute();

            $conn->commit();

            $userData = [
                'id' => $newUserId,
                'user_id' => $userIdStr,
                'role' => $role,
                'name' => $name,
                'email' => $email,
                'status' => $status,
                'student_number' => $studentNumber,
                'course' => $course,
                'year_level' => $yearLevel,
                'contact_number' => $contactNumber,
            ];

            closeApiDBConnection($conn);
            jsonSuccess(['user' => $userData], 'Registration successful', 201);
        } catch (Throwable $e) {
            $conn->rollback();
            closeApiDBConnection($conn);
            jsonError('Failed to register student: ' . $e->getMessage(), 500);
        }
    } elseif ($action === 'change_password') {
        $userId = intval($input['user_id'] ?? 0);
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (!$userId || empty($currentPassword) || empty($newPassword)) {
            jsonError('All password fields are required', 422);
        }

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            if (!password_verify($currentPassword, $row['password'])) {
                jsonError('Incorrect current password', 400);
            }

            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $newHash, $userId);
            $upd->execute();

            closeApiDBConnection($conn);
            jsonSuccess(null, 'Password updated successfully');
        } else {
            jsonError('User not found', 404);
        }
    }
}

closeApiDBConnection($conn);
jsonError('Invalid request method or action', 400);
