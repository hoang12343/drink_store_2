document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            document.querySelectorAll('.error').forEach(el => el.textContent = '');

            const fullName = document.getElementById('full_name').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!fullName) {
                document.querySelector('#full_name + .error').textContent = 'Vui lòng nhập họ và tên';
                isValid = false;
            }
            if (!username || username.length < 3) {
                document.querySelector('#username + .error').textContent = 'Tên đăng nhập phải từ 3 ký tự';
                isValid = false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.querySelector('#email + .error').textContent = 'Email không hợp lệ';
                isValid = false;
            }
            if (!/^[0-9]{10,11}$/.test(phone)) {
                document.querySelector('#phone + .error').textContent = 'Số điện thoại phải từ 10-11 số';
                isValid = false;
            }
            if (!address) {
                document.querySelector('#address + .error').textContent = 'Vui lòng nhập địa chỉ';
                isValid = false;
            }
            if (!password || password.length < 6) {
                document.querySelector('#password + .error').textContent = 'Mật khẩu phải từ 6 ký tự';
                isValid = false;
            }
            if (password !== confirmPassword) {
                document.querySelector('#confirm_password + .error').textContent = 'Mật khẩu không khớp';
                isValid = false;
            }

            if (!isValid) e.preventDefault();
        });
    }
});