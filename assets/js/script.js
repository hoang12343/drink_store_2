document.addEventListener('DOMContentLoaded', () => {
    // Utility functions
    const utils = {
        $(selector) {
            return document.querySelector(selector);
        },
        $$(selector) {
            return document.querySelectorAll(selector);
        },
        showError(element, message, show = true) {
            if (!element) return;
            element.textContent = message;
            element.style.display = show ? 'block' : 'none';
        },
        validateField(field, test, errorElement, errorMessage) {
            if (!field) return false;
            const isValid = test(field.value);
            this.showError(errorElement, errorMessage, !isValid);
            return isValid;
        }
    };

    // Form switching between register and login
    const formSwitch = {
        registerPage: utils.$('#registerPage'),
        loginPage: utils.$('#loginPage'),
        init() {
            if (!this.registerPage || !this.loginPage) {
                console.warn('Register or Login page not found in DOM. Form switching disabled.');
                return;
            }
            const links = utils.$$('.form-link a');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetPage = link.getAttribute('href').includes('register') ? 'registerPage' : 'loginPage';
                    this.switchTo(targetPage);
                });
            });
            const currentPage = window.location.search.includes('page=register') ? 'registerPage' : 'loginPage';
            this.switchTo(currentPage);
        },
        switchTo(pageId) {
            if (!this.registerPage || !this.loginPage) return;
            if (pageId === 'registerPage') {
                this.registerPage.style.display = 'block';
                this.loginPage.style.display = 'none';
            } else if (pageId === 'loginPage') {
                this.registerPage.style.display = 'none';
                this.loginPage.style.display = 'block';
            }
        }
    };

    // Form validation and submission
    const forms = {
        validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },
        validatePhone(phone) {
            return /^[0-9]{10,11}$/.test(phone);
        },
        initRegisterForm() {
            const form = utils.$('#registerForm');
            if (!form) return;

            const validations = {
                'full_name': { test: value => value.trim().length > 0, errorId: 'fullNameError', message: 'Vui lòng nhập họ và tên' },
                'username': { test: value => value.length >= 4 && value.length <= 20, errorId: 'usernameError', message: 'Tên đăng nhập phải từ 4-20 ký tự' },
                'email': { test: value => this.validateEmail(value), errorId: 'emailError', message: 'Vui lòng nhập email hợp lệ' },
                'phone': { test: value => this.validatePhone(value), errorId: 'phoneError', message: 'Số điện thoại phải có 10-11 số' },
                'address': { test: value => value.trim().length > 0, errorId: 'addressError', message: 'Vui lòng nhập địa chỉ' },
                'password': { test: value => value.length >= 6, errorId: 'passwordError', message: 'Mật khẩu phải từ 6 ký tự trở lên' },
                'confirm_password': {
                    test: function(value) {
                        const passwordField = form.querySelector('[name="password"]');
                        return passwordField && value === passwordField.value;
                    },
                    errorId: 'confirmPasswordError',
                    message: 'Mật khẩu không khớp'
                }
            };

            form.addEventListener('submit', (e) => {
                let isValid = true;
                Object.entries(validations).forEach(([fieldName, { test, errorId, message }]) => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    const errorElement = utils.$(`#${errorId}`);
                    if (!utils.validateField(field, test, errorElement, message)) {
                        isValid = false;
                        field?.classList.add('invalid');
                    } else {
                        field?.classList.remove('invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        },
        initLoginForm() {
            const form = utils.$('#loginForm');
            if (!form) return;

            const validations = {
                'username': { test: value => value.trim().length > 0, errorId: 'loginUsernameError', message: 'Vui lòng nhập tên đăng nhập' },
                'password': { test: value => value.trim().length > 0, errorId: 'loginPasswordError', message: 'Vui lòng nhập mật khẩu' }
            };

            form.addEventListener('submit', (e) => {
                let isValid = true;
                Object.entries(validations).forEach(([fieldName, { test, errorId, message }]) => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    const errorElement = utils.$(`#${errorId}`);
                    if (!utils.validateField(field, test, errorElement, message)) {
                        isValid = false;
                        field?.classList.add('invalid');
                    } else {
                        field?.classList.remove('invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        },
        init() {
            this.initRegisterForm();
            this.initLoginForm();
        }
    };

    // Navigation and usermenu handling
    const navigation = {
        init() {
            // Toggle menu trên mobile
            const menuToggle = utils.$('#menuToggle');
            const navWrapper = utils.$('#navWrapper');
            if (menuToggle && navWrapper) {
                menuToggle.addEventListener('click', () => {
                    navWrapper.classList.toggle('active');
                    menuToggle.innerHTML = navWrapper.classList.contains('active')
                        ? '<i class="fas fa-times"></i>'
                        : '<i class="fas fa-bars"></i>';
                });

                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.main-navigation') && !e.target.closest('#menuToggle')) {
                        navWrapper.classList.remove('active');
                        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });
            }

            // Xử lý usermenu dropdown
            const usermenu = utils.$('.usermenu');
            if (usermenu) {
                const dropdown = usermenu.querySelector('.usermenu-dropdown');
                if (dropdown) {
                    usermenu.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                    });

                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('.usermenu')) {
                            dropdown.style.display = 'none';
                        }
                    });
                }
            }
        }
    };

    // Khởi tạo các module
    formSwitch.init();
    forms.init();
    navigation.init();
});