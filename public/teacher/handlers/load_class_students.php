<?php
require '../../../config/db.php';
require '../../../includes/security.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$class_id = $input['class_id'] ?? null;

if (!$class_id) {
    echo json_encode(['success' => false, 'message' => 'Class ID not provided']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Verify teacher owns this class
$stmt = $conn->prepare("SELECT teacher_id FROM class_sections WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();

if (!$class || $class['teacher_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get students enrolled in this class
$stmt = $conn->prepare("
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.id_number, u.email 
    FROM users u 
    JOIN class_enrollments ce ON u.id = ce.student_id 
    WHERE ce.class_section_id = ? AND u.is_active = 1 AND ce.status = 'active'
    ORDER BY u.first_name, u.last_name
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query preparation failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $class_id);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Query execution failed: ' . $stmt->error]);
    exit();
}

$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Result retrieval failed: ' . $stmt->error]);
    exit();
}

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode([
    'success' => true,
    'students' => $students,
    'debug' => [
        'class_id' => $class_id,
        'user_id' => $user_id,
        'student_count' => count($students)
    ]
]);
?>

