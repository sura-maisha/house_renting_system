<?php
require 'config.php';
if(!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$err=''; $succ='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = trim($_POST['title']); $location = trim($_POST['location']);
    $rent = floatval($_POST['rent']); $rooms = intval($_POST['rooms']);
    $desc = trim($_POST['description']);
    if(!$title || !$location || !$rent || !$rooms){ $err='Please fill required fields.'; }
    else {
        $img_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
      }
        $ins = $mysqli->prepare("INSERT INTO houses (owner_id,title,location,rent,rooms,description,image_path) VALUES (?,?,?,?,?,?,?)");
        $ins->bind_param('issdiss', $_SESSION['user_id'], $title, $location, $rent, $rooms, $desc, $img_path);
        if($ins->execute()){ $succ='House listed successfully.'; } else { $err='Failed to list house.'; }
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Add House</title><link rel="stylesheet" href="style.css"></head><body>
<h2>Add House</h2>
<?php if($err) echo '<p class="error">'.esc($err).'</p>'; if($succ) echo '<p class="success">'.esc($succ).'</p>'; ?>
<form method="post" enctype="multipart/form-data">
<label>Title</label><input name="title">
<label>Location</label><input name="location">
<label>Monthly Rent</label><input name="rent" type="number" step="0.01">
<label>Rooms</label><input name="rooms" type="number">
<label>Description</label><textarea name="description"></textarea>
<label>Image</label><input name="image" type="file" accept="image/*">
<button>Add House</button>
</form>
</body></html>
