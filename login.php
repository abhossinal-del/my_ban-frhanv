<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->execute([$username, $password]);

    if ($stmt->rowCount() == 1) {
        $_SESSION['admin'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "❌ بيانات الدخول غير صحيحة";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تسجيل الدخول</title>
<style>
body{background:#f0f2f5;font-family:tahoma}
.login{max-width:400px;margin:100px auto;background:#fff;padding:30px;border-radius:10px}
input,button{width:100%;padding:10px;margin-top:10px}
button{background:#2563eb;color:#fff;border:none}
</style>
</head>
<body>


<div class="login">
<h2>موقع محاضراتي</h2>
<?php if($error) echo "<p>$error</p>"; ?>
<form method="POST">
<div style="text-align:center;margin-bottom:20px">
    <img src="mm.jpg" style="width:390px;border-radius:50%">
    <h3>مهندس: بشار فرحان</h3>
</div>
<input type="text" name="username" placeholder="اسم المستخدم" required>
<input type="password" name="password" placeholder="كلمة المرور" required>
<button>دخول</button>



</form>
</div>
</body>
</html>
