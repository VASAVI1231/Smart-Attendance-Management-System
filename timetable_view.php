<?php
// timetable_view.php
require_once 'helpers.php';
require_login();

$user = current_user();

// Allow admin, hod, class lecturer to view timetable
if(!in_array($user['role'], ['admin','hod','class'])){
    flash("Access denied");
    header("Location: dashboard.php");
    exit;
}
$mysqli->set_charset("utf8mb4");

$semesters = [1,2,3,4,5,6];
$selected_sem   = isset($_GET['sem']) ? (int)$_GET['sem'] : 0;
$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$selected_batch = $_GET['batch'] ?? '';

$classes = $mysqli->query("SELECT id,name FROM classes ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$days = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];

$rows = []; 
$time_slots = []; 
$grid = [];

if($selected_class && $selected_sem && $selected_batch){
    $stmt = $mysqli->prepare("
        SELECT t.*, u.full_name AS lecturer_name, c.name AS class_name
        FROM timetable t
        LEFT JOIN users u ON t.lecturer_id = u.id
        LEFT JOIN classes c ON t.class_id = c.id
        WHERE t.class_id = ? AND t.semester = ? AND t.batch_year = ?
    ");
    $stmt->bind_param("iis",$selected_class,$selected_sem,$selected_batch);
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

    // ---- CUSTOM FIXED TIME ORDER ----
$custom_order = [
    "10:00" => 1,
    "11:00" => 2,
    "12:00" => 3,
    "01:00" => 4,
    "02:00" => 5,
    "03:00" => 6,
    "04:00" => 7,
    "05:00" => 8
];

// Normalize 2:00 → 02:00
function normalize_time($t){
    list($h,$m) = explode(":", $t);
    return str_pad($h,2,'0',STR_PAD_LEFT).":".str_pad($m,2,'0',STR_PAD_LEFT);
}

// Sort using custom sequence
usort($time_slots, function($a, $b) use ($custom_order){

    $t1 = normalize_time($a['start_time']);
    $t2 = normalize_time($b['start_time']);

    $o1 = $custom_order[$t1] ?? 999;
    $o2 = $custom_order[$t2] ?? 999;

    return $o1 <=> $o2;
});

}

function shortt($s){ return $s ? substr($s,0,5) : ''; }
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Timetable View</title>
<link rel="stylesheet" href="style.css">

<style>
body{font-family:Arial,Helvetica,sans-serif}
.container{max-width:1200px;margin:14px auto;padding:10px}
.header{display:flex;gap:12px;align-items:center;margin-bottom:10px}
.select{padding:8px;border-radius:6px;border:1px solid #bfc7c8}
.small{font-size:13px;color:#333}
.print-btn{background:#2dbe60;color:#fff;border:0;padding:8px 12px;border-radius:6px}
.back-btn{background:#dc3545;color:#fff;border:0;padding:8px 12px;border-radius:6px}
.table-wrap{overflow:auto;margin-top:12px}
.timetable{width:100%;border-collapse:collapse;border:4px solid #9aa1a3}
.timetable th,.timetable td{border:3px solid #9aa1a3;padding:14px;text-align:left;vertical-align:top}
.timetable th{background:#dcdfe0;color:#0b3a49}
.day-col{background:#f3f4f5;font-weight:700;width:150px}
.cell-subject{font-weight:700}
.cell-meta{font-size:13px;color:#2f3b40;margin-top:6px}
.legend{margin-top:18px;border-top:1px solid #eee;padding-top:10px;font-size:14px}
@media print {

  /* Page should be landscape */
  @page {
      size: A4 landscape;
      margin: 8mm;
  }

  body {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      visibility: hidden;
  }

  #print-area, #print-area * {
      visibility: visible;
  }

  #print-area {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
  }

  /* FIX 1 — remove scroll cutting */
  .table-wrap {
      overflow: visible !important;
      width: 100% !important;
  }

  /* FIX 2 — allow columns to shrink & wrap */
  .timetable {
      width: 100% !important;
      table-layout: fixed !important;
      border-collapse: collapse;
  }

  /* FIX 3 — wrap text inside cells */
  .timetable th, .timetable td {
      white-space: normal !important;
      word-wrap: break-word !important;
      overflow-wrap: break-word !important;
      font-size: 11px !important;
      padding: 6px !important;
  }

  /* FIX 4 — avoid breaking inside table */
  table, tr, td, th {
      page-break-inside: avoid !important;
  }

  /* Fix 5 — remove extra margins around table */
  .container {
      margin: 0 !important;
      padding: 0 !important;
      width: 100% !important;
  }
}

</style>

</head>
<body>
<div class="container">

<div class="header">
    <div>
        <label class="small">Semester</label><br>
        <select id="sem" class="select" onchange="onChange()">
            <option value="">-- Sem --</option>
            <?php foreach($semesters as $s): ?>
            <option value="<?=$s?>" <?=($s==$selected_sem)?'selected':''?>>Semester <?=$s?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="small">Class</label><br>
        <select id="class_id" class="select" onchange="onChange()">
            <option value="">-- Class --</option>
            <?php foreach($classes as $c): ?>
            <option value="<?=$c['id']?>" <?=($c['id']==$selected_class)?'selected':''?>><?=h($c['name'])?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label class="small">Batch</label><br>
        <select id="batch" class="select" onchange="onChange()">
            <option value="">-- Batch --</option>
            <?php foreach(year_list() as $y): 
                $b = $y . "-" . ($y+1);
            ?>
            <option value="<?=$b?>" <?=($selected_batch==$b)?'selected':''?>><?=$b?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-left:auto">
        <button class="print-btn" onclick="window.print()">Print</button>
        <a href="dashboard.php"><button class="back-btn">Back</button></a>
    </div>
</div>

<?php if(!$selected_sem || !$selected_class || !$selected_batch): ?>
    <div class="small">Please select Semester, Class and Batch.</div>
<?php else: ?>

<div id="print-area">
<div class="small" style="margin-bottom:8px">
    <strong>Class:</strong>
    <?php foreach($classes as $c) if($c['id']==$selected_class) echo h($c['name']); ?>
    &nbsp;|&nbsp;<strong>Semester:</strong> <?=h($selected_sem)?>
    &nbsp;|&nbsp;<strong>Batch:</strong> <?=h($selected_batch)?>
</div>

<?php if(empty($time_slots)): ?>
    <div class="small">No timetable found.</div>
<?php else: ?>

<div class="table-wrap">
<table class="timetable">
<thead>
<tr><th class="day-col">Day / Time</th>
<?php foreach($time_slots as $slot): ?>
<th><?=h(shortt($slot['start_time']))?> - <?=h(shortt($slot['end_time']))?></th>
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
    $subject = h($r['subject_name']);
    $type = h(ucfirst($r['type']));
    $lect = h($r['lecturer_name']);
    $cell = "<div class='cell-subject'>$subject</div>";

    if(!empty($lect))
        $cell .= "<div class='cell-meta'>$lect • $type</div>";

} else { $cell = "&nbsp;"; }
?>
<td><?= $cell ?></td>
<?php endforeach; ?>

</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="legend">
<strong>Subject — Lecturer</strong>
<div style="margin-top:8px">
<?php
$pairs = [];
foreach($rows as $r){
    $key = trim($r['subject_name']).'||'.trim($r['lecturer_name']);
    if(!isset($pairs[$key]))
        $pairs[$key] = [
            's'=>trim($r['subject_name']),
            'l'=>trim($r['lecturer_name'])
        ];
}

echo "<table style='width:100%;border-collapse:collapse'>";
foreach($pairs as $p){
    echo "<tr>
            <td style='padding:6px;border-bottom:1px solid #eee'>".h($p['s'])."</td>
            <td style='padding:6px;border-bottom:1px solid #eee'>".h($p['l'])."</td>
          </tr>";
}
echo "</table>";
?>
</div></div>

<?php endif; ?>
</div>

<?php endif; ?>

</div>

<script>
function onChange(){
    const s = document.getElementById('sem').value;
    const c = document.getElementById('class_id').value;
    const b = document.getElementById('batch').value;

    if(s && c && b){
        window.location = "timetable_view.php?sem="+s+"&class_id="+c+"&batch="+b;
    } else if(s && c){
        window.location = "timetable_view.php?sem="+s+"&class_id="+c;
    } else if(s){
        window.location = "timetable_view.php?sem="+s;
    }
}
</script>

</body>
</html>