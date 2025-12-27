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
    <title>سامانه پرسش و پاسخ | آستان قدس رضوی</title>
    <meta name="description" content="سامانه جامع نظرسنجی و مسابقات آستان قدس رضوی">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { vazir: ['Vazirmatn', 'sans-serif'] },
                    colors: {
                        aqr: {
                            gold: '#D4AF37',
                            'gold-light': '#F3E5AB',
                            'gold-dark': '#B4941F',
                            green: '#0F4C3A', // Deep traditional green
                            'green-light': '#1A6B54',
                            'green-dark': '#083326',
                            cream: '#FFFDD0',
                            surface: '#F9FAFB'
                        }
                    },
                    backgroundImage: {
                        'islamic-pattern': "url('../assets/img/pattern-white.png')",
                    },
                    boxShadow: {
                        'gold': '0 10px 30px -10px rgba(212, 175, 55, 0.3)',
                        'card': '0 20px 40px -5px rgba(0, 0, 0, 0.2)',
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        [x-cloak] { display: none !important; }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .ornament-corner {
            position: absolute;
            width: 80px;
            height: 80px;
            background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h30c10 0 20 10 20 20v10c0 10 10 20 20 20h10' stroke='%23D4AF37' stroke-width='1' fill='none' opacity='0.4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="min-h-screen bg-aqr-green relative overflow-x-hidden selection:bg-aqr-gold selection:text-aqr-green-dark">

    <!-- Background Gradients for Depth -->
    <div class="fixed inset-0 pointer-events-none">
        <!-- Pattern Overlay -->
        <div class="absolute inset-0 bg-islamic-pattern opacity-[3%] bg-repeat"></div>
        
        <div class="absolute top-0 left-0 w-full h-[500px] bg-gradient-to-b from-black/30 to-transparent"></div>
        <div class="absolute bottom-0 left-0 w-full h-[500px] bg-gradient-to-t from-black/50 to-transparent"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[1000px] h-[1000px] bg-aqr-green-light/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Hanging Lanterns (Decorative) -->
    <div class="fixed top-0 inset-x-0 flex justify-center gap-40 md:gap-80 pointer-events-none z-10 opacity-80 hidden md:flex">
        <div class="h-32 w-[1px] bg-aqr-gold/50 relative">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full">
                <!-- Lantern SVG -->
                <svg width="40" height="60" viewBox="0 0 40 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 0L20 10" stroke="#D4AF37" stroke-width="2"/>
                    <path d="M10 15C10 12.2386 12.2386 10 15 10H25C27.7614 10 30 12.2386 30 15V45C30 47.7614 27.7614 50 25 50H15C12.2386 50 10 47.7614 10 45V15Z" fill="url(#lantern-gradient)" stroke="#D4AF37" stroke-width="2"/>
                    <path d="M20 50V60" stroke="#D4AF37" stroke-width="2"/>
                    <defs>
                        <radialGradient id="lantern-gradient" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(20 30) rotate(90) scale(20 10)">
                            <stop stop-color="#FFFDD0"/>
                            <stop offset="1" stop-color="#F3E5AB" stop-opacity="0.2"/>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
        </div>
        <div class="h-48 w-[1px] bg-aqr-gold/50 relative">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full">
                <svg width="40" height="60" viewBox="0 0 40 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 0L20 10" stroke="#D4AF37" stroke-width="2"/>
                    <path d="M10 15C10 12.2386 12.2386 10 15 10H25C27.7614 10 30 12.2386 30 15V45C30 47.7614 27.7614 50 25 50H15C12.2386 50 10 47.7614 10 45V15Z" fill="url(#lantern-gradient-2)" stroke="#D4AF37" stroke-width="2"/>
                    <path d="M20 50V60" stroke="#D4AF37" stroke-width="2"/>
                    <defs>
                        <radialGradient id="lantern-gradient-2" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(20 30) rotate(90) scale(20 10)">
                            <stop stop-color="#FFFDD0"/>
                            <stop offset="1" stop-color="#F3E5AB" stop-opacity="0.2"/>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="relative z-20 w-full max-w-7xl mx-auto px-4 py-8 pb-12 md:py-16 flex flex-col min-h-screen">
        
        <!-- Header Section -->
        <header class="text-center mb-16 md:mb-24 relative">
            <div class="inline-flex flex-col items-center justify-center p-8 bg-aqr-green-dark/40 backdrop-blur-md rounded-[3rem] border border-aqr-gold/30 shadow-2xl relative">
                <!-- Top Ornament -->
                <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-aqr-gold">
                    <svg width="40" height="20" viewBox="0 0 40 20" fill="currentColor">
                        <path d="M20 20C20 20 10 10 0 0H40C30 10 20 20 20 20Z"/>
                    </svg>
                </div>

                <!-- Logo Placeholder / Icon -->
                <div class="size-32 mb-4 bg-gradient-to-tr from-aqr-gold to-aqr-gold-light rounded-full flex items-center justify-center p-1 shadow-lg shadow-aqr-gold/20">
                    <div class="w-full h-full bg-aqr-green rounded-full flex items-center justify-center p-4">
                        <img src="../assets/img/logo-white.png" alt="آستان قدس رضوی" class="w-full h-full object-contain drop-shadow-md">
                    </div>
                </div>

                <h1 class="text-3xl md:text-5xl font-black text-white px-8 drop-shadow-md mb-2">آستان قـدس رضـوی</h1>
                <p class="text-aqr-gold-light text-sm md:text-base font-medium opacity-90">سامانه جامع نظرسنجی و خدمات الکترونیک</p>
                
                <!-- Bottom Ornament -->
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-aqr-gold rotate-180">
                    <svg width="40" height="20" viewBox="0 0 40 20" fill="currentColor">
                        <path d="M20 20C20 20 10 10 0 0H40C30 10 20 20 20 20Z"/>
                    </svg>
                </div>
            </div>
        </header>

        <!-- Categories Grid -->
        <?php if (empty($categories)): ?>
            <div class="flex-1 flex items-center justify-center">
                <div class="bg-white/10 backdrop-blur-md border border-aqr-gold/30 rounded-3xl p-12 text-center max-w-lg">
                    <p class="text-aqr-gold-light text-lg">در حال حاضر موردی برای نمایش وجود ندارد.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-10 items-stretch justify-center">
                <?php foreach ($categories as $index => $cat): ?>
                    <!-- Card -->
                    <div class="group relative flex flex-col h-full transform transition-all duration-500 hover:-translate-y-2 hover:z-30">
                        
                        <!-- Glow Effect behind card -->
                        <div class="absolute inset-4 bg-aqr-gold/20 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                        <div class="relative bg-[#F9F9F4] rounded-[2.5rem] p-1 shadow-card flex flex-col h-full overflow-hidden border border-white/50">
                            <!-- Inner Border Line -->
                            <div class="absolute inset-2 border border-aqr-gold/20 rounded-[2rem] pointer-events-none z-10"></div>
                            
                            <!-- Card Content Container -->
                            <div class="bg-white rounded-[2.25rem] flex-1 flex flex-col relative overflow-hidden">
                                
                                <!-- Top Decoration -->
                                <div class="absolute top-0 inset-x-0 h-24 bg-gradient-to-b from-aqr-gold/5 to-transparent"></div>

                                <!-- Badge (Question Count) -->
                                <div class="absolute top-6 right-6 z-20">
                                    <span class="inline-flex items-center justify-center h-8 px-4 bg-aqr-gold text-aqr-green-dark text-sm font-bold rounded-full shadow-md shadow-aqr-gold/30">
                                        <?= $cat['question_count'] ?> سوال
                                    </span>
                                </div>

                                <!-- Icon Area -->
                                <div class="pt-12 pb-4 flex justify-center relative z-10">
                                    <div class="w-20 h-20 bg-aqr-green rounded-2xl rotate-45 flex items-center justify-center shadow-lg shadow-aqr-green/30 group-hover:rotate-0 transition-all duration-500">
                                        <div class="-rotate-45 group-hover:rotate-0 transition-transform duration-500">
                                            <svg class="w-10 h-10 text-aqr-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Text Content -->
                                <div class="px-8 mt-4 text-center flex-1">
                                    <h3 class="text-xl font-black text-aqr-green-dark mb-3 leading-tight">
                                        <?= htmlspecialchars($cat['title']) ?>
                                    </h3>
                                    <p class="text-gray-500 text-sm leading-7 line-clamp-3 mb-6">
                                        <?= htmlspecialchars($cat['description'] ?: 'توضیحات مربوط به این بخش در این قسمت قرار می‌گیرد.') ?>
                                    </p>
                                </div>

                                <!-- Action Area (Button) -->
                                <div class="mt-auto relative">
                                    <!-- Decorative Divider -->
                                    <div class="flex items-center justify-center gap-2 mb-4 opacity-40">
                                        <div class="h-px w-12 bg-aqr-gold"></div>
                                        <div class="w-2 h-2 rotate-45 bg-aqr-gold"></div>
                                        <div class="h-px w-12 bg-aqr-gold"></div>
                                    </div>

                                    <a href="answer.php?category=<?= $cat['id'] ?>" 
                                       class="block bg-aqr-gold hover:bg-aqr-gold-dark py-5 text-center text-aqr-green-dark font-black text-xl transition-all duration-300 relative overflow-hidden group/btn shadow-[0_-4px_20px_rgba(0,0,0,0.1)]">
                                        <span class="relative z-10 flex items-center justify-center gap-3">
                                            شروع پاسخ‌دهی
                                            <svg class="w-6 h-6 transition-transform duration-300 group-hover/btn:-translate-x-2 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                            </svg>
                                        </span>
                                        <!-- Pattern Overlay on Button -->
                                        <div class="absolute inset-0 opacity-20 bg-islamic-pattern bg-[length:100px_100px]"></div>
                                        <!-- Glow effect on hover -->
                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover/btn:animate-[shimmer_1.5s_infinite]"></div>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer Copyright -->
    <footer class="absolute bottom-4 left-0 right-0 text-center z-10">
        <p class="text-aqr-gold-light/60 text-xs text-shadow-sm font-light">طراحی و توسعه توسط مدیریت آمار و فناوری اطلاعات</p>
    </footer>

</body>
</html>
