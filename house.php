<?php
require 'config.php';
if(!isset($_GET['id'])) { header('Location: index.php'); exit; }
$id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT h.*, u.name owner FROM houses h LEFT JOIN users u ON h.owner_id = u.id WHERE h.id = ?");
$stmt->bind_param('i',$id); $stmt->execute(); $res = $stmt->get_result();
if($res->num_rows==0) { echo "House not found."; exit; }
$h = $res->fetch_assoc();
?>
<!doctype html><html><head><meta charset="utf-8"><title><?php echo esc($h['title']); ?></title><link rel="stylesheet" href="style.css"></head><body>
<h2><?php echo esc($h['title']); ?></h2>
<?php if($h['image_path'] && file_exists($h['image_path'])) echo '<img src="'.esc($h['image_path']).'">'; ?>
<p>Location: <?php echo esc($h['location']); ?></p>
<p>Rent: <?php echo esc($h['rent']); ?></p>
<p>Rooms: <?php echo esc($h['rooms']); ?></p>
<p>Owner: <?php echo esc($h['owner']); ?></p>
<p>Description: <?php echo nl2br(esc($h['description'])); ?></p>

<?php if(isset($_SESSION['user_id'])): ?>
  <form method="post" action="send_request.php">
    <input type="hidden" name="house_id" value="<?php echo esc($h['id']); ?>">
    <button>Send Booking Request</button>
  </form>
<?php else: ?>
  <p><a href="login.php">Login</a> to send booking request.</p>
<?php endif; ?>
</body></html>
