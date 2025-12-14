<?php
require 'config.php';
$q = trim($_GET['q'] ?? '');
$where = "";
$params = [];
if($q!==''){
    // try numeric rent
    if(is_numeric($q)) {
        $where = "WHERE rent <= ?";
        $params = [floatval($q)];
    } else {
        $where = "WHERE location LIKE ?";
        $params = ["%".$q."%"];
    }
}
$sql = "SELECT id,title,location,rent,rooms,image_path FROM houses $where ORDER BY id DESC";
$stmt = $mysqli->prepare($sql);
if($params){
    if(is_numeric($q)) $stmt->bind_param('d', $params[0]);
    else $stmt->bind_param('s', $params[0]);
}
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Search</title><link rel="stylesheet" href="style.css"></head><body>
<h2>Search Results</h2>
<?php
if($res->num_rows==0) echo "<p>No houses match your search.</p>";
else {
    while($row = $res->fetch_assoc()){
        echo "<div class='house'>";
        if($row['image_path'] && file_exists($row['image_path'])) echo "<img src='".esc($row['image_path'])."'>";
        echo "<h3>".esc($row['title'])."</h3>";
        echo "<p>Location: ".esc($row['location'])."</p>";
        echo "<p>Rent: ".esc($row['rent'])."</p>";
        echo "<p><a href='house.php?id=".esc($row['id'])."'>View</a></p>";
        echo "</div>";
    }
}
?>
</body></html>
