<?php
session_start();
// require '../includes/security.php';
require '../config/db.php';

$errors = [];
$success = "";

/* Generate CSRF token */
if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

/* CSRF validation */
if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
    die("Invalid request.");
}

/* Collect form data */
$first_name = trim($_POST['first_name']);
$last_name  = trim($_POST['last_name']);
$id_number  = trim($_POST['id_number']);
$email      = strtolower(trim($_POST['email']));
$department = trim($_POST['department']);
$year_level = trim($_POST['year_level']);
$section    = trim($_POST['section']);
$password   = $_POST['password'];
$password2  = $_POST['confirm_password'];

/* Password validation */
if($password !== $password2){
    $errors[] = "Passwords do not match.";
}

if(strlen($password) < 8){
    $errors[] = "Password must be at least 8 characters.";
}

/* Check duplicate email or ID */
$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR id_number = ?");
$check->bind_param("ss", $email, $id_number);
$check->execute();
$result = $check->get_result();

if($result->num_rows > 0){
    $errors[] = "The email or ID number already exist.";
}

/* If no errors insert user */
if(empty($errors)){

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
INSERT INTO users
(first_name,last_name,id_number,email,password_hash,department,year_level,section)
VALUES (?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
"ssssssss",
$first_name,
$last_name,
$id_number,
$email,
$password_hash,
$department,
$year_level,
$section
);

if($stmt->execute()){

/* Auto login user */
$_SESSION['user_id'] = $stmt->insert_id;
$_SESSION['user_email'] = $email;

/* Redirect to dashboard */
header("Location: student-dashboard.php");
exit();

}else{

$errors[] = "Registration failed. Please try again.";

}

}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendify - Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        a {
            text-decoration: none;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { display: flex; min-height: 100vh; align-items: center; justify-content: center; background-color: #f4f7f9; padding: 20px; }
        
        .container { display: flex; width: 900px; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        
        /* Left Side: Branding */
        .left-side { flex: 1; padding: 50px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; justify-content: center; }
        .left-side h1 { font-size: 2.5rem; margin-bottom: 15px; }
        .left-side p { font-size: 1.1rem; line-height: 1.6; opacity: 0.9; }

        /* Right Side: Form */
        .right-side { flex: 1.2; padding: 40px; display: flex; flex-direction: column; justify-content: center; }
        .right-side h2 { margin-bottom: 20px; color: #333; }
        
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        input { width: 100%; padding: 12px; margin: 5px 0; border: 1px solid #ccc; border-radius: 8px; font-size: 0.9rem; }
        button { width: 100%; padding: 14px; margin-top: 15px; background-color: #3498db; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: bold; }
        button:hover { background-color: #2980b9; }
        
        .footer-links { margin-top: 20px; text-align: center; font-size: 0.85em; }
        
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            .row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left-side">
        <h1>Attendify</h1>
        <p><strong>Streamline your classroom.</strong><br> Efficiently track student attendance and generate real-time reports with ease.</p>
    </div>
    <div class="right-side">
        <form action="" method="POST">
        
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <h2>Register Now!</h2>

        <?php if(!empty($errors)): ?>
        <div style="background:#ffdddd;padding:10px;border-radius:8px;margin-bottom:10px;color:#a00;">
        <?php foreach($errors as $error): ?>
        <p><?php echo $error; ?></p>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
        </div>
        
        <input type="text" name="id_number" placeholder="ID Number" required>
        <input type="email" name="email" placeholder="Email Address" required>
        
        <div class="row">
            <input type="text" name="department" placeholder="Department" required>
            <input type="text" name="year_level" placeholder="Year Level" required>
        </div>
        
        <input type="text" name="section" placeholder="Section" required>
        
        <div class="row">
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        
        <button>Register</button>
        
        <div class="footer-links">
            <p><a href="../public/get-started.php">Have an account? Login here</a></p>
            <p>New teacher/admin? <a href="../public/request-access.php">Request access here</a></p>
        </div>
        </form>
    </div>
</div>

</body>
</html>