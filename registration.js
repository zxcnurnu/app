document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Очистка предыдущих ошибок
    clearErrors();
    
    // Получение значений полей
    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value;
    const fullName = document.getElementById('fullName').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const email = document.getElementById('email').value.trim();
    
    // Валидация
    let isValid = true;
    
    // Проверка логина (уникальность проверяется при отправке на сервер)
    if (login.length < 3) {
        showError('loginError', 'Логин должен содержать минимум 3 символа');
        isValid = false;
    }
    
    // Проверка пароля
    if (password.length < 6) {
        showError('passwordError', 'Пароль должен содержать минимум 6 символов');
        isValid = false;
    }
    
    // Проверка ФИО (кириллица и пробелы)
    const fullNameRegex = /^[А-ЯЁа-яё\s]+$/;
    if (!fullNameRegex.test(fullName)) {
        showError('fullNameError', 'ФИО должно содержать только кириллические буквы и пробелы');
        isValid = false;
    }
    
    // Проверка телефона
    const phoneRegex = /^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/;
    if (!phoneRegex.test(phone)) {
        showError('phoneError', 'Телефон должен быть в формате +7(XXX)-XXX-XX-XX');
        isValid = false;
    }
    
    // Проверка email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('emailError', 'Введите корректный email');
        isValid = false;
    }
    
    // Если все проверки пройдены
    if (isValid) {
        // Здесь должна быть отправка данных на сервер
        // Для демонстрации просто покажем сообщение об успехе
        
        const userData = {
            login,
            password,
            fullName,
            phone,
            email
        };
        
        // Сохраняем в localStorage (имитация базы данных)
        saveToLocalStorage(userData);
        
        // Показываем сообщение об успехе
        document.getElementById('successMessage').textContent = 'Регистрация прошла успешно!';
        document.getElementById('successMessage').style.display = 'block';
        
        // Очищаем форму
        document.getElementById('registrationForm').reset();
        
        // Через 3 секунды скрываем сообщение
        setTimeout(() => {
            document.getElementById('successMessage').style.display = 'none';
        }, 3000);
    }
});

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    errorElement.textContent = message;
}

function clearErrors() {
    const errorElements = document.querySelectorAll('.error');
    errorElements.forEach(element => {
        element.textContent = '';
    });
    
    document.getElementById('successMessage').style.display = 'none';
}

function saveToLocalStorage(userData) {
    // Получаем текущих пользователей из localStorage
    let users = JSON.parse(localStorage.getItem('users')) || [];
    
    // Проверяем, существует ли уже пользователь с таким логином
    const userExists = users.some(user => user.login === userData.login);
    
    if (userExists) {
        showError('loginError', 'Пользователь с таким логином уже существует');
        return false;
    }
    
    // Добавляем нового пользователя
    users.push(userData);
    
    // Сохраняем обратно в localStorage
    localStorage.setItem('users', JSON.stringify(users));
    
    return true;
}

// Маска для телефона
document.getElementById('phone').addEventListener('input', function(e) {
    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
    e.target.value = !x[2] ? '+7' : '+7(' + x[2] + (x[3] ? ')-' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : ''));
});