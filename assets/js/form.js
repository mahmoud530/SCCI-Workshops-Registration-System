const form = document.getElementById('registrationForm');
const submitBtn = document.getElementById('submitBtn');
const successMessage = document.getElementById('successMessage');

// دوال التحقق البسيطة
function validateName(name) {
    return name.trim().length >= 2 && /^[a-zA-Zأ-ي\s]+$/u.test(name.trim());
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    const cleanPhone = phone.replace(/\D/g, '');
    return cleanPhone.length === 11;
}

function validateuniversity(university) {
    return university.trim().length >= 2;
}

function validatefaculty(faculty) {
    return faculty.trim().length >= 2;
}

function validatelevel(level) {
    return level.trim().length >= 1;
}

function isValidWorkshop(workshop) {
    return ['Devology', 'Marketnuer', 'Techsolve', 'Data Station'].includes(workshop);
}

// إظهار وإخفاء الأخطاء مع Scroll
function showError(fieldId, message) {
    const errorDiv = document.getElementById(fieldId + 'Error');
    const field = document.getElementById(fieldId);
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    field.style.borderColor = '#e74c3c';
    
    // Scroll to the error field smoothly
    field.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
    
    // Focus on the field
    setTimeout(() => {
        field.focus();
    }, 500);
}

function hideError(fieldId) {
    const errorDiv = document.getElementById(fieldId + 'Error');
    const field = document.getElementById(fieldId);
    errorDiv.style.display = 'none';
    field.style.borderColor = '#ddd';
}

// تحديث خيارات الـ select
function updateSelectOptions() {
    const selects = ['first_preference', 'second_preference', 'third_preference'];
    const selectedValues = selects.map(id => document.getElementById(id).value);
    
    selects.forEach((selectId, currentIndex) => {
        const select = document.getElementById(selectId);
        const options = select.querySelectorAll('option[value]');
        
        options.forEach(option => {
            const isSelectedElsewhere = selectedValues.some((value, index) =>
                value === option.value && index !== currentIndex && value !== ''
            );
            option.disabled = isSelectedElsewhere;
            option.style.color = isSelectedElsewhere ? '#ccc' : '';
        });
    });
}

// التحقق المباشر
document.getElementById('name').addEventListener('input', function() {
    if (this.value && validateName(this.value)) {
        hideError('name');
    }
});

document.getElementById('email').addEventListener('input', function() {
    if (this.value && validateEmail(this.value)) {
        hideError('email');
    }
});

document.getElementById('phone').addEventListener('input', function() {
    if (this.value && validatePhone(this.value)) {
        hideError('phone');
    }
});

document.getElementById('university').addEventListener('input', function() {
    if (this.value && validateuniversity(this.value)) {
        hideError('university');
    }
});

document.getElementById('faculty').addEventListener('input', function() {
    if(this.value && validatefaculty(this.value)){
        hideError('faculty');
    }
});

document.getElementById('level').addEventListener('input', function() {
    if(this.value && validatelevel(this.value)){
        hideError('level');
    }
});

// تحديث الخيارات عند تغيير الاختيارات
['first_preference', 'second_preference', 'third_preference'].forEach(id => {
    document.getElementById(id).addEventListener('change', function() {
        updateSelectOptions();
        if (this.value && isValidWorkshop(this.value)) {
            hideError(id);
        }
    });
});

// إرسال النموذج
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(form);
    const data = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        university: formData.get('university'),
        faculty: formData.get('faculty'),
        level: formData.get('level'),
        first_preference: formData.get('first_preference'),
        second_preference: formData.get('second_preference'),
        third_preference: formData.get('third_preference')
    };

    let hasErrors = false;
    let firstErrorField = null;

    // التحقق من الاسم
    if (!data.name || !validateName(data.name)) {
        showError('name', 'Please enter a valid name (letters only)');
        if (!firstErrorField) firstErrorField = 'name';
        hasErrors = true;
    }

    // التحقق من الإيميل
    if (!data.email || !validateEmail(data.email)) {
        showError('email', 'Please enter a valid email address');
        if (!firstErrorField) firstErrorField = 'email';
        hasErrors = true;
    }

    // التحقق من الهاتف
    if (!data.phone || !validatePhone(data.phone)) {
        showError('phone', 'Please enter a valid phone number (11 digits)');
        if (!firstErrorField) firstErrorField = 'phone';
        hasErrors = true;
    }

    // التحقق من الجامعة
    if (!data.university || !validateuniversity(data.university)) {
        showError('university', 'Please enter a valid university name');
        if (!firstErrorField) firstErrorField = 'university';
        hasErrors = true;
    }

    // التحقق من الكلية
    if (!data.faculty || !validatefaculty(data.faculty)) {
        showError('faculty', 'Please enter a valid faculty name');
        if (!firstErrorField) firstErrorField = 'faculty';
        hasErrors = true;
    }

    // التحقق من السنة
    if (!data.level || !validatelevel(data.level)) {
        showError('level', 'Please enter a valid level');
        if (!firstErrorField) firstErrorField = 'level';
        hasErrors = true;
    }

    // التحقق من الاختيارات
    if (!data.first_preference || !isValidWorkshop(data.first_preference)) {
        showError('first_preference', 'Please select your first workshop choice');
        if (!firstErrorField) firstErrorField = 'first_preference';
        hasErrors = true;
    }
    if (!data.second_preference || !isValidWorkshop(data.second_preference)) {
        showError('second_preference', 'Please select your second workshop choice');
        if (!firstErrorField) firstErrorField = 'second_preference';
        hasErrors = true;
    }
    if (!data.third_preference || !isValidWorkshop(data.third_preference)) {
        showError('third_preference', 'Please select your third workshop choice');
        if (!firstErrorField) firstErrorField = 'third_preference';
        hasErrors = true;
    }

    // التحقق من عدم تكرار الاختيارات
    const preferences = [data.first_preference, data.second_preference, data.third_preference];
    if (new Set(preferences).size !== 3 && preferences.every(p => p !== '')) {
        showError('first_preference', 'Please select different workshops for each choice');
        if (!firstErrorField) firstErrorField = 'first_preference';
        hasErrors = true;
    }

    // إذا كان هناك أخطاء، اذهب لأول خطأ فقط
    if (hasErrors) {
        if (firstErrorField) {
            const field = document.getElementById(firstErrorField);
            field.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            setTimeout(() => {
                field.focus();
            }, 500);
        }
        return;
    }
    // إرسال البيانات
    submitBtn.disabled = true;
    submitBtn.textContent = 'Registering...';

    try {
        const response = await fetch('./process_workshops.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
      if (result.success) {
    successMessage.style.display = 'block';
    form.reset();
    form.style.display = 'none';

    successMessage.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });

} else {

    // ⛔ Limit Error (نعرضها في الديف اللي فوق)
    if (result.limit === true) {

        const remainingSec = parseInt(result.remaining, 10);
        const minutes = Math.floor(remainingSec / 60);
        const seconds = remainingSec % 60;

        const limitMsg = `تم تجاوز الحد المسموح. يمكنك المحاولة بعد ${minutes} دقيقة و ${seconds} ثانية`;

        const limitDiv = document.getElementById('limitError');
        limitDiv.textContent = limitMsg;
        limitDiv.style.display = 'block';

        // Scroll to div
        limitDiv.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        submitBtn.disabled = false;
        submitBtn.textContent = 'Register for Workshops';
        return;
    }
    // ✋ الأخطاء الخاصة بالفيلدز
    if (result.field && result.message) {
        showError(result.field, result.message);
    }
}

    } catch (error) {
        showError('email', 'Connection error. Please try again.');
    }

    submitBtn.disabled = false;
    submitBtn.textContent = 'Register for Workshops';
});

// تهيئة الصفحة
updateSelectOptions();