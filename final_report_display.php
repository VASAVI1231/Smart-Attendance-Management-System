<?php    
require_once 'helpers.php';    
require_login();    
$user = current_user();    
    
$months = $_POST['months'] ?? [];    
$class_id = $_POST['class_id'];    
$semester = $_POST['semester'];    
$batch_year = $_POST['batch_year'];    
    
$class = $mysqli->query("SELECT * FROM classes WHERE id=$class_id")->fetch_assoc();    
    
$students = $mysqli->query("    
    SELECT * FROM students     
    WHERE class_id=$class_id     
      AND semester=$semester    
      AND batch_year='$batch_year'    
    ORDER BY roll_no    
")->fetch_all(MYSQLI_ASSOC);    
    
$months_list = [      
 1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",      
 7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December"      
];    
?>    
    
<!DOCTYPE html>    
<html>    
<head>    
<meta charset="UTF-8">    
<title>Monthly Attendance Report</title>    

<style>    
body{font-family:Arial;}    
    
.back-btn{    
    padding:6px 12px;    
    background:#444;    
    color:#fff;    
    border-radius:6px;    
    text-decoration:none;    
}    
    
.print-btn{    
    padding:8px 14px;    
    background:#0066ff;    
    color:white;    
    border:none;    
    border-radius:6px;    
    cursor:pointer;    
}    
    
table{    
    width:100%;    
    border-collapse:collapse;    
    margin-top:20px;    
    text-align:center;    
}    
    
th{    
    border:1px solid #555;    
    padding:6px;    
    background:#d9ecff;   /* Light Sky Blue */    
    font-weight:bold;    
}    
    
td{    
    border:1px solid #555;    
    padding:6px;    
}    
    
h2{text-align:center;margin-top:10px;}    
    
.print-area{    
    width:95%;    
    margin:0 auto;    
}    
    
@media print {    
  @page { size: A4 landscape; margin: 10px; }    
  .no-print { display:none !important; }    
  body {    
    -webkit-print-color-adjust: exact !important;    
    print-color-adjust: exact !important;    
  }    
  table { width:100% !important; page-break-inside: avoid; }    
}    
</style>    
    
</head>    
<body>     
    
<h2>Monthly Final Attendance</h2>    
    
<p style="text-align:center;">    
<b>Class:</b> <?=esc($class['name'])?>    
&nbsp;&nbsp; | &nbsp;&nbsp;
<b>Batch:</b> <?=esc($batch_year)?>    
&nbsp;&nbsp; | &nbsp;&nbsp;    
<b>Semester:</b> <?=esc($semester)?>    
</p>    
    
<table>    
<tr>    
    <th>Roll No</th>    
    <th>Name</th>    
    
    <?php foreach($months as $m): ?>    
        <th><?=$months_list[$m]?> Working Hours</th>
        <th><?=$months_list[$m]?> Present</th>
    <?php endforeach; ?>    
    
    <th>Total Working Hours</th>
    <th>Total Present Hours</th>
    <th>Percentage %</th>    
    <th>Signature</th>    
</tr>    
    
<?php    
foreach($students as $s){    
    $sid = $s['id'];    
    
    $grand_total_days = 0;    
    $grand_present_days = 0;    
    
    echo "<tr>";    
    echo "<td>{$s['roll_no']}</td>";    
    echo "<td>{$s['name']}</td>";    
    
    foreach($months as $m){    
        $year = date("Y");    
    
        // TOTAL WORKING DAYS (present+absent)
        $working = $mysqli->query("    
            SELECT COUNT(*) c FROM attendance    
            WHERE student_id=$sid 
              AND MONTH(date)=$m 
              AND YEAR(date)=$year    
        ")->fetch_assoc()['c'];    
    
        // PRESENT DAYS ONLY
        $present = $mysqli->query("    
            SELECT COUNT(*) c FROM attendance    
            WHERE student_id=$sid 
              AND status='present'    
              AND MONTH(date)=$m 
              AND YEAR(date)=$year    
        ")->fetch_assoc()['c'];    
    
        echo "<td>$working</td>";    
        echo "<td>$present</td>";    
    
        $grand_total_days += $working;    
        $grand_present_days += $present;    
    }    
    
    $perc = $grand_total_days ? round(($grand_present_days/$grand_total_days)*100,2) : 0;    
    
    echo "<td>$grand_total_days</td>";    
    echo "<td>$grand_present_days</td>";    
    echo "<td>$perc%</td>";    
    echo "<td></td>";   // Signature    
    echo "</tr>";    
}    
?>    
    
</table>    
    
<div class="no-print" style="margin:10px;">    
    <a href="final_report.php" class="back-btn">Back</a>    
    <button class="print-btn" onclick="window.print()">Print</button>    
</div>    
    
</body>    
</html>