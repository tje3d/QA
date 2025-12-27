<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
requireAdmin();

$pdo = Database::getInstance()->getConnection();

// Get categories
$categories = $pdo->query('SELECT * FROM categories ORDER BY title')->fetchAll();

$pageTitle = 'پاسخ‌ها';
$currentPage = 'answers';
include __DIR__ . '/includes/header.php';
?>
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-10" x-data="answersApp()">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-dark-900">پاسخ‌ها</h1>
                <p class="text-dark-400 mt-1">مشاهده و خروجی پاسخ‌های کاربران</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="exportCSV()" :disabled="!selectedCategory || attempts.length === 0"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    خروجی CSV
                </button>
                <button @click="exportXLSX()" :disabled="!selectedCategory || attempts.length === 0"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    خروجی XLSX
                </button>
            </div>
        </div>

        <!-- Category Filter -->
        <div class="bg-white rounded-2xl border border-dark-100 p-6 mb-6">
            <label class="block text-dark-800 text-sm font-medium mb-3">انتخاب دسته‌بندی</label>
            <select x-model="selectedCategory" @change="loadAnswers()"
                class="w-full max-w-sm px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                <option value="">انتخاب کنید...</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex justify-center py-20">
            <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- No Category Selected -->
        <div x-show="!loading && !selectedCategory" class="bg-white rounded-2xl border border-dark-100 p-16 text-center">
            <div class="w-16 h-16 bg-dark-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-900 mb-2">دسته‌بندی را انتخاب کنید</h3>
            <p class="text-dark-400">ابتدا یک دسته‌بندی برای مشاهده پاسخ‌ها انتخاب کنید</p>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && selectedCategory && attempts.length === 0" class="bg-white rounded-2xl border border-dark-100 p-16 text-center">
            <div class="w-16 h-16 bg-dark-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-900 mb-2">هنوز پاسخی ثبت نشده</h3>
            <p class="text-dark-400">برای این دسته‌بندی هیچ پاسخ‌نامه‌ای وجود ندارد</p>
        </div>

        <!-- Stats -->
        <div x-show="!loading && selectedCategory && attempts.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <div class="bg-white rounded-2xl border border-dark-100 p-5">
                <p class="text-dark-400 text-sm">تعداد پاسخ‌نامه‌ها</p>
                <p class="text-2xl font-bold text-dark-900" x-text="attempts.length"></p>
            </div>
            <div class="bg-white rounded-2xl border border-dark-100 p-5">
                <p class="text-dark-400 text-sm">تعداد سوالات</p>
                <p class="text-2xl font-bold text-dark-900" x-text="questions.length"></p>
            </div>
            <div class="bg-white rounded-2xl border border-dark-100 p-5">
                <p class="text-dark-400 text-sm">میانگین پاسخ‌دهی</p>
                <p class="text-2xl font-bold text-dark-900" x-text="avgProgress + '%'"></p>
            </div>
        </div>

        <!-- Answers List -->
        <div x-show="!loading && selectedCategory && attempts.length > 0" class="space-y-4">
            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-dark-100 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-dark-50 border-b border-dark-100/50 text-dark-500 font-medium">
                            <tr>
                                <th class="px-6 py-4 w-20">#</th>
                                <th class="px-6 py-4">تاریخ ثبت</th>
                                <th class="px-6 py-4">وضعیت پیشرفت</th>
                                <th class="px-6 py-4 w-32 text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-100/50">
                            <template x-for="(att, idx) in attempts" :key="att.id">
                                <tr class="hover:bg-primary-50/50 transition-colors group">
                                    <td class="px-6 py-4 text-dark-400 font-medium" x-text="idx + 1"></td>
                                    <td class="px-6 py-4 text-dark-800">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span x-text="formatDate(att.created_at)"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 max-w-[200px]">
                                            <div class="flex-1 bg-dark-100 rounded-full h-2 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500"
                                                    :class="getAttemptProgress(att) === 100 ? 'bg-primary-500' : 'bg-primary-400'"
                                                    :style="'width: ' + getAttemptProgress(att) + '%'"></div>
                                            </div>
                                            <span class="text-xs font-bold" 
                                                :class="getAttemptProgress(att) === 100 ? 'text-primary-600' : 'text-dark-500'"
                                                x-text="getAttemptProgress(att) + '%'"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button @click="viewDetails(att)" 
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-dark-400 hover:text-primary-600 hover:bg-primary-50 transition border border-transparent hover:border-primary-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div x-show="showModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-dark-900/40 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-100 bg-dark-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-dark-900">جزئیات پاسخ‌نامه</h3>
                            <p class="text-xs text-dark-500 mt-0.5" x-text="selectedAttempt ? formatDate(selectedAttempt.created_at) : ''"></p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="text-dark-400 hover:text-red-500 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                    <template x-if="selectedAttempt">
                        <div class="space-y-8">
                            <!-- Grouped Questions -->
                            <template x-for="group in questionGroups" :key="group">
                                <div>
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="w-1.5 h-6 bg-primary-500 rounded-full"></span>
                                        <h4 class="font-bold text-dark-800" x-text="group"></h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 gap-4">
                                        <template x-for="q in getQuestionsByGroup(group)" :key="q.id">
                                            <div class="bg-dark-50 rounded-xl p-4 border border-dark-100 hover:border-dark-200 transition">
                                                <p class="text-dark-600 text-sm font-medium mb-3 leading-relaxed" x-text="q.question_text"></p>
                                                <div class="bg-white rounded-lg p-3 border border-dark-100 text-dark-900 font-medium break-words">
                                                    <template x-if="getAnswer(selectedAttempt.id, q.id)">
                                                        <span x-text="getAnswer(selectedAttempt.id, q.id)"></span>
                                                    </template>
                                                    <template x-if="!getAnswer(selectedAttempt.id, q.id)">
                                                        <span class="text-dark-300 text-sm italic">بدون پاسخ</span>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>

    <script>
        function answersApp() {
            return {
                selectedCategory: '',
                loading: false,
                questions: [],
                attempts: [],
                allAnswers: {},
                showModal: false,
                selectedAttempt: null,

                get avgProgress() {
                    if (this.attempts.length === 0) return 0;
                    const total = this.attempts.reduce((sum, att) => sum + this.getAttemptProgress(att), 0);
                    return Math.round(total / this.attempts.length);
                },

                get questionGroups() {
                    if (this.questions.length === 0) return [];
                    const groups = new Set(this.questions.map(q => q.question_group || 'عمومی'));
                    // Sort groups if needed, but 'عمومی' first or natural order
                    return Array.from(groups).sort();
                },

                async loadAnswers() {
                    if (!this.selectedCategory) {
                        this.questions = [];
                        this.attempts = [];
                        this.allAnswers = {};
                        return;
                    }
                    this.loading = true;
                    try {
                        const res = await fetch(`../api/admin/answers.php?category_id=${this.selectedCategory}`);
                        const data = await res.json();
                        if (data.success) {
                            this.questions = data.questions;
                            this.attempts = data.attempts;
                            this.allAnswers = data.answers;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.loading = false;
                },

                getQuestionsByGroup(group) {
                    return this.questions.filter(q => (q.question_group || 'عمومی') === group);
                },

                viewDetails(att) {
                    this.selectedAttempt = att;
                    this.showModal = true;
                },

                getAnswer(attemptId, questionId) {
                    const key = attemptId + '_' + questionId;
                    let val = this.allAnswers[key] || '';
                    if (val.startsWith('[')) {
                        try {
                            val = JSON.parse(val).join('، ');
                        } catch {}
                    }
                    return val;
                },

                getAttemptProgress(att) {
                    let answered = 0;
                    for (const q of this.questions) {
                        if (this.getAnswer(att.id, q.id)) answered++;
                    }
                    return this.questions.length > 0 ? Math.round((answered / this.questions.length) * 100) : 0;
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
                },

                exportCSV() {
                    let csv = '\uFEFF'; 
                    csv += 'شماره,تاریخ,پیشرفت';
                    for (const q of this.questions) {
                        csv += ',"' + q.question_text.replace(/"/g, '""') + '"';
                    }
                    csv += '\n';

                    for (let i = 0; i < this.attempts.length; i++) {
                        const att = this.attempts[i];
                        csv += (i + 1) + ',' + att.created_at + ',' + this.getAttemptProgress(att) + '%';
                        for (const q of this.questions) {
                            const val = this.getAnswer(att.id, q.id) || '';
                            csv += ',"' + val.replace(/"/g, '""') + '"';
                        }
                        csv += '\n';
                    }

                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'answers_' + this.selectedCategory + '_' + new Date().toISOString().slice(0,10) + '.csv';
                    link.click();
                },

                exportXLSX() {
                    // Headers
                    const headers = ['شماره', 'تاریخ ثبت', 'وضعیت پیشرفت'];
                    for (const q of this.questions) {
                        headers.push(q.question_text);
                    }

                    const data = [headers];

                    // Data Rows
                    for (let i = 0; i < this.attempts.length; i++) {
                        const att = this.attempts[i];
                        const row = [
                            i + 1,
                            this.formatDate(att.created_at),
                            this.getAttemptProgress(att) + '%'
                        ];
                        for (const q of this.questions) {
                            row.push(this.getAnswer(att.id, q.id) || '');
                        }
                        data.push(row);
                    }

                    // Create Workbook
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.aoa_to_sheet(data);

                    // Set RTL direction for the sheet
                    if(!ws['!views']) ws['!views'] = [];
                    ws['!views'].push({ rightToLeft: true });

                    XLSX.utils.book_append_sheet(wb, ws, "Answers");
                    XLSX.writeFile(wb, 'answers_' + this.selectedCategory + '_' + new Date().toISOString().slice(0,10) + '.xlsx');
                }
            };
        }
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
