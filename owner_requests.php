<?php
require 'config.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');
$uid = $_SESSION['user_id'];
// show requests for houses owned by this user
$query = "SELECT r.id,r.status,r.request_date, u.name requester, h.title, h.id house_id FROM requests r
JOIN users u ON r.user_id = u.id
JOIN houses h ON r.house_id = h.id
WHERE h.owner_id = ? ORDER BY r.request_date DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i',$uid);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Owner Requests</title><link rel="stylesheet" href="style.css"></head><body>
<h2>Booking Requests for Your Houses</h2>
<?php
if($res->num_rows==0) echo "<p>No requests.</p>";
else {
    while($r = $res->fetch_assoc()){
        echo "<div class='request'>";
        echo "<p>House: ".esc($r['title'])." | Requester: ".esc($r['requester'])." | Date: ".esc($r['request_date'])." | Status: ".esc($r['status'])."</p>";
        if($r['status']=='pending'){
            echo "<p><a href='respond_request.php?id=".esc($r['id'])."&action=accept'>Accept</a> | <a href='respond_request.php?id=".esc($r['id'])."&action=reject'>Reject</a></p>";
        }
        echo "</div>";
    }
}
?>
</body></html>
