<?php
require 'config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>House Renting System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>House Renting System</h1>
    <nav>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="add_house.php">Add House</a>
        <a href="owner_requests.php">Requests</a>
        <a href="logout.php">Logout (<?php echo esc($_SESSION['name']); ?>)</a>
      <?php else: ?>
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
      <?php endif; ?>
      <a href="index.php">View Houses</a>
    </nav>
  </header>

  <main>
    <section class="search">
      <form method="get" action="search.php">
        <input type="text" name="q" placeholder="Search by location or max rent">
        <button>Search</button>
      </form>
    </section>

    <section class="houses">
      <h2>Available Houses</h2>
      <?php
// check if houses table exists
$check = $mysqli->query("SHOW TABLES LIKE 'houses'");

if ($check->num_rows == 0) {
    echo "<p style='color:red;'>
        Houses table does not exist.<br>
        Please create the database tables first using phpMyAdmin.
    </p>";
} else {

    $stmt = $mysqli->prepare("
        SELECT h.id, h.title, h.location, h.rent, h.rooms, h.image_path,
               u.name AS owner
        FROM houses h
        LEFT JOIN users u ON h.owner_id = u.id
        ORDER BY h.id DESC
    ");
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        echo "<p>No houses available.</p>";
    } else {
        while ($row = $res->fetch_assoc()) {
            echo '<div class="house">';
            if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                echo '<img src="'.esc($row['image_path']).'">';
            }
            echo '<h3>'.esc($row['title']).'</h3>';
            echo '<p>Location: '.esc($row['location']).'</p>';
            echo '<p>Rent: '.esc($row['rent']).'</p>';
            echo '<p>Rooms: '.esc($row['rooms']).'</p>';
            echo '<p>Owner: '.esc($row['owner']).'</p>';
            echo '</div>';
        }
    }
}
?>
</body>
</html>
