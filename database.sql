-- Set UTF-8 encoding
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_connection=utf8mb4;

-- QA System Database Schema

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    question_text TEXT NOT NULL,
    answer_type ENUM('boolean', 'text', 'textarea', 'select', 'multiselect', 'city_province', 'dropdown', 'number') NOT NULL DEFAULT 'text',
    options JSON,
    placeholder VARCHAR(500),
    question_group VARCHAR(255) DEFAULT 'عمومی',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE IF NOT EXISTS attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (session_id) REFERENCES user_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attempt_question (attempt_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Insert Categories
INSERT INTO categories (title, description) VALUES 
('نظرسنجی رضایت مشتری', 'این دسته‌بندی برای سنجش میزان رضایت مشتریان از خدمات و محصولات ما طراحی شده است.'),
('ارزیابی محیط کاری', 'این دسته‌بندی برای دریافت بازخورد کارکنان در مورد محیط کار و شرایط شغلی است.');

-- Insert Questions for Category 1
SET @cat1 = (SELECT id FROM categories WHERE title = 'نظرسنجی رضایت مشتری' LIMIT 1);
INSERT INTO questions (category_id, question_text, answer_type, options, placeholder, sort_order) VALUES 
(@cat1, 'آیا از سرعت پاسخگویی ما راضی هستید؟', 'boolean', NULL, NULL, 1),
(@cat1, 'نام کاربری یا شماره فاکتور خود را وارد کنید.', 'text', NULL, 'مثلاً: 12345', 2),
(@cat1, 'نظرات و پیشنهادات خود را برای بهبود خدمات ما بنویسید.', 'textarea', NULL, 'پیام خود را اینجا بنویسید...', 3),
(@cat1, 'نحوه آشنایی شما با ما چگونه بوده است؟', 'select', '["گوگل", "شبکه‌های اجتماعی", "دوستان", "تبلیغات"]', 'یک گزینه را انتخاب کنید', 4),
(@cat1, 'کدام یک از خدمات ما را بیشتر استفاده می‌کنید؟', 'multiselect', '["پشتیبانی", "فروش", "مشاوره", "خدمات پس از فروش"]', 'چند گزینه را انتخاب کنید', 5),
(@cat1, 'در کدام استان و شهر سکونت دارید؟', 'city_province', NULL, NULL, 6),
(@cat1, 'آیا ما را به دیگران توصیه می‌کنید؟', 'boolean', NULL, NULL, 7),
(@cat1, 'کیفیت محصولات ما را چگونه ارزیابی می‌کنید؟', 'select', '["عالی", "خوب", "متوسط", "ضعیف"]', 'یک گزینه را انتخاب کنید', 8),
(@cat1, 'مدل دستگاه یا محصول خریداری شده را بنویسید.', 'text', NULL, 'نام محصول', 9),
(@cat1, 'اگر مشکلی در فرآیند خرید داشتید، لطفاً توضیح دهید.', 'textarea', NULL, 'شرح مشکل...', 10),
(@cat1, 'زمان مورد نظر برای تماس پشتیبانی را انتخاب کنید.', 'dropdown', '["صبح (8 تا 12)", "ظهر (12 تا 16)", "عصر (16 تا 20)"]', 'انتخاب زمان', 11);

-- Insert Questions for Category 2
SET @cat2 = (SELECT id FROM categories WHERE title = 'ارزیابی محیط کاری' LIMIT 1);
INSERT INTO questions (category_id, question_text, answer_type, options, placeholder, sort_order) VALUES 
(@cat2, 'آیا از ابزارهای کاری خود رضایت دارید؟', 'boolean', NULL, NULL, 1),
(@cat2, 'نام دپارتمان خود را وارد کنید.', 'text', NULL, 'نام واحد سازمانی', 2),
(@cat2, 'چه پیشنهادی برای بهبود فضای فیزیکی دفتر دارید؟', 'textarea', NULL, 'پیشنهاد شما...', 3),
(@cat2, 'سطح تعامل با مدیر مستقیم خود را چگونه می‌بینید؟', 'select', '["عالی", "مناسب", "نیاز به بهبود", "ضعیف"]', 'یک گزینه را انتخاب کنید', 4),
(@cat2, 'از کدام مزایای رفاهی شرکت استفاده می‌کنید؟', 'multiselect', '["بیمه تکمیلی", "ناهار", "باشگاه ورزشی", "بن خرید"]', 'چند گزینه را انتخاب کنید', 5),
(@cat2, 'محل دفتر یا شعبه‌ای که در آن فعالیت می‌کنید را انتخاب کنید.', 'city_province', NULL, NULL, 6),
(@cat2, 'آیا تعادل میان کار و زندگی شخصی شما برقرار است؟', 'boolean', NULL, NULL, 7),
(@cat2, 'فرصت‌های رشد شغلی در شرکت را چگونه ارزیابی می‌کنید؟', 'select', '["زیاد", "متوسط", "کم", "هیچ"]', 'یک گزینه را انتخاب کنید', 8),
(@cat2, 'عنوان شغلی دقیق خود را بنویسید.', 'text', NULL, 'سمت شغلی', 9),
(@cat2, 'بزرگترین چالش شما در محیط کار چیست؟', 'textarea', NULL, 'شرح چالش...', 10),
(@cat2, 'نحوه همکاری خود را انتخاب کنید.', 'dropdown', '["تمام وقت", "پاره وقت", "دورکاری", "پروژه‌ای"]', 'انتخاب نوع همکاری', 11);
