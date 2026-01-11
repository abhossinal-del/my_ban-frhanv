<?php

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}


require 'db.php';

$message = '';
$page = $_GET['page'] ?? 'home';

// معالجة الحذف
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "✅ تم حذف المحاضرة بنجاح!";
    }
}

// معالجة الإضافة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_material'])) {
    $title = $_POST['title'];
    $doctor_name = $_POST['doctor_name'];
    $description = $_POST['description'];
    $notes = $_POST['notes'];
    $type = $_POST['type'];
    $semester = $_POST['semester'];
    
    $file_url = '';
    if (isset($_FILES["file_to_upload"]) && $_FILES["file_to_upload"]["error"] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES["file_to_upload"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $target_file)) {
            $file_url = $target_file;
        }
    }

    $sql = "INSERT INTO materials (title, doctor_name, description, notes, type, semester, file_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $doctor_name, $description, $notes, $type, $semester, $file_url]);
    $message = "✅ تمت إضافة المحاضرة بنجاح!";
    $page = 'home'; // العودة للرئيسية بعد الإضافة
}

// جلب البيانات
$stmt = $pdo->query("SELECT * FROM materials ORDER BY created_at DESC");
$materials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المحاضرات المتكامل</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #2563eb; --secondary: #64748b; --bg: #f8fafc; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--bg); margin: 0; direction: rtl; }
        
        /* شريط التنقل */
        .navbar { background: white; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .logo { font-size: 24px; font-weight: bold; color: var(--primary); text-decoration: none; }
        
        /* القائمة المنسدلة */
        .dropdown { position: relative; display: inline-block; }
        .dropbtn { background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; display: flex; align-items: center; gap: 8px; }
        .dropdown-content { display: none; position: absolute; left: 0; background-color: white; min-width: 180px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .dropdown-content a { color: #333; padding: 12px 16px; text-decoration: none; display: block; transition: 0.3s; }
        .dropdown-content a:hover { background-color: #f1f5f9; color: var(--primary); }
        .dropdown:hover .dropdown-content { display: block; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .alert { padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; text-align: center; }

        /* تصميم البطاقات */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 5px solid var(--primary); }
        .card h3 { margin: 0 0 10px 0; color: #1e293b; }
        .doctor { color: #64748b; font-size: 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 5px; }
        .notes-box { background: #fffbeb; border-right: 4px solid #f59e0b; padding: 10px; margin: 10px 0; font-size: 13px; color: #92400e; border-radius: 4px; }
        
        /* تصميم النموذج */
        .form-card { background: white; padding: 30px; border-radius: 15px; max-width: 700px; margin: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #334155; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        .submit-btn { background: var(--primary); color: white; border: none; padding: 15px; border-radius: 8px; width: 100%; cursor: pointer; font-size: 16px; font-weight: bold; }

        /* جدول الحذف */
        .manage-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; border-collapse: collapse; }
        .manage-table th, .manage-table td { padding: 15px; text-align: right; border-bottom: 1px solid #f1f5f9; }
        .manage-table th { background: #f8fafc; color: #64748b; }
        .delete-link { color: #ef4444; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo"><i class="fas fa-graduation-cap"></i> بوابة المحاضرات</a>
        
        <div class="dropdown">
            <button class="dropbtn">خيارات الإدارة <i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="index.php?page=home"><i class="fas fa-home"></i> عرض المحاضرات</a>
                <a href="index.php?page=add"><i class="fas fa-plus-circle"></i> إضافة محاضرة</a>
                <a href="index.php?page=manage"><i class="fas fa-tasks"></i> حذف محاضرات</a>
           <a href="logout.php" style="color:red;margin-right:15px">
    <i class="fas fa-sign-out-alt"></i> تسجيل خروج
</a>

            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($page == 'home'): ?>
            <h2 style="margin-bottom: 25px;">📚 المحاضرات المتاحة</h2>
            <div class="grid">
                <?php foreach ($materials as $item): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <div class="doctor"><i class="fas fa-user-tie"></i> د. <?= htmlspecialchars($item['doctor_name']) ?></div>
                    <p style="color: #475569; font-size: 14px;"><?= htmlspecialchars($item['description']) ?></p>
                    
                    <?php if (!empty($item['notes'])): ?>
                    <div class="notes-box">
                        <i class="fas fa-sticky-note"></i> <strong>ملاحظة:</strong> <?= htmlspecialchars($item['notes']) ?>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <span style="background: #e2e8f0; padding: 4px 10px; border-radius: 20px; font-size: 12px;"><?= $item['type'] ?></span>
                        <a href="<?= $item['file_url'] ?>" style="color: var(--primary); text-decoration: none; font-weight: bold;">تحميل <i class="fas fa-download"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($page == 'add'): ?>
            <div class="form-card">
                <h2 style="text-align: center; margin-top: 0;"><i class="fas fa-plus-circle"></i> إضافة مادة جديدة</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>اسم المحاضرة</label>
                        <input type="text" name="title" required placeholder="مثال: المحاضرة الأولى">
                    </div>
                    <div class="form-group">
                        <label>اسم الدكتور</label>
                        <input type="text" name="doctor_name" required placeholder="أدخل اسم الدكتور">
                    </div>
                    <div class="form-group">
                        <label>نوع المحاضرة</label>
                        <select name="type">
                            <option value="lecture">محاضرة</option>
                            <option value="summary">ملخص</option>
                            <option value="practical">عملي</option>
                            <option value="exam">اختبار</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>الفصل الدراسي</label>
                        <select name="semester">
                            <option value="1">الفصل الأول</option>
                            <option value="2">الفصل الثاني</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>وصف المحاضرة</label>
                        <textarea name="description" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>تسجيل ملاحظات عن المحاضرة</label>
                        <textarea name="notes" rows="3" placeholder="اكتب أي ملاحظات إضافية هنا..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>رفع ملف المحاضرة</label>
                        <input type="file" name="file_to_upload">
                    </div>
                    <button type="submit" name="add_material" class="submit-btn">حفظ البيانات</button>
                </form>
            </div>

        <?php elseif ($page == 'manage'): ?>
            <h2 style="margin-bottom: 25px;">⚙️ إدارة وحذف المحاضرات</h2>
            <table class="manage-table">
                <thead>
                    <tr>
                        <th>المحاضرة</th>
                        <th>الدكتور</th>
                        <th>النوع</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= htmlspecialchars($item['doctor_name']) ?></td>
                        <td><?= $item['type'] ?></td>
                        <td>
                            <a href="index.php?page=manage&delete=<?= $item['id'] ?>" class="delete-link" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                <i class="fas fa-trash"></i> حذف
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
