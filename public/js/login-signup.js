// Thêm kiểm tra null và aria-label cho accessibility
document.querySelectorAll('.password-toggle').forEach((toggle, index) => {
    const passwordField = document.querySelectorAll('input[type="password"]')[index];
    if (!passwordField) return;

    toggle.addEventListener('click', () => {
        const isPassword = passwordField.type === 'password';
        passwordField.type = isPassword ? 'text' : 'password';
        toggle.textContent = isPassword ? 'Hide' : 'Show';
        toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        passwordField.focus();
    });
});

// Lấy phần modal
var modal = document.getElementById("loginModal");

// Lấy nút mở modal
var btn = document.getElementById("loginButton");

// Lấy nút đóng modal
var span = document.getElementsByClassName("close-button")[0];

// Lấy phần login và signup form
var loginContainer = document.querySelector('.login-container');
var signupContainer = document.querySelector('.signup-container');

// Lấy nút chuyển đổi giữa login và signup form
var showSignupForm = document.getElementById('showSignupForm');
var showLoginForm = document.getElementById('showLoginForm');

// Phần chuyển đổi login và signup form
function showLogin() {
    loginContainer.style.display = 'block';
    signupContainer.style.display = 'none';
}

function showSignup() {
    loginContainer.style.display = 'none';
    signupContainer.style.display = 'flex';
}

// Biến lưu url cần chuyển hướng sau khi đăng nhập
let redirect_url;

/*
Hàm mở modal đăng nhập với tham số là url cần chuyển hướng sau khi đăng nhập
(vấn đề: mặc định tham số đầu tiên là event)
*/
function openModal(url) {
    modal.style.display = "block";
    document.body.classList.add('modal-open');
    showLogin(); // Mặc định hiển thị form login

    // Nếu có url thì gán vào biến redirect_url
    if (url && typeof url === 'string') {
        redirect_url = url;
    }
}

// Hàm đóng modal với kiểm tra null
function closeModal() {
    if (modal) {
        modal.style.display = "none";
        document.body.classList.remove('modal-open');
    } else {
        console.error('Modal element not found');
    }
}

// Khi người dùng click nút, mở modal
if (btn) {
    btn.onclick = openModal;
}

// Khi người dùng click vào nút đóng, đóng modal
if (span) {
    span.onclick = closeModal;
} else {
    console.warn('Close button not found');
}

// Khi người dùng click bên ngoài modal, đóng modal
window.onclick = function(event) {
    if (modal && event.target === modal) {
        closeModal();
    }
}

// Phần chuyển đổi nội dung login và signup form
showSignupForm.onclick = showSignup;
showLoginForm.onclick = showLogin;

/*
Xử lý form đăng nhập, đăng ký
*/
document.querySelector('.form-grid').addEventListener('submit', function (e) {
    e.preventDefault(); // Ngăn form submit mặc định

    // Reset errors
    clearErrors();

    // Validate
    let isValid = true;
    const email = this.querySelector('input[name="email"]');
    const password = this.querySelector('input[name="password"]');

    if (!email.value.trim()) {
        showError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email');
        isValid = false;
    }

    // Password validation
    if (!password.value.trim()) {
        showError(password, 'Password is required');
        isValid = false;
    } else if (!isValidPassword(password.value)) {
        showError(password, 'Password must be at least 8 characters with 1 lowercase letter, 1 number, and no whitespace');
        isValid = false;
    }

    if (!isValid) return;

    // Submit form
    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Lưu thông báo vào sessionStorage trước khi chuyển hướng
            sessionStorage.setItem('message', data.message);
            window.location.href = redirect_url ?? data.redirect_url;
        } else {
            notification(data.message || "Có lỗi xảy ra", 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notification('Có lỗi xảy ra, vui lòng thử lại sau.', 'error');
    });
});

// Hàm hiển thị thông báo lỗi
function showError(input, message) {
    input.classList.add('error');
    const errorElement = document.getElementById(`${input.name}-error`);
    if (errorElement) {
        errorElement.style.display = 'block';
        errorElement.textContent = message;
    }
}

// Hàm clear thông báo lỗi
function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    document.querySelectorAll('.form-input').forEach(input => {
        input.classList.remove('error');
    });
}

// Hàm kiểm tra email
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Hàm kiểm tra password
function isValidPassword(password) {
    return /^(?=.*[a-z])(?=.*\d)(?!\s).{8,}$/.test(password);
}

// Thêm sự kiện cho nút đăng xuất
document.addEventListener('DOMContentLoaded', () => {
    const logoutButton = document.getElementById('logout-btn');
    if (logoutButton) {
        logoutButton.addEventListener('click', handleLogout);
    } else {
        console.warn('Logout button not found in DOM.');
    }
});

/*
Xử lý đăng xuất
*/
async function handleLogout(e) {
    e.preventDefault();
    const button = document.getElementById('logout-btn');

    try {
        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang đăng xuất...';

        const response = await fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            notification(data.message || 'Đăng xuất thành công', 'success');
            sessionStorage.setItem('message', data.message);
            window.location.href = data.redirect_url;
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Logout error:', error);
        notification(error.message || 'Có lỗi xảy ra khi đăng xuất', 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fa fa-sign-out"></i> Đăng xuất';
    }
}