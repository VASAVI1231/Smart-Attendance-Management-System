<?php
//helpers.php
require_once 'config.php'; // you must have $mysqli and session_start() in config.php

function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function is_logged_in(){ return !empty($_SESSION['user_id']); }

function current_user(){
    global $mysqli;
    if(!is_logged_in()) return null;
    $id = (int)$_SESSION['user_id'];
    $r = $mysqli->query("SELECT id,username,full_name,role,email,branch FROM users WHERE id=$id LIMIT 1");
    return $r ? $r->fetch_assoc() : null;
}

function require_login(){ if(!is_logged_in()){ header('Location: index.php'); exit; } }

function flash($msg){ $_SESSION['flash'] = $msg; }
function get_flash(){ if(!empty($_SESSION['flash'])){ $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m; } return ''; }

function log_action($action,$target_type=null,$target_id=null,$details=null){
    global $mysqli;
    $u = current_user(); $uid = $u['id'] ?? null; $uname = $u['username'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $mysqli->prepare("INSERT INTO activity_log (user_id,username,action,target_type,target_id,details,ip_address) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('isssiss',$uid,$uname,$action,$target_type,$target_id,$details,$ip);
    $stmt->execute();
}

function academic_year_options($count_prev = 3, $count_next = 1){
    // returns array like ['2022-2023','2023-2024',...]
    $current = (int)date('Y');
    $arr = [];
    for($i = $current - $count_prev; $i <= $current + $count_next; $i++){
        $arr[] = sprintf('%04d-%04d', $i, $i+1);
    }
    return $arr;
}

function year_list(){
    $start = 2020;
    $end = 2100;
    $years = [];
    for($y = $start; $y <= $end; $y++){
        $years[] = $y;
    }
    return $years;
}
?>