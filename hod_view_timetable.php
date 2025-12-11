<?php  
//hod_view_timetable.php
require_once 'helpers.php';  
require_login();  
$user = current_user();  
$mysqli->set_charset("utf8mb4");  

if($user['role']!='hod'){  
    flash("Access denied");  
    header("Location: dashboard.php");  
    exit;  
}  

$semesters = [1,2,3,4,5,6];  
$selected_sem   = $_GET['sem']      ?? 0;  
$selected_class = $_GET['class_id'] ?? 0;  
$selected_batch = $_GET['batch']    ?? '';

$selected_sem   = (int)$selected_sem;
$selected_class = (int)$selected_class;

$classes = $mysqli->query("SELECT id,name FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);  

$days = [
    1=>"Monday", 2=>"Tuesday", 3=>"Wednesday",
    4=>"Thursday",5=>"Friday",6=>"Saturday"
];

$rows = [];  
$time_slots = [];  
$grid = [];  

// ---------------- LOAD TIMETABLE ONLY IF ALL 3 SELECTED ----------------
if($selected_class && $selected_sem && $selected_batch){

    $stmt = $mysqli->prepare("
        SELECT t.*, 
               u.full_name AS lecturer_name, 
               c.name AS class_name
        FROM timetable t
        LEFT JOIN users u ON t.lecturer_id = u.id
        LEFT JOIN classes c ON t.class_id = c.id
        WHERE t.class_id = ? 
          AND t.semester = ?
          AND t.batch_year = ?
        ORDER BY t.start_time, t.hour_no, t.day_of_week
    ");

    $stmt->bind_param("iis", $selected_class, $selected_sem, $selected_batch);
    $stmt->execute();
    $res = $stmt->get_result();

    while($r = $res->fetch_assoc()){
        $rows[] = $r;

        $slot_key = $r['start_time'].'-'.$r['end_time'];

        if(!isset($time_slots[$slot_key])){
            $time_slots[$slot_key] = [
                'start_time'=>$r['start_time'],
                'end_time'=>$r['end_time'],
                'hour_no'=>$r['hour_no']
            ];
        }

        $grid[(int)$r['day_of_week']][$slot_key] = $r;
    }
    $stmt->close();

    // sort times
   // CUSTOM FIXED ORDER
$custom_order = [
    "10:00" => 1,
    "11:00" => 2,
    "12:00" => 3,
    "01:00" => 4,
    "02:00" => 5,
    "03:00" => 6,
    "04:00" => 7,
    "05:00" => 8,
];

// Normalize time (convert 2:00 → 02:00)
function normalize_time($t){
    list($h,$m) = explode(":", $t);
    return str_pad($h,2,'0',STR_PAD_LEFT).":".str_pad($m,2,'0',STR_PAD_LEFT);
}

// Sort using custom order
usort($time_slots, function($a, $b) use ($custom_order){

    $t1 = normalize_time($a['start_time']);
    $t2 = normalize_time($b['start_time']);

    $o1 = $custom_order[$t1] ?? 999;
    $o2 = $custom_order[$t2] ?? 999;

    return $o1 <=> $o2;
});

}

function shortt($s){ return $s ? substr($s,0,5) : ''; }
function h($s){ return htmlspecialchars($s,ENT_QUOTES,'UTF-8'); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>HOD Timetable View</title>
<link rel="stylesheet" href="style.css">

<style>
body{font-family:Arial}
.container{max-width:1200px;margin:15px auto;padding:10px}
.header{display:flex;gap:12px;margin-bottom:10px}
.select{padding:6px;border-radius:6px;border:1px solid #aaa}
.print-btn{background:#2dbe60;color:#fff;border:none;padding:6px 12px;border-radius:6px}
.back-btn{background:#dc3545;color:#fff;border:none;padding:6px 12px;border-radius:6px}

.table-wrap{overflow:auto;margin-top:10px}
.timetable{width:100%;border-collapse:collapse;border:3px solid #7c878a}
.timetable th,.timetable td{
    border:2px solid #7c878a;
    padding:12px;text-align:left;font-size:14px;
}
.timetable th{background:#d6d7d8}
.day-col{background:#f1f1f1;font-weight:bold;width:140px}

.legend{margin-top:20px;font-size:14px}
.legend table{width:60%;border-collapse:collapse;margin-top:10px}
.legend td{padding:6px;border-bottom:1px solid #ddd}

.subject-title{font-weight:bold}
.type-tag{
    font-size:11px;
    padding:2px 6px;
    background:#cfd4d6;
    display:inline-block;
    border-radius:4px;
    margin-top:4px;
}
.lecturer-name{font-size:12px;color:#444;margin-top:4px}

@media print{
  body *{visibility:hidden}
  #print-area, #print-area *{visibility:visible}
  #print-area{position:absolute;left:0;top:0;width:100%}
}
</style>

</head>
<body>

<div class="container">

<div class="header">

    <div>
        <label>Semester:</label>
        <select class="select" id="sem" onchange="refresh()">
            <option value="">--Select--</option>
            <?php foreach($semesters as $s): ?>
            <option value="<?=$s?>" <?=$selected_sem==$s?'selected':''?>>Sem <?=$s?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label>Class:</label>
        <select class="select" id="class_id" onchange="refresh()">
            <option value="">--Select--</option>
            <?php foreach($classes as $c): ?>
            <option value="<?=$c['id']?>" <?=$selected_class==$c['id']?'selected':''?>><?=h($c['name'])?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label>Batch:</label>
        <select class="select" id="batch" onchange="refresh()">
            <option value="">--Select--</option>
            <?php foreach(year_list() as $y): 
                $b = $y."-".($y+1);
            ?>
            <option value="<?=$b?>" <?=$selected_batch==$b?'selected':''?>><?=$b?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-left:auto">
        <button class="print-btn" onclick="window.print()">Print</button>
        <a href="dashboard.php"><button class="back-btn">Back</button></a>
    </div>

</div>

<?php if(!$selected_class || !$selected_sem || !$selected_batch): ?>
    <p>Please select Semester, Class & Batch.</p>
<?php else: ?>

<div id="print-area">

<h3>
Class:
<?php foreach($classes as $c){ if($c['id']==$selected_class) echo h($c['name']); } ?>
 | Semester <?=$selected_sem?>
 | Batch <?=$selected_batch?>
</h3>

<?php if(empty($time_slots)): ?>
<p>No timetable found.</p>
<?php else: ?>

<div class="table-wrap">
<table class="timetable">
<thead>
<tr>
  <th class="day-col">Day / Time</th>
  <?php foreach($time_slots as $slot): ?>
      <th><?=shortt($slot['start_time'])?> - <?=shortt($slot['end_time'])?></th>
  <?php endforeach; ?>
</tr>
</thead>

<tbody>
<?php foreach($days as $dnum=>$dname): ?>
<tr>
  <td class="day-col"><?=h($dname)?></td>

  <?php foreach($time_slots as $slot):
        $key = $slot['start_time'].'-'.$slot['end_time'];
        if(isset($grid[$dnum][$key])){
            $r = $grid[$dnum][$key];

            $cell  = "<div class='subject-title'>".h($r['subject_name'])."</div>";
            $cell .= "<div class='type-tag'>".strtoupper(h($r['type']))."</div>";
            if(!empty($r['lecturer_name']))
                $cell .= "<div class='lecturer-name'>".h($r['lecturer_name'])."</div>";
        } else {
            $cell = "&nbsp;";
        }
  ?>
    <td><?=$cell?></td>
  <?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>

</table>
</div>

<!-- SUBJECT–LECTURER LIST -->
<div class="legend">
<strong>Subject – Lecturer List</strong>

<?php  
$pairs=[];  
foreach($rows as $r){
    $key = strtolower(trim($r['subject_name']) . "|" . trim($r['lecturer_name']));
    if(!isset($pairs[$key])){
        $pairs[$key] = [
            trim($r['subject_name']),
            trim($r['lecturer_name'])
        ];
    }
}
?>
<table style="width:100%;border-collapse:collapse;margin-top:10px;">
<tr style="background:#e8e8e8;font-weight:bold;">
    <td style="padding:6px;border:1px solid #ccc;">Subject</td>
    <td style="padding:6px;border:1px solid #ccc;">Lecturer</td>
</tr>

<?php foreach($pairs as $p): ?>
<tr>
    <td style="padding:6px;border:1px solid #ccc;"><?=h($p[0])?></td>
    <td style="padding:6px;border:1px solid #ccc;"><?=h($p[1])?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php endif; ?>
</div>
<?php endif; ?>

</div>

<script>
function refresh(){
    let s = document.getElementById('sem').value;
    let c = document.getElementById('class_id').value;
    let b = document.getElementById('batch').value;

    if(s && c && b){
        window.location = "hod_view_timetable.php?sem="+s+"&class_id="+c+"&batch="+b;
    } else {
        window.location = "hod_view_timetable.php?sem="+s+"&class_id="+c+"&batch="+b;
    }
}
</script>

</body>
</html>