<?php
session_start();
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
$records = $input['records'] ?? [];

if (!$class_id || empty($records)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
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

// Process each attendance record
$success_count = 0;
$error_count = 0;

foreach ($records as $record) {
    $student_id = $record['student_id'] ?? null;
    $status = $record['status'] ?? 'present';
    
    if (!$student_id) continue;
    
    // Validate status
    $valid_statuses = ['present', 'late', 'absent', 'excused'];
    if (!in_array($status, $valid_statuses)) {
        $status = 'present';
    }
    
    // Insert or update attendance record
    $stmt = $conn->prepare("
        INSERT INTO attendance_records (student_id, class_section_id, attendance_date, status, marked_by, marked_at)
        VALUES (?, ?, CURDATE(), ?, ?, NOW())
        ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by), marked_at = NOW()
    ");
    
    $stmt->bind_param("iisi", $student_id, $class_id, $status, $user_id);
    
    if ($stmt->execute()) {
        $success_count++;
    } else {
        $error_count++;
    }
}

if ($error_count === 0) {
    echo json_encode([
        'success' => true,
        'message' => "$success_count attendance records submitted successfully"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "$success_count succeeded, $error_count failed"
    ]);
}
?>
