<?php
require 'config.php';
$err = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    if(!$name || !$email || !$phone || !$password){
        $err = 'All fields required.';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param('s',$email);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows>0){
            $err = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $mysqli->prepare("INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)");
            $ins->bind_param('ssss',$name,$email,$phone,$hash);
            if($ins->execute()){
                header('Location: login.php?registered=1'); exit;
            } else {
                $err = 'Registration failed.';
            }
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Register</title><link rel="stylesheet" href="style.css"></head>
<body>
<h2>Register</h2>
<?php if($err) echo '<p class="error">'.esc($err).'</p>'; ?>
<form method="post">
<label>Full name</label><input name="name">
<label>Email</label><input name="email" type="email">
<label>Phone</label><input name="phone">
<label>Password</label><input name="password" type="password">
<button>Register</button>
</form>
<p><a href="login.php">Already have account? Login</a></p>
</body></html>
