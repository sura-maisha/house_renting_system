<?php
require 'config.php';
if(!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
?>
<!doctype html><html><head><meta charset="utf-8"><title>Dashboard</title><link rel="stylesheet" href="style.css"></head><body>
<h2>Dashboard</h2>
<p>Welcome, <?php echo esc($_SESSION['name']); ?></p>
<p><a href="add_house.php">Add a new house</a> | <a href="owner_requests.php">View requests</a> | <a href="index.php">View Houses</a></p>
</body></html>
