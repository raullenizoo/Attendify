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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/logo.jpg">

<style>

*{
box-sizing:border-box;
margin:0;
padding:0;
font-family:'Poppins',sans-serif;
}

body{
min-height:100vh;
background:#f4f7f9;
display:flex;
align-items:center;
justify-content:center;
padding:20px;
}

.container{
display:flex;
width:100%;
max-width:900px;
background:white;
border-radius:16px;
box-shadow:0 10px 40px rgba(0,0,0,0.12);
overflow:hidden;
}

.left-side{
flex:1;
background:#2c3e50;
color:white;
padding:60px 50px;
display:flex;
flex-direction:column;
justify-content:center;
}

.left-side h1{
font-size:2.8rem;
margin-bottom:1rem;
}

.left-side p{
font-size:1.1rem;
line-height:1.7;
opacity:0.9;
}

.right-side{
flex:1;
padding:60px 50px;
background:white;
}

h2{
margin-bottom:2rem;
color:#2c3e50;
font-weight:600;
}

.success{
background:#e8f5e9;
color:#2e7d32;
padding:12px 16px;
border-radius:8px;
margin-bottom:1.5rem;
}

.error{
background:#ffebee;
color:#c62828;
padding:12px 16px;
border-radius:8px;
margin-bottom:1.5rem;
}

form{
display:flex;
flex-direction:column;
}

label{
margin-bottom:0.5rem;
font-weight:500;
color:#444;
}

input, select, textarea {
padding:14px 16px;
margin-bottom:1.2rem;
border:1px solid #d1d9e0;
border-radius:8px;
font-size:1rem;
width:100%;
}

input:focus, select:focus, textarea:focus{
outline:none;
border-color:#3498db;
box-shadow:0 0 0 3px rgba(52,152,219,0.15);
}

button{
padding:14px;
background:#3498db;
color:white;
border:none;
border-radius:8px;
font-size:1.05rem;
font-weight:600;
cursor:pointer;
}

button:hover{
background:#2980b9;
}

.footer-links{
margin-top:1.8rem;
text-align:center;
font-size:0.92rem;
color:#555;
}

.footer-links a{
color:#3498db;
font-weight:500;
}

.footer-links a:hover{
text-decoration:underline;
}

@media(max-width:768px){

.container{
flex-direction:column;
max-width:480px;
}

.left-side,.right-side{
padding:50px 30px;
}

}

</style>
</head>
<body>

<div class="container">
    <div class="left-side">
        <h1>Attendify</h1>
        <p><strong>Streamline your classroom.</strong><br>Request elevated access to manage student attendance and generate reports with ease.</p>
    </div>

    <div class="right-side">
        <h2>Request Access</h2>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <label>First Name</label>
            <input type="text" name="first_name" placeholder="Enter your first name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>

            <label>Last Name</label>
            <input type="text" name="last_name" placeholder="Enter your last name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>

            <label>ID Number</label>
            <input type="text" name="id_number" placeholder="1234567890" value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>" required>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="your.email@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter a strong password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Re-enter your password" required>

            <label>Role Request</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="Teacher" <?php echo (($_POST['role'] ?? '') == 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
                <option value="Admin" <?php echo (($_POST['role'] ?? '') == 'Admin') ? 'selected' : ''; ?>>Admin</option>
            </select>

            <label>Optional Message (Why you need access)</label>
            <textarea name="message" placeholder="e.g., I need admin access to manage user accounts." rows="4"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>

            <button type="submit">Submit Request</button>

            <div class="footer-links">
                <p>Already have an account? <a href="/Attendify/public/get-started.php">Sign in here</a></p>
            </div>
        </form>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>