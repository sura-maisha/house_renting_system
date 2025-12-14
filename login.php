<?php
require 'config.php';
$err = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if(!$email || !$password) $err = 'All fields required.';
    else {
        $stmt = $mysqli->prepare("SELECT id,name,password FROM users WHERE email=?");
        $stmt->bind_param('s',$email);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows==1){
            $u = $res->fetch_assoc();
            if(password_verify($password, $u['password'])){
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['name'] = $u['name'];
                header('Location: dashboard.php'); exit;
            } else $err = 'Invalid credentials.';
        } else $err = 'Invalid credentials.';
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Login</title><link rel="stylesheet" href="style.css"></head><body>
<h2>Login</h2>
<?php if(isset($_GET['registered'])) echo '<p class="success">Registration successful. Please login.</p>'; ?>
<?php if($err) echo '<p class="error">'.esc($err).'</p>'; ?>
<form method="post">
<label>Email</label><input name="email" type="email">
<label>Password</label><input name="password" type="password">
<button>Login</button>
</form>
</body></html>
