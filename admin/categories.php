<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
requireAdmin();

$pageTitle = 'دسته‌بندی‌ها';
$currentPage = 'categories';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-10" x-data="categoriesApp()">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-dark-900">دسته‌بندی‌ها</h1>
                <p class="text-dark-400 mt-1">مدیریت دسته‌بندی‌های سوالات</p>
            </div>
            <button @click="openModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-2xl transition-all shadow-lg shadow-brand-500/20 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                دسته‌بندی جدید
            </button>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex justify-center py-20">
            <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && categories.length === 0" class="bg-white rounded-2xl border border-dark-100 p-16 text-center">
            <div class="w-16 h-16 bg-dark-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-900 mb-2">هنوز دسته‌بندی‌ای ایجاد نشده</h3>
            <p class="text-dark-400 mb-6">برای شروع یک دسته‌بندی جدید بسازید</p>
            <button @click="openModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-2xl transition-all shadow-lg shadow-brand-500/20 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                اولین دسته‌بندی
            </button>
        </div>

        <!-- Categories Grid -->
        <div x-show="!loading && categories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <template x-for="cat in categories" :key="cat.id">
                <div class="bg-white rounded-2xl border border-dark-100 p-6 hover:shadow-lg hover:shadow-dark-100/50 transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="editCategory(cat)" class="w-10 h-10 flex items-center justify-center bg-brand-50 text-brand-600 hover:bg-brand-600 hover:text-white rounded-xl transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button @click="deleteCategory(cat.id)" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-dark-900 mb-2" x-text="cat.title"></h3>
                    <p class="text-dark-400 text-sm mb-4 line-clamp-2" x-text="cat.description || 'بدون توضیحات'"></p>
                    <div class="flex items-center gap-4 pt-4 border-t border-dark-100">
                        <span class="text-sm text-dark-400">
                            <span class="font-semibold text-dark-800" x-text="cat.question_count"></span> سوال
                        </span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-dark-900/20 backdrop-blur-sm" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg">
                <div class="p-6 border-b border-dark-100">
                    <h3 class="text-xl font-bold text-dark-900" x-text="editingId ? 'ویرایش دسته‌بندی' : 'دسته‌بندی جدید'"></h3>
                </div>
                <form @submit.prevent="saveCategory()" class="p-6 space-y-5">
                    <div>
                        <label class="block text-dark-800 text-sm font-medium mb-2">عنوان</label>
                        <input type="text" x-model="form.title" required
                            class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                    <div>
                        <label class="block text-dark-800 text-sm font-medium mb-2">توضیحات</label>
                        <textarea x-model="form.description" rows="3"
                            class="w-full px-4 py-3 bg-dark-50 border border-dark-200 rounded-xl text-dark-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="saving"
                            class="flex-1 py-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-brand-500/20 active:scale-95 disabled:opacity-50">
                            <span x-show="!saving" x-text="editingId ? 'به‌روزرسانی' : 'ذخیره'"></span>
                            <span x-show="saving">در حال ذخیره...</span>
                        </button>
                        <button type="button" @click="closeModal()"
                            class="px-8 py-4 bg-white border border-slate-200 text-slate-700 font-bold rounded-2xl transition-all hover:bg-slate-50 active:scale-95">
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
            x-transition:leave="transition ease-in duration-200"
            class="fixed bottom-6 left-6 px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium"
            :class="toast.type === 'success' ? 'bg-primary-500' : 'bg-red-500'">
            <span x-text="toast.message"></span>
        </div>
    </main>

    <script>
        function categoriesApp() {
            return {
                categories: [],
                loading: true,
                showModal: false,
                saving: false,
                editingId: null,
                form: { title: '', description: '' },
                toast: { show: false, message: '', type: 'success' },

                async init() {
                    await this.loadCategories();
                },

                async loadCategories() {
                    this.loading = true;
                    try {
                        const res = await fetch('../api/categories.php');
                        const data = await res.json();
                        if (data.success) this.categories = data.data;
                    } catch (e) {
                        this.showToast('خطا در بارگذاری', 'error');
                    }
                    this.loading = false;
                },

                openModal() {
                    this.editingId = null;
                    this.form = { title: '', description: '' };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                editCategory(cat) {
                    this.editingId = cat.id;
                    this.form = { title: cat.title, description: cat.description || '' };
                    this.showModal = true;
                },

                async saveCategory() {
                    this.saving = true;
                    try {
                        const method = this.editingId ? 'PUT' : 'POST';
                        const body = this.editingId ? { ...this.form, id: this.editingId } : this.form;

                        const res = await fetch('../api/categories.php', {
                            method,
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.closeModal();
                            await this.loadCategories();
                        } else {
                            this.showToast(data.message, 'error');
                        }
                    } catch (e) {
                        this.showToast('خطا در ذخیره', 'error');
                    }
                    this.saving = false;
                },

                async deleteCategory(id) {
                    if (!confirm('آیا از حذف این دسته‌بندی اطمینان دارید؟')) return;
                    try {
                        const res = await fetch(`../api/categories.php?id=${id}`, { method: 'DELETE' });
                        const data = await res.json();
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            await this.loadCategories();
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
