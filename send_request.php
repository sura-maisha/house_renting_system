<?php
require 'config.php';
if(!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $house_id = intval($_POST['house_id']);
    $ins = $mysqli->prepare("INSERT INTO requests (user_id,house_id,status,request_date) VALUES (?,?,?,NOW())");
    $status = 'pending';
    $ins->bind_param('iis', $_SESSION['user_id'], $house_id, $status);
    if($ins->execute()){
        header('Location: house.php?id='.$house_id.'&requested=1'); exit;
    } else {
        echo "Failed to send request.";
    }
}
