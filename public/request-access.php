<?php
session_start();

/* =============================================================
   BOOTSTRAP INCLUDE — that's all you need now!
   ============================================================= */
require_once __DIR__ . '/bootstrap.php';

$errors = [];
$success_message = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token. Please try again.';
    } else {
        // Input sanitization and validation
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $id_number = trim($_POST['id_number']);
        $email = strtolower(trim($_POST['email']));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];
        $message = trim($_POST['message']);

        // Validate empty inputs
        if (empty($first_name)) { $errors[] = 'First Name is required.'; }
        if (empty($last_name)) { $errors[] = 'Last Name is required.'; }
        if (empty($id_number)) { $errors[] = 'ID Number is required.'; }
        if (empty($email)) { $errors[] = 'Email is required.'; }
        if (empty($password)) { $errors[] = 'Password is required.'; }
        if (empty($confirm_password)) { $errors[] = 'Confirm Password is required.'; }
        if (empty($role)) { $errors[] = 'Role Request is required.'; }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        // Validate password strength
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        // Validate password match
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }

        // Validate role
        $allowed_roles = ['Teacher', 'Admin'];
        if (!in_array($role, $allowed_roles)) {
            $errors[] = 'Invalid role selected.';
        }

        // Check if email or ID number already exists
        if (empty($errors)) {
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? OR id_number = ?");
            $stmt_check->bind_param("ss", $email, $id_number);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = 'Email or ID Number already registered.';
            }
            $stmt_check->close();
        }

        // If no validation errors, proceed with insertion
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_active = 0; // Pending approval
            $created_at = date('Y-m-d H:i:s');

            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, id_number, email, password_hash, role, is_active, created_at, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssiss", $first_name, $last_name, $id_number, $email, $hashed_password, $role, $is_active, $created_at, $message);

            if ($stmt->execute()) {
                $success_message = 'Your access request has been submitted and is awaiting admin approval.';
                $_POST = [];
                unset($_SESSION['csrf_token']);
            } else {
                $errors[] = 'Something went wrong. Please try again later.';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Access - Attendify</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        a { text-decoration: none; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { display: flex; min-height: 100vh; align-items: center; justify-content: center; background-color: #f4f7f9; padding: 20px; }
        
        .container {
            display: flex; width: 900px; background: white; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;
        }
        
        .left-side {
            flex: 1; padding: 50px; background-color: #2c3e50; color: white;
            display: flex; flex-direction: column; justify-content: center;
        }
        .left-side h1 { font-size: 2.5rem; margin-bottom: 15px; }
        .left-side p { font-size: 1.1rem; line-height: 1.6; opacity: 0.9; }

        .right-side {
            flex: 1.2; padding: 40px; display: flex; flex-direction: column; justify-content: center;
        }
        .right-side h2 { margin-bottom: 20px; color: #333; text-align: center; }
        
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 0.9rem; }
        input[type="text"], input[type="email"], input[type="password"], select, textarea {
            width: 100%; padding: 12px; margin: 5px 0; border: 1px solid #ccc;
            border-radius: 8px; font-size: 0.9rem; box-sizing: border-box;
        }
        button {
            width: 100%; padding: 14px; margin-top: 15px; background-color: #3498db;
            color: white; border: none; border-radius: 8px; cursor: pointer;
            font-size: 1rem; font-weight: bold; transition: background-color 0.3s ease;
        }
        button:hover { background-color: #2980b9; }
        
        .error-message { color: #e74c3c; font-size: 0.85rem; margin-top: 5px; text-align: center; }
        .success-message { color: #27ae60; font-size: 0.95rem; margin-top: 15px; text-align: center; font-weight: 600; }

        .footer-links { margin-top: 20px; text-align: center; font-size: 0.85em; }
        .footer-links a { color: #3498db; font-weight: 600; }
        .footer-links a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .container { flex-direction: column; width: auto; }
            .left-side { padding: 30px; text-align: center; }
            .right-side { padding: 30px; }
            .row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left-side">
        <h1>Attendify</h1>
        <p><strong>Streamline your attendance.</strong><br> Request elevated access to manage student attendance and generate reports with ease.</p>
    </div>
    <div class="right-side">
        <h2>Request Access</h2>

        <?php
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<p class="error-message">' . htmlspecialchars($error) . '</p>';
            }
        }
        if (!empty($success_message)) {
            echo '<p class="success-message">' . htmlspecialchars($success_message) . '</p>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="row">
                <div class="form-group"><input type="text" id="first_name" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required></div>
                <div class="form-group"><input type="text" id="last_name" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required></div>
            </div>

            <div class="form-group"><input type="text" id="id_number" name="id_number" placeholder="ID Number" value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>" required></div>
            <div class="form-group"><input type="email" id="email" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required></div>

            <div class="row">
                <div class="form-group"><input type="password" id="password" name="password" placeholder="Password" required></div>
                <div class="form-group"><input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required></div>
            </div>

            <div class="form-group">
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="Teacher" <?php echo (($_POST['role'] ?? '') == 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
                    <option value="Admin" <?php echo (($_POST['role'] ?? '') == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Optional Message (Why you need access)</label>
                <textarea id="message" name="message" placeholder="e.g., I need admin access to manage user accounts."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>

            <button type="submit">Submit Request</button>
        </form>

        <div class="footer-links">
            <p><a href="<?= BASE_URL ?>get-started.php">Already have an account? Login here</a></p>
        </div>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>