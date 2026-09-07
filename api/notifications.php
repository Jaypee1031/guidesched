<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getApiDBConnection();

if ($method === 'GET') {
    $userId = intval($_GET['user_id'] ?? 0);
    if (!$userId) {
        jsonError('user_id is required', 422);
    }

    $stmt = $conn->prepare("SELECT id, user_id, appointment_id, message, type, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $unreadStmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $unreadStmt->bind_param("i", $userId);
    $unreadStmt->execute();
    $unreadCount = intval($unreadStmt->get_result()->fetch_assoc()['unread_count'] ?? 0);

    closeApiDBConnection($conn);
    jsonSuccess([
        'unread_count' => $unreadCount,
        'notifications' => $notifications
    ]);

} elseif ($method === 'POST') {
    $input = getJsonInput();
    $userId = intval($input['user_id'] ?? 0);
    $notifId = intval($input['notification_id'] ?? 0);
    $markAll = !empty($input['mark_all']);

    if (!$userId) {
        jsonError('user_id is required', 422);
    }

    if ($markAll) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        closeApiDBConnection($conn);
        jsonSuccess(null, 'All notifications marked as read');
    } elseif ($notifId) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notifId, $userId);
        $stmt->execute();
        closeApiDBConnection($conn);
        jsonSuccess(null, 'Notification marked as read');
    } else {
        closeApiDBConnection($conn);
        jsonError('Either notification_id or mark_all is required', 422);
    }
}

closeApiDBConnection($conn);
jsonError('Method not allowed', 405);
