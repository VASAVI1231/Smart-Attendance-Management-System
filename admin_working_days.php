<?php
// admin_working_days.php
require_once "helpers.php";
require_login();
$user = current_user();

if($user['role'] != 'admin'){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$results = [];
if($from && $to){

    // Convert to timestamps
    $start = strtotime($from);
    $end   = strtotime($to);

    $total_days = 0;
    $sundays = 0;
    $holidays = 0;

    // Load holiday list
    $holiday_list = [];
    $res = $mysqli->query("SELECT holiday_date FROM holidays");
    while($h = $res->fetch_assoc()){
        $holiday_list[] = $h['holiday_date'];
    }

    // Loop every day
    for($date = $start; $date <= $end; $date += 86400){

        $total_days++;

        $curr = date("Y-m-d", $date);
        $day  = date("N", $date);

        if($day == 7){
            $sundays++;
            continue;
        }

        if(in_array($curr, $holiday_list)){
            $holidays++;
            continue;
        }
    }

    $working_days = $total_days - ($sundays + $holidays);

    $results = [
        'total_days'  => $total_days,
        'sundays'     => $sundays,
        'holidays'    => $holidays,
        'working'     => $working_days
    ];
}

?>
<!doctype html>
<html>
<head>
<title>Working Days</title>
<link rel="stylesheet" href="style.css">

<style>
.container{
    max-width:800px;
    margin:auto;
    padding:20px;
}
.card{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 3px 10px #ccc;
    margin-bottom:20px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:10px;
    border:1px solid #ccc;
}
h2{
    color:#004aad;
    text-align:center;
}
</style>

</head>
<body>

<div class="container">

<h2>College Working Days (Admin)</h2>

<div class="card">
    <form method="get">
        <label><b>From Date</b></label>
        <input type="date" name="from" value="<?=esc($from)?>" required>

        <label><b>To Date</b></label>
        <input type="date" name="to" value="<?=esc($to)?>" required>

        <br><br>
        <button>Calculate</button>
    </form>
</div>

<?php if($results): ?>
<div class="card">
<h3>Results</h3>

<table>
<tr><th>Total Days</th><td><?=$results['total_days']?></td></tr>
<tr><th>Sundays</th><td><?=$results['sundays']?></td></tr>
<tr><th>Holidays</th><td><?=$results['holidays']?></td></tr>
<tr><th><b>Working Days</b></th><td><b><?=$results['working']?></b></td></tr>
</table>

</div>
<?php endif; ?>

<p><a href="dashboard.php">Back</a></p>

</div>

</body>
</html>