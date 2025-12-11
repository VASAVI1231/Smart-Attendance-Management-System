<?php
//admin_logs.php
require_once 'helpers.php';
require_login();
$user = current_user();

if($user['role']!='admin'){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$actions = $mysqli->query("SELECT DISTINCT action FROM activity_log ORDER BY action")->fetch_all(MYSQLI_ASSOC);
$users = $mysqli->query("SELECT DISTINCT username FROM activity_log ORDER BY username")->fetch_all(MYSQLI_ASSOC);

$where=[];
if(!empty($_GET['action'])) $where[]="action='".$mysqli->real_escape_string($_GET['action'])."'";
if(!empty($_GET['user'])) $where[]="username='".$mysqli->real_escape_string($_GET['user'])."'";
if(!empty($_GET['from'])) $where[]="created_at>='".$mysqli->real_escape_string($_GET['from'])."'";
if(!empty($_GET['to'])) $where[]="created_at<='".$mysqli->real_escape_string($_GET['to'])."'";

$w = $where ? "WHERE ".implode(' AND ',$where) : "";
$rows = $mysqli->query("SELECT * FROM activity_log $w ORDER BY created_at DESC LIMIT 1000")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Activity Log</title>
<link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
<h1>Activity Log</h1>

<div class="card">
<form method="get">
<label>Action</label>
<select name="action">
<option value="">All</option>
<?php foreach($actions as $a): ?>
<option value="<?=$a['action']?>" <?=(($_GET['action']??'')==$a['action'])?'selected':''?>><?=$a['action']?></option>
<?php endforeach; ?>
</select>

<label>User</label>
<select name="user">
<option value="">All</option>
<?php foreach($users as $u): ?>
<option value="<?=$u['username']?>" <?=(($_GET['user']??'')==$u['username'])?'selected':''?>><?=$u['username']?></option>
<?php endforeach;?>
</select>

<label>From</label><input type="date" name="from" value="<?=esc($_GET['from']??'')?>">
<label>To</label><input type="date" name="to" value="<?=esc($_GET['to']??'')?>">

<button type="submit">Filter</button>
</form>
</div>

<div class="card">
<table class="table">
<tr><th>#</th><th>Date</th><th>User</th><th>Action</th><th>Target</th><th>Details</th><th>IP</th></tr>
<?php $i=1; foreach($rows as $r): ?>
<tr>
<td><?=$i++?></td>
<td><?=$r['created_at']?></td>
<td><?=$r['username']?></td>
<td><?=$r['action']?></td>
<td><?=$r['target_type']?>: <?=$r['target_id']?></td>
<td><?=$r['details']?></td>
<td><?=$r['ip_address']?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<a href="dashboard.php"><button>Back</button></a>
</div>
</body>
</html>