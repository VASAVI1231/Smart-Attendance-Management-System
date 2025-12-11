<?php
//index.php
require_once 'helpers.php';
if(is_logged_in()){ header('Location: dashboard.php'); exit; }
$flash = get_flash();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Smart Attendance & Timetable Management System</title>
<link rel="stylesheet" href="style.css">

<!-- GOOGLE FONTS -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    overflow-x: hidden;
}

/* ✨ Animated Gradient Background */
.bg-animation {
    width: 100%;
    height: 100vh;
    background: linear-gradient(-45deg, #004aad, #0cc0df, #008cff, #00e1ff);
    background-size: 400% 400%;
    animation: gradientMove 10s ease infinite;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* 🌟 Floating Bubble Animation */
.bubble {
    position: absolute;
    bottom: -100px;
    background: rgba(255,255,255,0.25);
    border-radius: 50%;
    opacity: 0.7;
    animation: rise 10s infinite ease-in;
}

@keyframes rise {
    0% { transform: translateY(0) scale(1); opacity: 0.7; }
    50% { opacity: 0.9; }
    100% { transform: translateY(-120vh) scale(1.4); opacity: 0; }
}

/* ✨ Title */
.project-title {
    color: white;
    font-size: 26px;
    font-weight: 700;
    text-align: center;
    letter-spacing: 1px;
    margin-bottom: 25px;
    text-shadow: 0 3px 10px rgba(0,0,0,0.4);
}

/* ✨ Login Card Stylish */
.login-card {
    width: 450px;
    padding: 30px;
    background: rgba(255,255,255,0.22);
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    border-radius: 18px;
    animation: floatCard 3s ease-in-out infinite;
}

@keyframes floatCard {
    0%   { transform: translateY(-5px); }
    50%  { transform: translateY(5px); }
    100% { transform: translateY(-5px); }
}

.login-card h2 {
    text-align: center;
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 20px;
}

/* Inputs */
input, select {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: none;
    outline: none;
    background: rgba(255,255,255,0.9);
    font-size: 15px;
}

/* Login Button */
.login-btn {
    width: 100%;
    background: #004aad;
    color: white;
    padding: 12px;
    border-radius: 10px;
    font-size: 17px;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

.login-btn:hover {
    background: #002f6e;
    transform: scale(1.03);
}

/* Flash Message */
.notice {
    background: rgba(255, 0, 0, 0.20);
    color: #fff;
    padding: 10px;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 8px;
    font-weight: 500;
}
</style>

</head>
<body>

<div class="bg-animation">

    <!-- 🌟 AUTO-GENERATED FLOATING BUBBLES -->
    <?php for($i=0; $i<18; $i++): ?>
        <div class="bubble" style="
            width: <?=rand(15,60)?>px;
            height: <?=rand(15,60)?>px;
            left: <?=rand(0,100)?>%;
            animation-duration: <?=rand(8,18)?>s;
            animation-delay: -<?=rand(0,10)?>s;
        "></div>
    <?php endfor; ?>


<!-- MAIN CONTAINER -->
<div>
    
    <!-- ⭐ PROJECT TITLE -->
    <div class="project-title">
        SMART ATTENDANCE MANAGEMENT <br>SYSTEM
    </div>

    <!-- FLASH MESSAGE -->
    <?php if($flash): ?>
    <div class="notice"><?=esc($flash)?></div>
    <?php endif; ?>

    <!-- ⭐ LOGIN CARD -->
    <div class="login-card">
        <h2>Login</h2>

        <form method="post" action="login_process.php">
            
            <label style="color:white;font-weight:500;">Username</label>
            <input type="text" name="username" required>

            <label style="color:white;font-weight:500;">Password</label>
            <input type="password" name="password" required>

            <label style="color:white;font-weight:500;">Role</label>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="hod">HOD</option>
                <option value="class">Class Lecturer</option>
                <option value="lecturer">Subject Lecturer</option>
            </select>

            <button class="login-btn">Login</button>
        </form>
    </div>

</div>

</div>

</body>
</html>