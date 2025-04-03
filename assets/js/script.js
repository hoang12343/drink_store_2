document.addEventListener('DOMContentLoaded', () => {
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
        showMessage(element, message, type = 'success') {
            if (!element) return;
            element.textContent = message;
            element.className = `form-message ${type}`;
            element.style.display = 'block';
            setTimeout(() => (element.style.display = 'none'), 3000);
        },
        validateField(field, test, errorElement, errorMessage) {
            const isValid = test(field.value);
            this.showError(errorElement, errorMessage, !isValid);
            return isValid;
        }
    };

    const formSwitch = {
        registerPage: utils.$('#registerPage'),
        loginPage: utils.$('#loginPage'),
        init() {
            const links = utils.$$('.form-link a');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetPage = link.getAttribute('href').substring(1);
                    this.switchTo(targetPage);
                });
            });
        },
        switchTo(pageId) {
            if (pageId === 'registerPage') {
                this.registerPage.style.display = 'block';
                this.loginPage.style.display = 'none';
            } else if (pageId === 'loginPage') {
                this.registerPage.style.display = 'none';
                this.loginPage.style.display = 'block';
            }
        }
    };

    const forms = {
        validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },
        validatePhone(phone) {
            return /^[0-9]{10,11}$/.test(phone);
        },
        initRegisterForm() {
            const form = utils.$('#registerForm');
            const message = utils.$('#registerMessage');
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
                e.preventDefault();
                let isValid = true;

                Object.entries(validations).forEach(([fieldName, { test, errorId, message }]) => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    const errorElement = utils.$(`#${errorId}`);
                    if (!utils.validateField(field, test, errorElement, message)) {
                        isValid = false;
                        field.classList.add('invalid');
                    } else {
                        field.classList.remove('invalid');
                    }
                });

                if (isValid) {
                    utils.showMessage(message, 'Đăng ký thành công! Vui lòng đăng nhập.', 'success');
                    form.reset();
                    setTimeout(() => formSwitch.switchTo('loginPage'), 2000);
                }
            });
        },
        initLoginForm() {
            const form = utils.$('#loginForm');
            const message = utils.$('#loginMessage');
            if (!form) return;

            const validations = {
                'username': { test: value => value.trim().length > 0, errorId: 'loginUsernameError', message: 'Vui lòng nhập tên đăng nhập' },
                'password': { test: value => value.trim().length > 0, errorId: 'loginPasswordError', message: 'Vui lòng nhập mật khẩu' }
            };

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                let isValid = true;

                Object.entries(validations).forEach(([fieldName, { test, errorId, message }]) => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    const errorElement = utils.$(`#${errorId}`);
                    if (!utils.validateField(field, test, errorElement, message)) {
                        isValid = false;
                        field.classList.add('invalid');
                    } else {
                        field.classList.remove('invalid');
                    }
                });

 
   
            if (isValid) {
                    utils.showMessage(message, 'Thông tin đăng nhập không chính xác!', 'error');
                }
            });
        },
        init() {
            this.initRegisterForm();
            this.initLoginForm();
        }
    };

    const navigation = {
        init() {
            const menuToggle = utils.$('#menuToggle');
            const navWrapper = utils.$('#navWrapper');
            if (!menuToggle || !navWrapper) return;
    
            menuToggle.addEventListener('click', () => {
                navWrapper.classList.toggle('active');
                menuToggle.innerHTML = navWrapper.classList.contains('active')
                    ? '<i class="fas fa-times"></i>'
                    : '<i class="fas fa-bars"></i>';
            });
    
            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.menu-container') && !e.target.closest('#menuToggle')) {
                    navWrapper.classList.remove('active');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });

            // Đảm bảo trạng thái active được áp dụng đúng
            const navItems = utils.$$('.main-nav li a');
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    navItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                });
            });
        }
    };

    const slider = {
        container: utils.$('.slider'),
        slides: utils.$('#slidesContainer'),
        prevBtn: utils.$('#sliderPrev'),
        nextBtn: utils.$('#sliderNext'),
        dotsContainer: utils.$('#sliderDots'),
        slideElements: null,
        currentIndex: 0,
        totalSlides: 0,
        intervalId: null,
        init() {
            if (!this.container || !this.slides) return;
            this.slideElements = this.slides.querySelectorAll('.slide');
            this.totalSlides = this.slideElements.length;
            if (this.totalSlides === 0) return;

            this.createDots();
            if (this.prevBtn) this.prevBtn.addEventListener('click', () => this.prevSlide());
            if (this.nextBtn) this.nextBtn.addEventListener('click', () => this.nextSlide());
            this.setupTouchEvents();
            this.startAutoplay();
            this.container.addEventListener('mouseenter', () => this.stopAutoplay());
            this.container.addEventListener('mouseleave', () => this.startAutoplay());
            this.updateDots();
        },
        createDots() {
            if (!this.dotsContainer) return;
            for (let i = 0; i < this.totalSlides; i++) {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                dot.addEventListener('click', () => this.goToSlide(i));
                this.dotsContainer.appendChild(dot);
            }
        },
        updateDots() {
            if (!this.dotsContainer) return;
            const dots = this.dotsContainer.querySelectorAll('.dot');
            dots.forEach((dot, index) => dot.classList.toggle('active', index === this.currentIndex));
        },
        updatePosition() {
            if (!this.slides) return;
            const offset = -(this.currentIndex * 100);
            this.slides.style.transform = `translateX(${offset}%)`;
            this.updateDots();
        },
        goToSlide(index) {
            this.currentIndex = index;
            this.updatePosition();
        },
        prevSlide() {
            this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.totalSlides - 1;
            this.updatePosition();
        },
        nextSlide() {
            this.currentIndex = this.currentIndex < this.totalSlides - 1 ? this.currentIndex + 1 : 0;
            this.updatePosition();
        },
        setupTouchEvents() {
            if (!this.container) return;
            let touchStartX = 0, touchEndX = 0;
            this.container.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            this.container.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe(touchStartX, touchEndX);
            }, { passive: true });
        },
        handleSwipe(startX, endX) {
            const threshold = 50;
            if (startX - endX > threshold) this.nextSlide();
            else if (endX - startX > threshold) this.prevSlide();
        },
        startAutoplay() {
            this.stopAutoplay();
            this.intervalId = setInterval(() => this.nextSlide(), 5000);
        },
        stopAutoplay() {
            if (this.intervalId) clearInterval(this.intervalId);
        }
    };

    const cart = {
        cartIcon: utils.$('.right-nav a[href="?page=cart"]'),
        cartCount: utils.$('#cartCount'),
        addButtons: utils.$$('.buy-now-btn, .add-to-cart-btn'),
        init() {
            if (!this.addButtons.length) return;
            this.addButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const productCard = e.target.closest('.product-card, .product-detail');
                    if (!productCard) return;
                    const productId = productCard.dataset.id || productCard.getAttribute('data-code');
                    const productName = productCard.dataset.name || productCard.querySelector('h3').textContent;
                    const price = productCard.dataset.price || productCard.querySelector('.product-price').textContent;
                    this.addToCart(productId, productName, price);
                });
            });
        },
        addToCart(productId, productName, price) {
            this.updateCartCount(1);
            this.showNotification(`Đã thêm "${productName}" vào giỏ hàng!`);
        },
        updateCartCount(count) {
            if (!this.cartCount) return;
            const currentCount = parseInt(this.cartCount.textContent) || 0;
            this.cartCount.textContent = currentCount + count;
            this.cartCount.classList.add('pulse');
            setTimeout(() => this.cartCount.classList.remove('pulse'), 300);
        },
        showNotification(message, type = 'success') {
            let notification = utils.$('.cart-notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'cart-notification';
                document.body.appendChild(notification);
            }
            notification.textContent = message;
            notification.className = `cart-notification ${type}`;
            notification.classList.add('show');
            setTimeout(() => notification.classList.remove('show'), 3000);
        }
    };

    const lazyLoad = {
        init() {
            const lazyImages = utils.$$('img[loading="lazy"]');
            if (!lazyImages.length) return;
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            imageObserver.unobserve(img);
                        }
                    });
                });
                lazyImages.forEach(img => img.dataset.src && imageObserver.observe(img));
            } else {
                lazyImages.forEach(img => img.dataset.src && (img.src = img.dataset.src));
            }
        }
    };

    formSwitch.init();
    forms.init();
    navigation.init();
    slider.init();
    cart.init();
    lazyLoad.init();
});

document.addEventListener('DOMContentLoaded', function() {
    // Toggle main menu
    const menuToggle = document.getElementById('menuToggle');
    const navWrapper = document.getElementById('navWrapper');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            const mainNav = navWrapper.querySelector('.main-nav');
            if (mainNav) {
                mainNav.classList.toggle('active');
            }
        });
    }
    
    // Handle dropdowns on mobile
    const dropdownItems = document.querySelectorAll('.has-dropdown');
    
    if (window.innerWidth <= 768) {
        dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.target === this || e.target === this.querySelector('a')) {
                    e.preventDefault();
                    this.classList.toggle('active');
                }
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Đảm bảo dropdown menu nằm trong viewport
    const wineDropdown = document.querySelector('.wine-dropdown');
    const wineMenuItem = document.querySelector('.main-nav > li:nth-child(3)');
    
    if (wineDropdown && wineMenuItem) {
        const menuRect = wineMenuItem.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        
        if (menuRect.left + 780 > viewportWidth) {
            wineDropdown.style.left = 'auto';
            wineDropdown.style.right = '0';
        }
    }
});