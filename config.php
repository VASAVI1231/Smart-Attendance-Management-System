<?php
// config.php - simple config for DB and email
session_start();

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';    // XAMPP default
$DB_NAME = 'attendance';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if($mysqli->connect_errno){
    die("DB Connect error: ".$mysqli->connect_error);
}
$mysqli->set_charset("utf8");

/* Email (PHPMailer or fallback mail)
   If you use PHPMailer put app password in SMTP_PASS.
*/
define('SMTP_HOST','smtp.gmail.com');
define('SMTP_PORT',587);
define('SMTP_USER','vasavipeddada43@gmail.com'); // your email
define('SMTP_PASS','qnhz jkti nyxm izki');    // replace with app password
define('SMTP_FROM_NAME','PR Govt College Attendance');
?>