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
    return cleanPhone.length >= 10 && cleanPhone.length <= 15;
}

function isValidWorkshop(workshop) {
    return ['Devology', 'Business', 'Techsolve', 'Appsplash', 'UI/UX'].includes(workshop);
}

// إظهار وإخفاء الأخطاء
function showError(fieldId, message) {
    const errorDiv = document.getElementById(fieldId + 'Error');
    const field = document.getElementById(fieldId);
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    field.style.borderColor = '#e74c3c';
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
        first_preference: formData.get('first_preference'),
        second_preference: formData.get('second_preference'),
        third_preference: formData.get('third_preference')
    };

    let hasErrors = false;

    // التحقق من الاسم
    if (!data.name || !validateName(data.name)) {
        showError('name', 'Please enter a valid name (letters only)');
        hasErrors = true;
    }

    // التحقق من الإيميل
    if (!data.email || !validateEmail(data.email)) {
        showError('email', 'Please enter a valid email address');
        hasErrors = true;
    }

    // التحقق من الهاتف
    if (!data.phone || !validatePhone(data.phone)) {
        showError('phone', 'Please enter a valid phone number (10-15 digits)');
        hasErrors = true;
    }

    // التحقق من الاختيارات
    if (!data.first_preference || !isValidWorkshop(data.first_preference)) {
        showError('first_preference', 'Please select your first workshop choice');
        hasErrors = true;
    }
    if (!data.second_preference || !isValidWorkshop(data.second_preference)) {
        showError('second_preference', 'Please select your second workshop choice');
        hasErrors = true;
    }
    if (!data.third_preference || !isValidWorkshop(data.third_preference)) {
        showError('third_preference', 'Please select your third workshop choice');
        hasErrors = true;
    }

    // التحقق من عدم تكرار الاختيارات
    const preferences = [data.first_preference, data.second_preference, data.third_preference];
    if (new Set(preferences).size !== 3 && preferences.every(p => p !== '')) {
        showError('first_preference', 'Please select different workshops for each choice');
        hasErrors = true;
    }

    if (hasErrors) return;

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
        } else {
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