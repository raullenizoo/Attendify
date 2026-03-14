<?php
session_start();
require '../../../config/db.php';
require '../../../includes/security.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$title = trim($input['title'] ?? '');
$content = trim($input['content'] ?? '');
$class_section_id = isset($input['class_section_id']) && $input['class_section_id'] !== '' ? (int)$input['class_section_id'] : null;
$priority = in_array($input['priority'] ?? 'normal', ['normal','important','urgent']) ? $input['priority'] : 'normal';

if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO announcements (title, content, author_id, class_section_id, priority) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param('ssiss', $title, $content, $user_id, $class_section_id, $priority);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    exit();
}

$announcement_id = $stmt->insert_id;

$stmt = $conn->prepare("SELECT a.id, a.title, a.content, a.priority, a.created_at, u.first_name, u.last_name, a.class_section_id, s.section_name, s.subject_name FROM announcements a JOIN users u ON a.author_id = u.id LEFT JOIN class_sections s ON a.class_section_id = s.id WHERE a.id = ?");
$stmt->bind_param('i', $announcement_id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

echo json_encode(['success' => true, 'announcement' => $announcement]);
