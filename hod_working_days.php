<?php
require_once 'helpers.php';
require_login();
$user = current_user();

if($user['role'] != 'hod'){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$total_days = null;
$start = '';
$end = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    // Validate
    if(!empty($start) && !empty($end)){
        
        // Sundays and Holidays already excluded (because no attendance added those days)
        // So we simply count DISTINCT attendance dates
        $q = $mysqli->query("
            SELECT COUNT(DISTINCT date) AS d
            FROM attendance
            WHERE date BETWEEN '$start' AND '$end'
        ")->fetch_assoc();

        $total_days = $q['d'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>College Working Days</title>
<meta charset="utf-8">
<link rel="stylesheet" href="style.css">

<style>
.card{
    padding:20px;
    border:1px solid #ccc;
    border-radius:8px;
    background:#f8f9ff;
}
input[type=date], button{
    padding:8px;
    margin:5px;
}
button{
    background:#0066ff;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
.result-box{
    margin-top:20px;
    padding:15px;
    font-size:18px;
    background:#e7f3ff;
    border-left:6px solid #007bff;
}
</style>

</head>
<body>
<div class="container">

<h1>College Working Days (HOD)</h1>

<div class="card">
<form method="post">

<label><strong>Start Date</strong></label><br>
<input type="date" name="start_date" value="<?=$start?>" required><br>

<label><strong>End Date</strong></label><br>
<input type="date" name="end_date" value="<?=$end?>" required><br><br>

<button type="submit">Calculate Working Days</button>

</form>
</div>

<?php if($total_days !== null): ?>
<div class="result-box">
    <strong>Total College Working Days:</strong> <?=$total_days?>
</div>
<?php endif; ?>

<p><a href="dashboard.php">Back</a></p>

</div>
</body>
</html>