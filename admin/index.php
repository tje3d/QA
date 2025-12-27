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

$pageTitle = 'داشبورد مدیریت';
$currentPage = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-10">
        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">خوش آمدید، مدیر عزیز 👋</h1>
                <p class="text-slate-500 mt-2 font-medium">آخرین آمار و فعالیت‌های سامانه در یک نگاه</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="stat-card bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm shadow-slate-200/50 group">
                <div class="flex flex-col gap-6">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-1">دسته‌بندی‌ها</p>
                        <p class="text-4xl font-black text-slate-900"><?= $categoryCount ?></p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm shadow-slate-200/50 group">
                <div class="flex flex-col gap-6">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-1">سوالات</p>
                        <p class="text-4xl font-black text-slate-900"><?= $questionCount ?></p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm shadow-slate-200/50 group">
                <div class="flex flex-col gap-6">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-1">پاسخ‌نامه‌ها</p>
                        <p class="text-4xl font-black text-slate-900"><?= $attemptCount ?></p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm shadow-slate-200/50 group">
                <div class="flex flex-col gap-6">
                    <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-500 text-sm font-bold mb-1">کاربران فعال</p>
                        <p class="text-4xl font-black text-slate-900"><?= $sessionCount ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Quick Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-[2.5rem] p-10 border border-slate-200 shadow-xl shadow-slate-200/40 relative overflow-hidden h-full">
                    <!-- Decorative element -->
                    <div class="absolute -top-12 -right-12 w-24 h-24 bg-brand-50 rounded-full blur-2xl opacity-60"></div>
                    
                    <h2 class="text-xl font-black text-slate-900 mb-8 flex items-center gap-3">
                        <span class="w-2 h-8 bg-brand-500 rounded-full"></span>
                        دسترسی سریع
                    </h2>
                    
                    <div class="space-y-4 relative z-10">
                        <a href="categories.php" class="flex items-center justify-between p-5 bg-white hover:bg-brand-600 hover:text-white rounded-2xl transition-all duration-300 group border border-slate-200 shadow-sm hover:shadow-lg hover:shadow-brand-500/20">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-brand-50 group-hover:bg-brand-500 rounded-xl flex items-center justify-center transition-colors">
                                    <svg class="w-5 h-5 text-brand-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <span class="font-bold">دسته‌بندی جدید</span>
                            </div>
                            <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>

                        <a href="questions.php" class="flex items-center justify-between p-5 bg-white hover:bg-brand-600 hover:text-white rounded-2xl transition-all duration-300 group border border-slate-200 shadow-sm hover:shadow-lg hover:shadow-brand-500/20">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-brand-50 group-hover:bg-brand-500 rounded-xl flex items-center justify-center transition-colors">
                                    <svg class="w-5 h-5 text-brand-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <span class="font-bold">سوال جدید</span>
                            </div>
                            <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                    </div>

                    <div class="mt-12 p-6 bg-brand-50 rounded-[2rem] border border-brand-100">
                        <p class="text-brand-800 text-xs font-bold uppercase tracking-wider mb-2">راهنمای سریع</p>
                        <p class="text-brand-600 text-sm leading-relaxed">برای مدیریت بهتر سوالات، ابتدا دسته‌بندی مناسب را ایجاد کنید.</p>
                    </div>
                </div>
            </div>

            <!-- Placeholder for Recent Activity or more stats -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-[2.5rem] p-10 border border-slate-200 shadow-sm h-full flex flex-center items-center justify-center text-center">
                    <div>
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">فعالیت‌های اخیر</h3>
                        <p class="text-slate-500 mt-2">گزارش فعالیت‌های اخیر در نسخه‌های بعدی اضافه خواهد شد.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
