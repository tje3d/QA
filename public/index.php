<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = Database::getInstance()->getConnection();

// Get categories with question count
$stmt = $pdo->query('
    SELECT c.*, COUNT(q.id) as question_count 
    FROM categories c 
    LEFT JOIN questions q ON c.id = q.category_id 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
');
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سامانه پرسش و پاسخ</title>
    <meta name="description" content="پاسخ به سوالات در دسته‌بندی‌های مختلف">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { vazir: ['Vazirmatn', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' },
                        surface: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a' }
                    },
                    boxShadow: {
                        'glass': '0 4px 30px rgba(0, 0, 0, 0.1)',
                        'soft': '0 10px 40px -10px rgba(0,0,0,0.08)',
                        'xl-soft': '0 20px 40px -10px rgba(0,0,0,0.1)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-surface-50 selection:bg-primary-100 selection:text-primary-900 flex items-center justify-center p-4 md:p-8"
      style="background-image: radial-gradient(circle at 10% 20%, rgb(240, 253, 244) 0%, rgb(255, 255, 255) 90%);">

    <!-- Main Content -->
    <main class="w-full max-w-7xl mx-auto relative z-10">
        <?php if (empty($categories)): ?>
            <!-- Empty State -->
            <div class="bg-white/60 backdrop-blur-md rounded-3xl border border-white p-12 text-center shadow-xl-soft max-w-lg mx-auto animate-slide-up">
                <div class="w-20 h-20 bg-surface-100 rounded-[2rem] flex items-center justify-center mx-auto mb-6 transform rotate-3">
                    <svg class="w-10 h-10 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-surface-900 mb-3">هنوز چالشی نیست!</h2>
                <p class="text-surface-500 leading-relaxed">در حال حاضر دسته‌بندی‌ای برای نمایش وجود ندارد.</p>
            </div>
        <?php else: ?>
            <!-- Categories Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories as $index => $cat): ?>
                    <div class="group relative animate-slide-up" style="animation-delay: <?= $index * 100 ?>ms">
                        <div class="absolute inset-0 bg-primary-500 rounded-[2rem] opacity-0 group-hover:opacity-5 transform group-hover:scale-105 transition-all duration-500"></div>
                        
                        <a href="/public/answer.php?category=<?= $cat['id'] ?>" 
                           class="block h-full bg-white rounded-[2rem] border border-surface-200/60 p-6 md:p-8 shadow-soft hover:shadow-xl-soft hover:-translate-y-2 transition-all duration-300 relative z-10 overflow-hidden">
                            
                            <!-- Card Decoration -->
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-primary-50 to-transparent rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                            <div class="flex items-start justify-between mb-6 relative">
                                <div class="w-14 h-14 bg-surface-50 rounded-2xl flex items-center justify-center group-hover:bg-primary-500 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-primary-500/30">
                                    <svg class="w-7 h-7 text-surface-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <span class="px-4 py-1.5 bg-surface-100 text-surface-600 rounded-full text-xs font-bold border border-surface-200 group-hover:border-primary-200 group-hover:text-primary-700 group-hover:bg-primary-50 transition-colors">
                                    <?= $cat['question_count'] ?> سوال
                                </span>
                            </div>

                            <h2 class="text-xl font-black text-surface-900 mb-3 group-hover:text-primary-600 transition-colors relative">
                                <?= htmlspecialchars($cat['title']) ?>
                            </h2>
                            
                            <p class="text-surface-500 text-sm leading-7 line-clamp-2 mb-8 h-14 relative">
                                <?= htmlspecialchars($cat['description'] ?: 'بدون توضیحات تکمیلی برای این بخش.') ?>
                            </p>

                            <div class="flex items-center text-primary-600 font-bold text-sm group/btn">
                                <span class="group-hover/btn:ml-2 transition-all">شروع پاسخ‌دهی</span>
                                <svg class="w-4 h-4 mr-2 rotate-180 transition-transform duration-300 transform group-hover/btn:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
