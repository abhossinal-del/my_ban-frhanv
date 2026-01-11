<?php
require 'db.php';

$message = '';
$target_dir = "uploads/";

if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// معالجة الحذف
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // جلب مسار الملف لحذفه من الخادم أيضاً
    $stmt = $pdo->prepare("SELECT file_url FROM materials WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    
    if ($file && file_exists($file['file_url'])) {
        unlink($file['file_url']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "✅ تم حذف المحاضرة بنجاح!";
    }
}

// معالجة الإضافة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_material'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $notes = $_POST['notes'];
    $type = $_POST['type'];
    $semester = $_POST['semester'];
    
    $file_url = '';
    if (isset($_FILES["file_to_upload"]) && $_FILES["file_to_upload"]["error"] == 0) {
        $file_name = time() . '_' . basename($_FILES["file_to_upload"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $target_file)) {
            $file_url = $target_file;
        }
    } else {
        $file_url = $_POST['file_url'] ?? '#';
    }

    if (!empty($title)) {
        $sql = "INSERT INTO materials (title, description, notes, type, semester, file_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $notes, $type, $semester, $file_url]);
        $message = "✅ تمت إضافة المحاضرة بنجاح!";
    }
}

// جلب كافة المحاضرات
$stmt = $pdo->query("SELECT * FROM materials ORDER BY created_at DESC");
$materials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم الشاملة - إدارة المحاضرات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; direction: rtl; }
        .container { max-width: 1000px; margin: auto; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h2 { color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-top: 0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group.full { grid-column: span 2; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #4b5563; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        .btn-add { background: #2563eb; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-add:hover { background: #1d4ed8; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: right; border-bottom: 1px solid #e5e7eb; }
        th { background: #f8fafc; color: #64748b; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: bold; padding: 5px 10px; border: 1px solid #ef4444; border-radius: 5px; transition: 0.3s; }
        .btn-delete:hover { background: #ef4444; color: white; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-lecture { background: #dbeafe; color: #1e40af; }
        .badge-summary { background: #fef3c7; color: #92400e; }
        .alert { padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .nav-links { margin-bottom: 20px; }
        .nav-links a { text-decoration: none; color: #2563eb; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-eye"></i> عرض الموقع للطلاب</a>
        </div>

        <?php if ($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>

        <!-- نموذج الإضافة -->
        <div class="card">
            <h2><i class="fas fa-plus-circle"></i> إضافة محاضرة جديدة</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>عنوان المحاضرة</label>
                        <input type="text" name="title" required placeholder="مثال: مقدمة في البرمجة">
                    </div>
                    <div class="form-group">
                        <label>النوع</label>
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
                        <label>رفع ملف (PDF, Word...)</label>
                        <input type="file" name="file_to_upload">
                    </div>
                    <div class="form-group full">
                        <label>الوصف</label>
                        <textarea name="description" rows="2" required></textarea>
                    </div>
                    <div class="form-group full">
                        <label>ملاحظات إضافية</label>
                        <textarea name="notes" rows="2"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_material" class="btn-add">حفظ وإضافة</button>
            </form>
        </div>

        <!-- قائمة المحاضرات الحالية -->
        <div class="card">
            <h2><i class="fas fa-list"></i> المحاضرات الحالية</h2>
            <table>
                <thead>
                    <tr>
                        <th>العنوان</th>
                        <th>النوع</th>
                        <th>الفصل</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $item): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                        <td><span class="badge badge-<?= $item['type'] ?>"><?= $item['type'] ?></span></td>
                        <td>الفصل <?= $item['semester'] ?></td>
                        <td>
                            <a href="?delete=<?= $item['id'] ?>" class="btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذه المحاضرة؟')">
                                <i class="fas fa-trash"></i> حذف
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
