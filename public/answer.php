<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = Database::getInstance()->getConnection();
$categoryId = $_GET['category'] ?? null;
$attemptId = $_GET['attempt'] ?? null;

if (!$categoryId) {
    header('Location: /public/');
    exit;
}

// Get category
$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute([(int)$categoryId]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: /public/');
    exit;
}

// Get questions
$stmt = $pdo->prepare('SELECT * FROM questions WHERE category_id = ? ORDER BY question_group ASC, sort_order ASC, id ASC');
$stmt->execute([(int)$categoryId]);
$questions = $stmt->fetchAll();

// Parse JSON options
foreach ($questions as &$q) {
    $q['options'] = $q['options'] ? json_decode($q['options'], true) : [];
}

$questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['title']) ?> | سامانه پرسش و پاسخ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="/assets/js/iran_cities.js"></script>
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
                        'inner-light': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
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
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Smooth transitions for all interactive elements */
        .btn-transition { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-hover:hover { transform: translateY(-2px); }
        .btn-active:active { transform: translateY(0); }
    </style>
</head>
<body class="h-screen bg-surface-50 overflow-hidden selection:bg-primary-100 selection:text-primary-900" 
      x-data="answerApp()" 
      x-init="init()"
      style="background-image: radial-gradient(circle at 10% 20%, rgb(240, 253, 244) 0%, rgb(255, 255, 255) 90%);">
    
    <!-- Attempt Selection Modal -->
    <div x-show="showAttemptModal" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 backdrop-blur-none"
         x-transition:enter-end="opacity-100 backdrop-blur-sm"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-surface-900/30 backdrop-blur-sm transition-opacity"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="bg-gradient-to-br from-primary-600 to-primary-800 p-8 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-10 -mt-10 blur-2xl"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-primary-400/20 rounded-full -ml-8 -mb-8 blur-xl"></div>
                <h3 class="text-2xl font-black text-white relative z-10">انتخاب پاسخ‌نامه</h3>
                <p class="text-primary-100 text-sm mt-2 relative z-10">یک مسیر را انتخاب کنید</p>
            </div>

            <div class="p-6">
                <!-- Previous Attempts -->
                <div x-show="attempts.length > 0" class="mb-6">
                    <label class="block text-surface-500 text-xs font-bold uppercase tracking-wider mb-3">ادامه دهید</label>
                    <div class="space-y-3 max-h-56 overflow-y-auto custom-scrollbar pr-1">
                        <template x-for="att in attempts" :key="att.id">
                            <button @click="selectAttempt(att.id)"
                                class="w-full group text-right p-4 bg-surface-50 hover:bg-white border border-surface-200 hover:border-primary-200 hover:shadow-lg rounded-2xl transition-all duration-200 flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                        <span class="text-surface-900 font-bold text-sm" x-text="formatDate(att.created_at)"></span>
                                    </div>
                                    <span class="text-surface-400 text-xs mr-4" x-text="att.answered_count + ' پاسخ ثبت شده'"></span>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-white border border-surface-100 flex items-center justify-center text-surface-300 group-hover:text-primary-600 group-hover:border-primary-100 transition-colors">
                                    <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                
                <!-- New Attempt Button -->
                <button @click="createNewAttempt()"
                    class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-2xl shadow-lg shadow-primary-600/30 hover:shadow-primary-600/50 transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center gap-3 group">
                    <span class="bg-white/20 p-1 rounded-lg group-hover:bg-white/30 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </span>
                    شروع پاسخ‌نامه جدید
                </button>

                <a href="/public/" class="block text-center text-surface-400 hover:text-surface-700 text-sm mt-6 font-medium transition-colors">
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>

    <!-- Main Layout -->
    <div x-show="currentAttemptId" x-cloak class="h-full flex flex-col backdrop-blur-[2px]">
        <!-- Top Bar -->
        <header class="glass-panel z-40 relative">
            <div class="max-w-[1920px] mx-auto px-4 md:px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-4 md:gap-6">
                    <button @click="sidebarOpen = true" class="md:hidden text-surface-500 hover:text-surface-900 transition p-2 hover:bg-surface-100 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="/public/" class="hidden md:flex items-center justify-center w-10 h-10 rounded-xl bg-surface-50 text-surface-500 hover:bg-surface-100 hover:text-surface-900 transition border border-surface-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-primary-500 rounded-full hidden md:block"></span>
                            <h1 class="text-surface-900 font-black text-lg md:text-xl tracking-tight"><?= htmlspecialchars($category['title']) ?></h1>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Status Indicators -->
                    <div class="flex items-center px-4 py-2 bg-surface-50 rounded-full border border-surface-200/60 shadow-inner-light">
                        <div x-show="saving" class="flex items-center gap-2 text-amber-600">
                            <div class="w-3.5 h-3.5 border-2 border-amber-600 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs font-bold hidden sm:inline">در حال ذخیره...</span>
                        </div>
                        <div x-show="!saving && lastSaved" x-cloak class="flex items-center gap-2 text-emerald-600"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-90"
                             x-transition:enter-end="opacity-100 scale-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs font-bold hidden sm:inline">ذخیره شد</span>
                        </div>
                        <div x-show="!saving && !lastSaved" class="text-surface-400 text-xs font-medium">آماده</div>
                    </div>

                    <button @click="showAttemptModal = true" class="hidden md:flex items-center gap-2 px-4 py-2 text-sm font-medium text-surface-600 hover:text-surface-900 bg-transparent hover:bg-surface-100/50 rounded-xl transition border border-transparent hover:border-surface-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>تغییر پاسخ‌نامه</span>
                    </button>
                </div>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden relative">
            <!-- Mobile Sidebar Backdrop -->
            <div x-show="sidebarOpen" 
                x-transition:enter="transition-opacity duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="fixed inset-0 bg-surface-900/60 backdrop-blur-sm z-40 md:hidden" x-cloak></div>

            <!-- Sidebar -->
            <aside class="fixed inset-y-0 right-0 w-80 bg-white border-l border-surface-200 flex flex-col z-50 transition-transform duration-300 shadow-2xl md:shadow-none translate-x-full md:relative md:translate-x-0"
                :class="sidebarOpen ? '!translate-x-0' : ''">
                
                <!-- Sidebar Header Mobile -->
                <div class="md:hidden p-4 border-b border-surface-100 flex items-center justify-between">
                    <h2 class="font-bold text-surface-900">فهرست سوالات</h2>
                    <button @click="sidebarOpen = false" class="text-surface-500 hover:text-surface-900 bg-surface-50 p-2 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Progress Section -->
                <div class="p-6 border-b border-surface-100 bg-surface-50/50">
                    <div class="flex items-center justify-between text-sm mb-3">
                        <span class="text-surface-500 font-medium">پیشرفت کلی</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-surface-900 font-black text-lg" x-text="answeredCount"></span>
                            <span class="text-surface-400">/</span>
                            <span class="text-surface-400" x-text="questions.length"></span>
                        </div>
                    </div>
                    <div class="h-3 bg-surface-200 rounded-full overflow-hidden shadow-inner">
                        <div class="h-full bg-gradient-to-r from-primary-500 to-primary-400 rounded-full transition-all duration-700 ease-out shadow-lg shadow-primary-500/30"
                            :style="'width: ' + progressPercent + '%'">
                            <div class="w-full h-full opacity-30 bg-[length:10px_10px] bg-[linear-gradient(45deg,rgba(255,255,255,0.2)_25%,transparent_25%,transparent_50%,rgba(255,255,255,0.2)_50%,rgba(255,255,255,0.2)_75%,transparent_75%,transparent)] animate-[pulse_2s_linear_infinite]"></div>
                        </div>
                    </div>
                </div>

                <!-- Question List -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
                    <template x-for="group in questionGroups" :key="group">
                        <div class="bg-white rounded-2xl border border-surface-100 overflow-hidden shadow-sm">
                            <!-- Group Header -->
                            <button @click="toggleGroup(group)" 
                                class="w-full flex items-center justify-between px-5 py-3 hover:bg-surface-50 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-1.5 h-1.5 rounded-full bg-primary-500 shadow-sm"></div>
                                    <span class="text-surface-700 font-bold text-sm" x-text="group"></span>
                                    <span class="px-2 py-0.5 bg-surface-100 text-surface-500 text-[10px] rounded-md font-medium group-hover:bg-white transition-colors" x-text="getGroupAnsweredCount(group) + ' / ' + getGroupQuestions(group).length"></span>
                                </div>
                                <svg class="w-4 h-4 text-surface-400 transition-transform duration-300" 
                                    :class="!collapsedGroups.includes(group) ? 'rotate-180' : 'rotate-0'" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Group Questions -->
                            <div x-show="!collapsedGroups.includes(group)" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="p-2 bg-surface-50/50 border-t border-surface-100 space-y-1">
                                <template x-for="(q, idx) in getGroupQuestions(group)" :key="q.id">
                                    <button @click="goToQuestion(getQuestionIndex(q.id)); sidebarOpen = false"
                                        class="w-full text-right p-2.5 rounded-xl transition-all duration-200 flex items-start gap-3 group relative overflow-hidden"
                                        :class="currentIndex === getQuestionIndex(q.id) ? 'bg-white shadow-md ring-1 ring-primary-100' : 'hover:bg-white hover:shadow-sm'">
                                        
                                        <!-- Active Indicator -->
                                        <div x-show="currentIndex === getQuestionIndex(q.id)" class="absolute left-0 top-0 bottom-0 w-1 bg-primary-500"></div>

                                        <div class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold transition-colors"
                                            :class="isAnswered(q.id) 
                                                ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' 
                                                : (currentIndex === getQuestionIndex(q.id) ? 'bg-primary-50 text-primary-600 ring-1 ring-primary-200' : 'bg-surface-200 text-surface-500')">
                                            <template x-if="isAnswered(q.id)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </template>
                                            <template x-if="!isAnswered(q.id)">
                                                <span x-text="getQuestionIndex(q.id) + 1"></span>
                                            </template>
                                        </div>
                                        <p class="text-sm line-clamp-2 leading-relaxed pt-0.5"
                                           :class="currentIndex === getQuestionIndex(q.id) ? 'text-surface-900 font-medium' : 'text-surface-600'">
                                            <span x-text="q.question_text"></span>
                                        </p>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col overflow-hidden w-full md:w-auto relative" @click="sidebarOpen = false">
                <!-- Question Area -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 md:p-10 flex flex-col items-center">
                    <template x-if="currentQuestion">
                        <div class="w-full max-w-3xl mx-auto my-auto min-h-min flex flex-col justify-center animate-slide-up">
                            
                            <!-- Breadcrumb / Meta -->
                            <div class="flex items-center gap-3 text-sm mb-6 px-2 opacity-80 hover:opacity-100 transition-opacity">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white border border-surface-200 shadow-sm text-surface-600 font-bold" x-text="currentIndex + 1"></span>
                                <span class="text-surface-400">از</span>
                                <span class="text-surface-600 font-medium" x-text="questions.length"></span>
                                <div class="h-4 w-px bg-surface-300 mx-2"></div>
                                <span class="px-2.5 py-1 bg-surface-200 text-surface-600 text-xs rounded-full font-bold" x-text="currentQuestion.question_group || 'عمومی'"></span>
                            </div>

                            <!-- Question Card -->
                            <div class="bg-white rounded-[2rem] p-8 md:p-10 shadow-soft border border-surface-100 mb-6 relative overflow-visible">
                                <h2 class="text-2xl md:text-3xl font-black text-surface-900 leading-relaxed mb-2" x-text="currentQuestion.question_text"></h2>
                            </div>

                            <!-- Interaction Area -->
                            <div class="bg-white/60 backdrop-blur-md rounded-[2rem] p-8 shadow-glass border border-white/50">
                                
                                <!-- Boolean -->
                                <template x-if="currentQuestion.answer_type === 'boolean'">
                                    <div class="grid grid-cols-2 gap-6">
                                        <button @click="setAnswer('بله', true)"
                                            class="group relative py-8 rounded-2xl text-xl font-bold transition-all duration-300 border-2 overflow-hidden"
                                            :class="currentAnswer === 'بله' 
                                                ? 'bg-primary-500 border-primary-500 text-white shadow-xl shadow-primary-500/20 scale-[1.02]' 
                                                : 'bg-white border-surface-200 text-surface-700 hover:border-primary-300 hover:bg-primary-50'">
                                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                            <div class="relative flex flex-col items-center gap-3">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                                                     :class="currentAnswer === 'بله' ? 'bg-white/20' : 'bg-primary-100'">
                                                    <svg class="w-6 h-6" :class="currentAnswer === 'بله' ? 'text-white' : 'text-primary-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                                <span>بله</span>
                                            </div>
                                        </button>
                                        <button @click="setAnswer('خیر', true)"
                                            class="group relative py-8 rounded-2xl text-xl font-bold transition-all duration-300 border-2 overflow-hidden"
                                            :class="currentAnswer === 'خیر' 
                                                ? 'bg-rose-500 border-rose-500 text-white shadow-xl shadow-rose-500/20 scale-[1.02]' 
                                                : 'bg-white border-surface-200 text-surface-700 hover:border-rose-300 hover:bg-rose-50'">
                                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                            <div class="relative flex flex-col items-center gap-3">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                                                     :class="currentAnswer === 'خیر' ? 'bg-white/20' : 'bg-rose-100'">
                                                    <svg class="w-6 h-6" :class="currentAnswer === 'خیر' ? 'text-white' : 'text-rose-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </div>
                                                <span>خیر</span>
                                            </div>
                                        </button>
                                    </div>
                                </template>

                                <!-- Text -->
                                <template x-if="currentQuestion.answer_type === 'text'">
                                    <div class="relative">
                                        <input type="text" 
                                            x-model="currentAnswer"
                                            @input="debounceSave()"
                                            @keydown.enter="handleEnter($event)"
                                            class="w-full px-6 py-5 bg-white border-2 border-surface-200 rounded-2xl text-surface-900 text-lg font-medium placeholder-surface-400 focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all shadow-sm"
                                            :placeholder="currentQuestion.placeholder || 'پاسخ خود را بنویسید...'">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-surface-400 pointer-events-none">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </div>
                                    </div>
                                </template>

                                <!-- Textarea -->
                                <template x-if="currentQuestion.answer_type === 'textarea'">
                                    <div class="relative">
                                        <textarea 
                                            x-model="currentAnswer"
                                            @input="debounceSave()"
                                            @keydown.enter="handleEnter($event)"
                                            rows="6"
                                            class="w-full px-6 py-5 bg-white border-2 border-surface-200 rounded-2xl text-surface-900 text-lg font-medium placeholder-surface-400 focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all shadow-sm resize-none leading-loose"
                                            :placeholder="currentQuestion.placeholder || 'توضیحات خود را بنویسید...'"></textarea>
                                        <div class="absolute left-4 bottom-4 text-surface-400 pointer-events-none">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                        </div>
                                    </div>
                                </template>

                                <!-- Select -->
                                <template x-if="currentQuestion.answer_type === 'select'">
                                    <div class="space-y-3">
                                        <template x-for="(opt, idx) in currentQuestion.options" :key="idx">
                                            <button @click="setAnswer(opt, true)"
                                                class="w-full text-right px-6 py-5 rounded-2xl transition-all duration-200 flex items-center gap-5 border-2 group"
                                                :class="currentAnswer === opt 
                                                    ? 'bg-primary-50 border-primary-500 shadow-lg shadow-primary-500/10 z-10' 
                                                    : 'bg-white border-surface-200 hover:border-primary-300 hover:bg-surface-50'">
                                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all duration-200"
                                                    :class="currentAnswer === opt ? 'border-primary-500 scale-110' : 'border-surface-300 group-hover:border-primary-400'">
                                                    <div x-show="currentAnswer === opt" class="w-3 h-3 bg-primary-500 rounded-full shadow-sm" x-transition:enter="scale-0 duration-200" x-transition:leave="scale-0 duration-200"></div>
                                                </div>
                                                <span class="text-lg font-medium" :class="currentAnswer === opt ? 'text-surface-900' : 'text-surface-600'">
                                                    <span x-text="opt"></span>
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </template>

                                <!-- Multiselect -->
                                <template x-if="currentQuestion.answer_type === 'multiselect'">
                                    <div class="space-y-3">
                                        <template x-for="(opt, idx) in currentQuestion.options" :key="idx">
                                            <button @click="toggleMultiSelect(opt)"
                                                class="w-full text-right px-6 py-5 rounded-2xl transition-all duration-200 flex items-center gap-5 border-2 group"
                                                :class="isSelectedMulti(opt) 
                                                    ? 'bg-primary-50 border-primary-500 shadow-lg shadow-primary-500/10' 
                                                    : 'bg-white border-surface-200 hover:border-primary-300 hover:bg-surface-50'">
                                                <div class="w-6 h-6 rounded-lg border-2 flex items-center justify-center flex-shrink-0 transition-all duration-200"
                                                    :class="isSelectedMulti(opt) ? 'border-primary-500 bg-primary-500 scale-110' : 'border-surface-300 group-hover:border-primary-400'">
                                                    <svg x-show="isSelectedMulti(opt)" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                                <span class="text-lg font-medium" :class="isSelectedMulti(opt) ? 'text-surface-900' : 'text-surface-600'">
                                                    <span x-text="opt"></span>
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </template>

                                <!-- City/Province -->
                                <template x-if="currentQuestion.answer_type === 'city_province'">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="relative">
                                            <label class="block text-sm font-bold text-surface-600 mb-2">استان</label>
                                            <div class="relative">
                                                <select 
                                                    @change="setAnswer({ province: $event.target.value, city: '' }, false)"
                                                    class="w-full px-4 py-4 bg-white border-2 border-surface-200 rounded-2xl appearance-none text-surface-900 font-medium focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all shadow-sm">
                                                    <option value="">انتخاب استان...</option>
                                                    <template x-for="p in iranProvinces" :key="p.name">
                                                        <option :value="p.name" x-text="p.name" :selected="currentAnswer.province === p.name"></option>
                                                    </template>
                                                </select>
                                                <div class="absolute inset-y-0 left-0 flex items-center px-4 pointer-events-none text-surface-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <label class="block text-sm font-bold text-surface-600 mb-2">شهر</label>
                                            <div class="relative">
                                                <select 
                                                    @change="setAnswer({ province: currentAnswer.province, city: $event.target.value }, true)"
                                                    :disabled="!currentAnswer.province"
                                                    class="w-full px-4 py-4 bg-white border-2 border-surface-200 rounded-2xl appearance-none text-surface-900 font-medium focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all shadow-sm disabled:bg-surface-100 disabled:text-surface-400">
                                                    <option value="">انتخاب شهر...</option>
                                                    <template x-for="(c, idx) in currentProvinceCities" :key="idx">
                                                        <option :value="c" x-text="c" :selected="currentAnswer.city === c"></option>
                                                    </template>
                                                </select>
                                                <div class="absolute inset-y-0 left-0 flex items-center px-4 pointer-events-none text-surface-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Bottom Navigation -->
                <div class="flex-shrink-0 glass-panel border-t border-white/50 px-8 py-5 flex items-center justify-between z-30">
                    <button @click="prevQuestion()" :disabled="currentIndex === 0"
                        class="px-8 py-3 text-surface-600 font-bold rounded-xl transition-all flex items-center gap-2 hover:bg-surface-100 disabled:opacity-40 disabled:hover:bg-transparent cursor-pointer disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                        سوال قبلی
                    </button>
                    
                    <button @click="nextQuestion()" :disabled="currentIndex === questions.length - 1"
                        class="px-8 py-3 bg-surface-900 hover:bg-black text-white font-bold rounded-xl shadow-lg shadow-surface-900/20 hover:shadow-xl hover:scale-105 transition-all flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:shadow-none">
                        <span>سوال بعدی</span>
                        <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </button>
                </div>
            </main>
        </div>
    </div>

    <!-- Completion Modal -->
    <div x-show="showCompletionModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 backdrop-blur-none"
         x-transition:enter-end="opacity-100 backdrop-blur-sm">
        <div class="absolute inset-0 bg-surface-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center overflow-hidden"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 scale-90 translate-y-10"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="w-24 h-24 bg-gradient-to-tr from-primary-400 to-primary-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary-500/40 relative">
                <div class="absolute inset-0 rounded-full border-4 border-white/20"></div>
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h3 class="text-3xl font-black text-surface-900 mb-2">تبریک!</h3>
            <p class="text-surface-500 leading-relaxed mb-8">شما به تمامی سوالات این بخش با موفقیت پاسخ دادید.</p>
            
            <div class="flex flex-col gap-3">
                <a href="/public/" class="w-full py-4 bg-surface-900 hover:bg-black text-white font-bold rounded-2xl shadow-lg transition-transform hover:-translate-y-1">
                    بازگشت به منوی اصلی
                </a>
                <button @click="createNewAttempt()" class="w-full py-4 bg-primary-100 hover:bg-primary-200 text-primary-700 font-bold rounded-2xl transition-colors">
                    شروع مجدد
                </button>
            </div>
        </div>
    </div>

    <script>
        function answerApp() {
            return {
                questions: <?= $questionsJson ?>,
                answers: {},
                currentIndex: 0,
                saving: false,
                lastSaved: false,
                debounceTimer: null,
                showCompletionModal: false,
                showAttemptModal: true,
                categoryId: <?= $categoryId ?>,
                currentAttemptId: null,
                attempts: [],
                initialAttemptId: <?= $attemptId ? $attemptId : 'null' ?>,
                collapsedGroups: [],
                sidebarOpen: false,

                get currentQuestion() {
                    return this.questions[this.currentIndex] || null;
                },

                get currentAnswer() {
                    if (!this.currentQuestion) return '';
                    const answer = this.answers[this.currentQuestion.id];
                    if (this.currentQuestion.answer_type === 'multiselect') {
                        return answer ? JSON.parse(answer) : [];
                    }
                    if (this.currentQuestion.answer_type === 'city_province') {
                        try {
                            return answer ? JSON.parse(answer) : { province: '', city: '' };
                        } catch {
                            return { province: '', city: '' };
                        }
                    }
                    return answer || '';
                },

                set currentAnswer(value) {
                    if (!this.currentQuestion) return;
                    if (this.currentQuestion.answer_type === 'multiselect' || this.currentQuestion.answer_type === 'city_province') {
                        this.answers[this.currentQuestion.id] = JSON.stringify(value);
                    } else {
                        this.answers[this.currentQuestion.id] = value;
                    }
                },

                get answeredCount() {
                    return Object.values(this.answers).filter(a => a && a !== '' && a !== '[]').length;
                },

                get progressPercent() {
                    return this.questions.length > 0 ? Math.round((this.answeredCount / this.questions.length) * 100) : 0;
                },

                get questionGroups() {
                    const groups = [...new Set(this.questions.map(q => q.question_group || 'عمومی'))];
                    return groups;
                },

                getGroupQuestions(group) {
                    return this.questions.filter(q => (q.question_group || 'عمومی') === group);
                },

                getGroupAnsweredCount(group) {
                    return this.getGroupQuestions(group).filter(q => this.isAnswered(q.id)).length;
                },

                getQuestionIndex(questionId) {
                    return this.questions.findIndex(q => q.id === questionId);
                },

                toggleGroup(group) {
                    const idx = this.collapsedGroups.indexOf(group);
                    if (idx === -1) {
                        this.collapsedGroups.push(group);
                    } else {
                        this.collapsedGroups.splice(idx, 1);
                    }
                },

                get iranProvinces() {
                    return typeof window.IRAN_CITIES !== 'undefined' ? window.IRAN_CITIES : [];
                },

                get currentProvinceCities() {
                    // Only compute cities if current question is city_province type
                    if (!this.currentQuestion || this.currentQuestion.answer_type !== 'city_province') return [];
                    const ans = this.currentAnswer;
                    if (!ans || typeof ans !== 'object' || !ans.province) return [];
                    if (typeof window.IRAN_CITIES === 'undefined') return [];
                    const p = window.IRAN_CITIES.find(x => x.name === ans.province);
                    return p ? p.cities : [];
                },

                async init() {
                    await this.loadAttempts();
                    if (this.initialAttemptId) {
                        await this.selectAttempt(this.initialAttemptId);
                    }
                },

                async loadAttempts() {
                    try {
                        const res = await fetch(`/api/attempts.php?category_id=${this.categoryId}`);
                        const data = await res.json();
                        if (data.success) this.attempts = data.data;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async createNewAttempt() {
                    try {
                        const res = await fetch('/api/attempts.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ category_id: this.categoryId })
                        });
                        const data = await res.json();
                        if (data.success) {
                            await this.selectAttempt(data.attempt_id);
                            await this.loadAttempts();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                async selectAttempt(attemptId) {
                    this.currentAttemptId = attemptId;
                    this.showAttemptModal = false;
                    this.showCompletionModal = false;
                    this.answers = {};
                    this.currentIndex = 0;
                    this.collapsedGroups = [];
                    await this.loadAnswers();
                },

                async loadAnswers() {
                    try {
                        const res = await fetch(`/api/answers.php?attempt_id=${this.currentAttemptId}`);
                        const data = await res.json();
                        if (data.success) this.answers = data.data;
                    } catch (e) {
                        console.error(e);
                    }
                },

                isAnswered(questionId) {
                    const answer = this.answers[questionId];
                    return answer && answer !== '' && answer !== '[]';
                },

                goToQuestion(index) {
                    this.currentIndex = index;
                },

                prevQuestion() {
                    if (this.currentIndex > 0) this.currentIndex--;
                },

                nextQuestion() {
                    if (this.currentIndex < this.questions.length - 1) this.currentIndex++;
                },

                setAnswer(value, autoAdvance = false) {
                    this.currentAnswer = value;
                    this.saveAnswer();
                    if (autoAdvance) {
                        setTimeout(() => this.nextQuestion(), 200);
                    }
                },

                handleEnter(e) {
                    if (this.currentQuestion.answer_type === 'textarea' && e.shiftKey) return;
                    e.preventDefault();
                    
                    if (this.currentAnswer && this.currentAnswer.toString().trim().length > 0) {
                        clearTimeout(this.debounceTimer);
                        this.saveAnswer();
                        this.nextQuestion();
                    }
                },

                isSelectedMulti(opt) {
                    const current = this.currentAnswer;
                    return Array.isArray(current) && current.includes(opt);
                },

                toggleMultiSelect(opt) {
                    let current = this.currentAnswer;
                    if (!Array.isArray(current)) current = [];
                    const index = current.indexOf(opt);
                    if (index === -1) {
                        current.push(opt);
                    } else {
                        current.splice(index, 1);
                    }
                    this.currentAnswer = [...current];
                    this.saveAnswer();
                },

                debounceSave() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => this.saveAnswer(), 500);
                },

                async saveAnswer() {
                    if (!this.currentQuestion || !this.currentAttemptId) return;
                    this.saving = true;
                    this.lastSaved = false;

                    try {
                        let value = this.answers[this.currentQuestion.id] || '';
                        if (this.currentQuestion.answer_type === 'multiselect' || this.currentQuestion.answer_type === 'city_province') {
                            try { value = JSON.parse(value); } catch { value = []; }
                        }

                        await fetch('/api/answers.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                attempt_id: this.currentAttemptId,
                                question_id: this.currentQuestion.id,
                                answer_value: value
                            })
                        });
                        
                        this.lastSaved = true;
                        
                        if (this.answeredCount === this.questions.length) {
                            setTimeout(() => { this.showCompletionModal = true; }, 500);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.saving = false;
                },

                formatDate(dateStr) {
                    const date = new Date(dateStr);
                    return new Intl.DateTimeFormat('fa-IR', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }).format(date);
                }
            };
        }
    </script>
</body>
</html>
