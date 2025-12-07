<?php
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (adminLogin($username, $password)) {
        header('Location: /admin/index.php');
        exit;
    } else {
        $error = 'نام کاربری یا رمز عبور اشتباه است';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود مدیر | سامانه پرسش و پاسخ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { vazir: ['Vazirmatn', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d' },
                        dark: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 800: '#1e293b', 900: '#0f172a' }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-dark-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-500 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-dark-900">پنل مدیریت</h1>
            <p class="text-dark-300 mt-1">وارد حساب کاربری خود شوید</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-dark-100 p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-dark-800 text-sm font-medium mb-2">نام کاربری</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 placeholder-dark-300 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                </div>
                <div>
                    <label class="block text-dark-800 text-sm font-medium mb-2">رمز عبور</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 placeholder-dark-300 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                </div>
                <button type="submit"
                    class="w-full py-3.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition duration-200 shadow-sm hover:shadow">
                    ورود به پنل
                </button>
            </form>
        </div>

        <p class="text-center text-dark-300 text-sm mt-6">
            سامانه پرسش و پاسخ &copy; <?= date('Y') ?>
        </p>
    </div>
</body>
</html>
