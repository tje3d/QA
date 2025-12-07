<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$pdo = Database::getInstance()->getConnection();

// Get stats
$categoryCount = $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$questionCount = $pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();
$attemptCount = $pdo->query('SELECT COUNT(*) FROM attempts')->fetchColumn();
$sessionCount = $pdo->query('SELECT COUNT(*) FROM user_sessions')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد | سامانه پرسش و پاسخ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { vazir: ['Vazirmatn', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d' },
                        dark: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 800: '#1e293b', 900: '#0f172a' }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-dark-50">
    <!-- Top Navigation -->
    <nav class="bg-white border-b border-dark-100 sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4 md:gap-8">
                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-dark-500 hover:text-dark-900 p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-primary-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="font-bold text-dark-900 hidden sm:inline">پنل مدیریت</span>
                    </div>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-1">
                        <a href="index.php" class="px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg">داشبورد</a>
                        <a href="categories.php" class="px-4 py-2 text-sm font-medium text-dark-400 hover:text-dark-800 hover:bg-dark-50 rounded-lg transition">دسته‌بندی‌ها</a>
                        <a href="questions.php" class="px-4 py-2 text-sm font-medium text-dark-400 hover:text-dark-800 hover:bg-dark-50 rounded-lg transition">سوالات</a>
                        <a href="answers.php" class="px-4 py-2 text-sm font-medium text-dark-400 hover:text-dark-800 hover:bg-dark-50 rounded-lg transition">پاسخ‌ها</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="../public/" target="_blank" class="text-dark-400 hover:text-dark-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                    <a href="logout.php" class="text-dark-400 hover:text-red-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" class="md:hidden border-t border-dark-100 bg-white" x-transition x-cloak>
            <div class="flex flex-col p-4 space-y-2">
                <a href="index.php" class="px-4 py-3 text-sm font-medium text-primary-600 bg-primary-50 rounded-xl">داشبورد</a>
                <a href="categories.php" class="px-4 py-3 text-sm font-medium text-dark-600 hover:bg-dark-50 rounded-xl transition">دسته‌بندی‌ها</a>
                <a href="questions.php" class="px-4 py-3 text-sm font-medium text-dark-600 hover:bg-dark-50 rounded-xl transition">سوالات</a>
                <a href="answers.php" class="px-4 py-3 text-sm font-medium text-dark-600 hover:bg-dark-50 rounded-xl transition">پاسخ‌ها</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 md:px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-dark-900">داشبورد</h1>
            <p class="text-dark-400 mt-1">خلاصه وضعیت سامانه</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white rounded-2xl p-6 border border-dark-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">دسته‌بندی‌ها</p>
                        <p class="text-2xl font-bold text-dark-900"><?= $categoryCount ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-dark-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">سوالات</p>
                        <p class="text-2xl font-bold text-dark-900"><?= $questionCount ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-dark-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">پاسخ‌نامه‌ها</p>
                        <p class="text-2xl font-bold text-dark-900"><?= $attemptCount ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-dark-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm">کاربران</p>
                        <p class="text-2xl font-bold text-dark-900"><?= $sessionCount ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 border border-dark-100">
            <h2 class="text-lg font-semibold text-dark-900 mb-4">دسترسی سریع</h2>
            <div class="flex flex-wrap gap-3">
                <a href="categories.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark-900 hover:bg-dark-800 text-white text-sm font-medium rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    دسته‌بندی جدید
                </a>
                <a href="questions.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-dark-50 text-dark-800 text-sm font-medium rounded-xl transition border border-dark-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    سوال جدید
                </a>
            </div>
        </div>
    </main>
</body>
</html>
