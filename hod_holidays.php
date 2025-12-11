<?php
//hod_holidays.php
require_once 'helpers.php';
require_login();
$user = current_user();
if(!in_array($user['role'], ['admin','hod'])){ flash('Access denied'); header('Location:dashboard.php'); exit; }

if($_SERVER['REQUEST_METHOD']=='POST'){
    $hdate = $mysqli->real_escape_string($_POST['holiday_date']);
    $reason = $mysqli->real_escape_string($_POST['reason']);
    $entered_by = $user['id'];
    $stmt = $mysqli->prepare("INSERT INTO holidays (holiday_date,reason,entered_by) VALUES (?,?,?)");
    $stmt->bind_param('ssi',$hdate,$reason,$entered_by);
    if($stmt->execute()) flash('Holiday added');
    else flash('DB error: '.$mysqli->error);
    header('Location: hod_holidays.php'); exit;
}
$hols = $mysqli->query("SELECT h.*, u.full_name FROM holidays h LEFT JOIN users u ON h.entered_by=u.id ORDER BY holiday_date DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Holidays</title>
<link rel="stylesheet" href="style.css"></head><body>
<div class="container">
  <h1>Holidays</h1>
  <form method="post">
    <label>Date</label><input type="date" name="holiday_date" required>
    <label>Reason</label><input type="text" name="reason">
    <button type="submit">Save Holiday</button>
  </form>

  <h3>Existing Holidays</h3>
  <table class="table"><tr><th>Date</th><th>Reason</th><th>Added By</th></tr>
  <?php foreach($hols as $h): ?>
    <tr><td><?=esc($h['holiday_date'])?></td><td><?=esc($h['reason'])?></td><td><?=esc($h['full_name'])?></td></tr>
  <?php endforeach; ?>
  </table>
  <p><a href="dashboard.php">Back</a></p>
</div></body></html>