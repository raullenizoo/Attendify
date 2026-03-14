<?php
require '../../../config/db.php';
require '../../../includes/security.php';

header('Content-Type: application/json');

// For testing without auth, we'll use teacher ID 3 (Sir Bernce)
$user_id = 3;
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 1;

$response = [
    'session_user_id' => $_SESSION['user_id'] ?? null,
    'test_user_id' => $user_id,
    'test_class_id' => $class_id,
    'class_check' => null,
    'students' => null,
    'error' => null
];

// Check class exists
$stmt = $conn->prepare("SELECT id, subject_id, teacher_id FROM class_sections WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$classResult = $stmt->get_result();
if ($classResult->num_rows > 0) {
    $response['class_check'] = $classResult->fetch_assoc();
}

// Check enrollments exist
$stmt = $conn->prepare("
    SELECT COUNT(*) as enrollment_count, GROUP_CONCAT(student_id) as student_ids
    FROM class_enrollments 
    WHERE class_section_id = ?
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$enrollResult = $stmt->get_result();
if ($enrollResult->num_rows > 0) {
    $response['enrollments'] = $enrollResult->fetch_assoc();
}

// Get students
$stmt = $conn->prepare("
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.id_number, u.email, u.is_active, ce.status
    FROM users u 
    LEFT JOIN class_enrollments ce ON u.id = ce.student_id 
    WHERE ce.class_section_id = ? AND u.is_active = 1 AND ce.status = 'active'
    ORDER BY u.first_name, u.last_name
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$studentsResult = $stmt->get_result();
$students = [];
while ($row = $studentsResult->fetch_assoc()) {
    $students[] = $row;
}
$response['students'] = $students;
$response['student_count'] = count($students);

// Check all users
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE is_active = 1");
$stmt->execute();
$userCountResult = $stmt->get_result();
$response['total_active_users'] = $userCountResult->fetch_assoc()['total_users'];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
