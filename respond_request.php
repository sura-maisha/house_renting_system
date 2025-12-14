<?php
require 'config.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');
if(!isset($_GET['id']) || !isset($_GET['action'])) { header('Location: owner_requests.php'); exit; }
$id = intval($_GET['id']);
$action = $_GET['action'] === 'accept' ? 'accepted' : 'rejected';
// ensure owner owns the house for this request
$check = $mysqli->prepare("SELECT h.owner_id FROM requests r JOIN houses h ON r.house_id = h.id WHERE r.id = ?");
$check->bind_param('i',$id); $check->execute(); $res = $check->get_result();
if($res->num_rows==0) { echo 'Not found'; exit; }
$row = $res->fetch_assoc();
if($row['owner_id'] != $_SESSION['user_id']) { echo 'Unauthorized'; exit; }
$u = $mysqli->prepare("UPDATE requests SET status=? WHERE id=?");
$u->bind_param('si', $action, $id);
$u->execute();
header('Location: owner_requests.php');
