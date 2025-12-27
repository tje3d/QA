<?php
if (!isset($currentPage)) $currentPage = '';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'پنل مدیریت' ?> | سامانه پرسش و پاسخ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        vazir: ['Vazirmatn', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f7ff',
                            100: '#ebf0fe',
                            200: '#ced9fd',
                            300: '#b1c2fb',
                            400: '#7694f8',
                            500: '#3b66f5',
                            600: '#355cdc',
                            700: '#2c4db8',
                            800: '#233d93',
                            900: '#1d3278',
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 0.5px, transparent 0.5px);
            background-size: 24px 24px;
        }
        .stat-card, .action-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
        .btn-primary {
            @apply inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-brand-500/20 active:scale-95 disabled:opacity-50 disabled:pointer-events-none;
        }
        .btn-secondary {
            @apply inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-bold rounded-xl transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none;
        }
        .btn-danger {
            @apply inline-flex items-center gap-2 px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-red-500/20 active:scale-95 disabled:opacity-50 disabled:pointer-events-none;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen">
    <!-- Top Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-4 md:gap-10">
                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-500 hover:text-brand-600 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-brand-600 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-500/20 rotate-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-black text-slate-900 leading-none">پنل مدیریت</span>
                            <span class="text-[10px] text-brand-600 font-bold uppercase tracking-tighter mt-1">داشبورد نسخه ۱.۱.۰</span>
                        </div>
                    </div>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-2">
                        <a href="index.php" class="px-5 py-2.5 text-sm font-bold <?= $currentPage === 'dashboard' ? 'text-brand-700 bg-brand-50' : 'text-slate-500 hover:text-brand-600 hover:bg-slate-50' ?> rounded-2xl transition-all">داشبورد</a>
                        <a href="categories.php" class="px-5 py-2.5 text-sm font-bold <?= $currentPage === 'categories' ? 'text-brand-700 bg-brand-50' : 'text-slate-500 hover:text-brand-600 hover:bg-slate-50' ?> rounded-2xl transition-all">دسته‌بندی‌ها</a>
                        <a href="questions.php" class="px-5 py-2.5 text-sm font-bold <?= $currentPage === 'questions' ? 'text-brand-700 bg-brand-50' : 'text-slate-500 hover:text-brand-600 hover:bg-slate-50' ?> rounded-2xl transition-all">سوالات</a>
                        <a href="answers.php" class="px-5 py-2.5 text-sm font-bold <?= $currentPage === 'answers' ? 'text-brand-700 bg-brand-50' : 'text-slate-500 hover:text-brand-600 hover:bg-slate-50' ?> rounded-2xl transition-all">پاسخ‌ها</a>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="../public/" target="_blank" title="مشاهده سایت" class="w-11 h-11 flex items-center justify-center rounded-2xl text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                    <div class="w-px h-6 bg-slate-200 mx-1"></div>
                    <a href="logout.php" title="خروج" class="w-11 h-11 flex items-center justify-center rounded-2xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden border-t border-slate-100 bg-white p-4 space-y-2 shadow-xl" x-cloak>
            <a href="index.php" class="block px-5 py-4 text-sm font-bold <?= $currentPage === 'dashboard' ? 'text-brand-700 bg-brand-50' : 'text-slate-600 hover:bg-slate-50' ?> rounded-2xl">داشبورد</a>
            <a href="categories.php" class="block px-5 py-4 text-sm font-bold <?= $currentPage === 'categories' ? 'text-brand-700 bg-brand-50' : 'text-slate-600 hover:bg-slate-50' ?> rounded-2xl transition-all">دسته‌بندی‌ها</a>
            <a href="questions.php" class="block px-5 py-4 text-sm font-bold <?= $currentPage === 'questions' ? 'text-brand-700 bg-brand-50' : 'text-slate-600 hover:bg-slate-50' ?> rounded-2xl transition-all">سوالات</a>
            <a href="answers.php" class="block px-5 py-4 text-sm font-bold <?= $currentPage === 'answers' ? 'text-brand-700 bg-brand-50' : 'text-slate-600 hover:bg-slate-50' ?> rounded-2xl transition-all">پاسخ‌ها</a>
        </div>
    </nav>
