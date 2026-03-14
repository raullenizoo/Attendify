<?php
/* =============================================================
   UNIVERSAL PROJECT ROOT DETECTION (best method for your setup)
   ============================================================= */
$root = __DIR__;
while (!file_exists($root . '/config/db.php') && dirname($root) !== $root) {
    $root = dirname($root);
}
define('ROOT_PATH', $root);
define('BASE_URL', '/Attendify/public/');   // ← matches your current folder structure

/* =============================================================
   Include core files (secure session + DB)
   ============================================================= */
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/security.php';

/* -----------------------------
   Redirect if already logged in
------------------------------*/

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: " . BASE_URL . "admin/admin-dashboard.php");
    }
    elseif ($_SESSION['role'] === 'teacher') {
        header("Location: " . BASE_URL . "teacher/teacher-dashboard.php");
    }
    else {
        header("Location: " . BASE_URL . "student/student-dashboard.php");
    }

    exit();
}

/* -----------------------------
   CSRF Token
------------------------------*/

// Ensure a CSRF token is available for forms
csrf_token();

/* -----------------------------
   Messages
------------------------------*/

$error_message = '';
$success_message = '';

if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success_message = "Account created successfully! Please sign in.";
}

/* -----------------------------
   Login Processing
------------------------------*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Basic login rate limiting (per session)
    $max_login_attempts = 5;
    $login_window_seconds = 300; // 5 minutes

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    // Keep only recent attempts
    $_SESSION['login_attempts'] = array_filter(
        $_SESSION['login_attempts'],
        fn($ts) => $ts > (time() - $login_window_seconds)
    );

    if (count($_SESSION['login_attempts']) >= $max_login_attempts) {
        $wait = $login_window_seconds - (time() - min($_SESSION['login_attempts']));
        $error_message = "Too many login attempts. Please try again in " . ceil($wait / 60) . " minute(s).";
    } elseif (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {

        $error_message = "Security check failed. Please refresh and try again.";

    } else {

        $id_number = trim($_POST['id_number'] ?? '');
        $password  = trim($_POST['password'] ?? '');

        if ($id_number === '' || $password === '') {

            $error_message = "Please enter both ID number and password.";

        } else {

            $stmt = $conn->prepare("
                SELECT id, first_name, last_name, password_hash, role
                FROM users
                WHERE id_number = ?
                AND is_active = 1
                LIMIT 1
            ");

            if (!$stmt) {
                $error_message = "Server error. Please try again later.";
                error_log("Login prepare failed: " . $conn->error);
            } else {

                $stmt->bind_param("s", $id_number);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password_hash'])) {

                        // Reset login attempt counter on successful login
                        $_SESSION['login_attempts'] = [];

                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
                        $_SESSION['user_id_number'] = $id_number;
                        $_SESSION['role'] = $user['role'];

                        /* Update last login */
                        $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                        if ($update) {
                            $update->bind_param("i", $user['id']);
                            $update->execute();
                            $update->close();
                        }

                        /* New CSRF token */
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        /* Redirect based on role */
                        switch ($user['role']) {
                            case 'admin':
                                header("Location: " . BASE_URL . "admin/admin-dashboard.view.php");
                                break;
                            case 'teacher':
                                header("Location: " . BASE_URL . "teacher/teacher-dashboard.php");
                                break;
                            default:
                                header("Location: " . BASE_URL . "student/student-dashboard.php");
                        }
                        exit();

                    } else {
                        $_SESSION['login_attempts'][] = time();
                        $error_message = "Invalid ID number or password.";
                    }
                } else {
                    $_SESSION['login_attempts'][] = time();
                    $error_message = "Invalid ID number or password.";
                }
                $stmt->close();
            }
        }
    }

    // Regenerate CSRF for next form submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Attendify - Sign In</title>

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

input{
padding:14px 16px;
margin-bottom:1.2rem;
border:1px solid #d1d9e0;
border-radius:8px;
font-size:1rem;
}

input:focus{
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

<p>
<strong>Streamline your classroom.</strong><br>
Track attendance effortlessly and get real-time insights — made for students, teachers, and administrators.
</p>

</div>

<div class="right-side">

<h2>Welcome back!</h2>

<?php if($success_message): ?>
<div class="success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if($error_message): ?>
<div class="error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<form method="POST" autocomplete="off">

<?php csrf_input_field(); ?>

<label>ID Number</label>

<input 
type="text"
name="id_number"
placeholder="1234567890"
required
autofocus
>

<label>Password</label>

<input 
type="password"
name="password"
placeholder="Enter your password"
required
>

<button type="submit">Sign In</button>

<div class="footer-links">

<p>Don't have an account? <a href="/Attendify/public/register.php">Create one</a></p>

<p>New teacher or admin? <a href="/Attendify/public/request-access.php">Request access</a></p>

</div>

</form>

</div>

</div>

</body>
</html>