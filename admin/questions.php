<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
requireAdmin();

$pageTitle = 'سوالات';
$currentPage = 'questions';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-10" x-data="questionsApp()">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-dark-900">سوالات</h1>
                <p class="text-dark-400 mt-1">مدیریت سوالات هر دسته‌بندی</p>
            </div>
            <button @click="openModal()" :disabled="!selectedCategory" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark-900 hover:bg-dark-800 text-white text-sm font-medium rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                سوال جدید
            </button>
        </div>

        <!-- Category Filter -->
        <div class="bg-white rounded-2xl border border-dark-100 p-6 mb-6">
            <label class="block text-dark-800 text-sm font-medium mb-3">انتخاب دسته‌بندی</label>
            <select x-model="selectedCategory" @change="loadQuestions()"
                class="w-full max-w-sm px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                <option value="">انتخاب کنید...</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.title"></option>
                </template>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-900 mb-2">دسته‌بندی را انتخاب کنید</h3>
            <p class="text-dark-400">ابتدا یک دسته‌بندی برای مشاهده سوالات انتخاب کنید</p>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && selectedCategory && questions.length === 0" class="bg-white rounded-2xl border border-dark-100 p-16 text-center">
            <div class="w-16 h-16 bg-dark-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-900 mb-2">هنوز سوالی ایجاد نشده</h3>
            <p class="text-dark-400 mb-6">برای این دسته‌بندی سوالی تعریف نشده است</p>
            <button @click="openModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                اولین سوال
            </button>
        </div>

        <!-- Questions List Grouped -->
        <div x-show="!loading && selectedCategory && questions.length > 0" class="space-y-6">
            <template x-for="group in questionGroups" :key="group">
                <div class="bg-white rounded-2xl border border-dark-100 overflow-hidden">
                    <div class="px-5 py-4 bg-dark-50 border-b border-dark-100 flex items-center justify-between">
                        <h3 class="font-semibold text-dark-800" x-text="group"></h3>
                        <span class="text-dark-400 text-sm" x-text="getGroupQuestions(group).length + ' سوال'"></span>
                    </div>
                    <div class="divide-y divide-dark-100">
                        <template x-for="q in getGroupQuestions(group)" :key="q.id">
                            <div class="p-5 hover:bg-dark-50 transition">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 bg-dark-100 rounded-xl flex items-center justify-center text-dark-400 font-semibold text-sm flex-shrink-0" x-text="q.sort_order"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-dark-900 font-medium mb-2" x-text="q.question_text"></p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-lg"
                                                :class="{
                                                    'bg-blue-50 text-blue-600': q.answer_type === 'text',
                                                    'bg-green-50 text-green-600': q.answer_type === 'boolean',
                                                    'bg-purple-50 text-purple-600': q.answer_type === 'textarea',
                                                    'bg-orange-50 text-orange-600': q.answer_type === 'select',
                                                    'bg-pink-50 text-pink-600': q.answer_type === 'multiselect',
                                                    'bg-cyan-50 text-cyan-600': q.answer_type === 'city_province'
                                                }"
                                                x-text="answerTypeLabels[q.answer_type]"></span>
                                            <span x-show="q.options && q.options.length > 0" class="text-dark-300 text-xs" x-text="q.options.length + ' گزینه'"></span>
                                            <span x-show="q.placeholder" class="text-dark-300 text-xs">راهنما: <span x-text="q.placeholder?.substring(0,20) + '...'"></span></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="editQuestion(q)" class="w-9 h-9 flex items-center justify-center text-dark-400 hover:text-dark-800 hover:bg-dark-100 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteQuestion(q.id)" class="w-9 h-9 flex items-center justify-center text-dark-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-dark-900/20 backdrop-blur-sm" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-dark-100 sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-dark-900" x-text="editingId ? 'ویرایش سوال' : 'سوال جدید'"></h3>
                </div>
                <form @submit.prevent="saveQuestion()" class="p-6 space-y-5">
                    <div>
                        <label class="block text-dark-800 text-sm font-medium mb-2">متن سوال</label>
                        <textarea x-model="form.question_text" required rows="3"
                            class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-dark-800 text-sm font-medium mb-2">نوع پاسخ</label>
                            <select x-model="form.answer_type" required
                                class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                                <option value="text">متن کوتاه</option>
                                <option value="textarea">متن بلند</option>
                                <option value="boolean">بله/خیر</option>
                                <option value="select">انتخابی (تک)</option>
                                <option value="multiselect">انتخابی (چند)</option>
                                <option value="city_province">استان و شهر</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-dark-800 text-sm font-medium mb-2">گروه سوال</label>
                            <input type="text" x-model="form.question_group" list="groupList"
                                class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition"
                                placeholder="عمومی">
                            <datalist id="groupList">
                                <template x-for="g in existingGroups" :key="g">
                                    <option :value="g"></option>
                                </template>
                            </datalist>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-dark-800 text-sm font-medium mb-2">ترتیب نمایش</label>
                            <input type="number" x-model="form.sort_order" min="0"
                                class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                        </div>
                        <div x-show="form.answer_type === 'text' || form.answer_type === 'textarea'">
                            <label class="block text-dark-800 text-sm font-medium mb-2">متن راهنما (Placeholder)</label>
                            <input type="text" x-model="form.placeholder"
                                class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition"
                                placeholder="مثال: نام و نام خانوادگی">
                        </div>
                    </div>

                    <!-- Options for select/multiselect -->
                    <div x-show="form.answer_type === 'select' || form.answer_type === 'multiselect'" class="bg-dark-50 rounded-xl p-4">
                        <label class="block text-dark-800 text-sm font-medium mb-3">گزینه‌ها</label>
                        <div class="space-y-2">
                            <template x-for="(opt, index) in form.options" :key="index">
                                <div class="flex gap-2">
                                    <input type="text" x-model="form.options[index]"
                                        class="flex-1 px-4 py-2.5 bg-white border border-dark-200 rounded-lg text-dark-900 focus:outline-none focus:border-primary-500 transition text-sm"
                                        placeholder="متن گزینه">
                                    <button type="button" @click="removeOption(index)" class="w-10 h-10 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addOption()" class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm text-primary-600 hover:bg-primary-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            افزودن گزینه
                        </button>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="saving"
                            class="flex-1 py-3 bg-dark-900 hover:bg-dark-800 text-white font-medium rounded-xl transition disabled:opacity-50">
                            <span x-show="!saving" x-text="editingId ? 'به‌روزرسانی' : 'ذخیره'"></span>
                            <span x-show="saving">در حال ذخیره...</span>
                        </button>
                        <button type="button" @click="closeModal()"
                            class="px-6 py-3 bg-dark-100 hover:bg-dark-200 text-dark-800 font-medium rounded-xl transition">
                            انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast -->
        <div x-show="toast.show" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="fixed bottom-6 left-6 px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium"
            :class="toast.type === 'success' ? 'bg-primary-500' : 'bg-red-500'">
            <span x-text="toast.message"></span>
        </div>
    </main>

    <script>
        function questionsApp() {
            return {
                categories: [],
                questions: [],
                selectedCategory: '',
                loading: false,
                showModal: false,
                saving: false,
                editingId: null,
                form: { question_text: '', answer_type: 'text', sort_order: 0, options: [], placeholder: '', question_group: 'عمومی' },
                toast: { show: false, message: '', type: 'success' },
                answerTypeLabels: {
                    'text': 'متن کوتاه',
                    'textarea': 'متن بلند',
                    'boolean': 'بله/خیر',
                    'select': 'انتخابی (تک)',
                    'multiselect': 'انتخابی (چند)',
                    'city_province': 'استان و شهر'
                },

                get questionGroups() {
                    const groups = [...new Set(this.questions.map(q => q.question_group || 'عمومی'))];
                    return groups.sort();
                },

                get existingGroups() {
                    return this.questionGroups;
                },

                getGroupQuestions(group) {
                    return this.questions.filter(q => (q.question_group || 'عمومی') === group);
                },

                async init() {
                    await this.loadCategories();
                },

                async loadCategories() {
                    try {
                        const res = await fetch('../api/categories.php');
                        const data = await res.json();
                        if (data.success) this.categories = data.data;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadQuestions() {
                    if (!this.selectedCategory) {
                        this.questions = [];
                        return;
                    }
                    this.loading = true;
                    try {
                        const res = await fetch(`../api/questions.php?category_id=${this.selectedCategory}`);
                        const data = await res.json();
                        if (data.success) this.questions = data.data;
                    } catch (e) {
                        this.showToast('خطا در بارگذاری', 'error');
                    }
                    this.loading = false;
                },

                openModal() {
                    this.editingId = null;
                    this.form = { question_text: '', answer_type: 'text', sort_order: this.questions.length, options: [], placeholder: '', question_group: 'عمومی' };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                editQuestion(q) {
                    this.editingId = q.id;
                    this.form = { 
                        question_text: q.question_text, 
                        answer_type: q.answer_type, 
                        sort_order: q.sort_order, 
                        options: q.options || [],
                        placeholder: q.placeholder || '',
                        question_group: q.question_group || 'عمومی'
                    };
                    this.showModal = true;
                },

                addOption() {
                    this.form.options.push('');
                },

                removeOption(index) {
                    this.form.options.splice(index, 1);
                },

                async saveQuestion() {
                    this.saving = true;
                    try {
                        const method = this.editingId ? 'PUT' : 'POST';
                        const body = { 
                            ...this.form, 
                            category_id: this.selectedCategory, 
                            options: this.form.options.filter(o => o.trim() !== ''),
                            question_group: this.form.question_group || 'عمومی'
                        };
                        if (this.editingId) body.id = this.editingId;

                        const res = await fetch('../api/questions.php', {
                            method,
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.closeModal();
                            await this.loadQuestions();
                        } else {
                            this.showToast(data.message, 'error');
                        }
                    } catch (e) {
                        this.showToast('خطا در ذخیره', 'error');
                    }
                    this.saving = false;
                },

                async deleteQuestion(id) {
                    if (!confirm('آیا از حذف این سوال اطمینان دارید؟')) return;
                    try {
                        const res = await fetch(`../api/questions.php?id=${id}`, { method: 'DELETE' });
                        const data = await res.json();
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            await this.loadQuestions();
                        } else {
                            this.showToast(data.message, 'error');
                        }
                    } catch (e) {
                        this.showToast('خطا در حذف', 'error');
                    }
                },

                showToast(message, type = 'success') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 3000);
                }
            };
        }
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

