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
            setTimeout(() => element.style.display = 'none', 3000);
        },
        validateField(field, test, errorElement, errorMessage) {
            if (!field) return false;
            const isValid = test(field.value);
            this.showError(errorElement, errorMessage, !isValid);
            return isValid;
        }
    };

    const formSwitch = {
        registerPage: utils.$('#registerPage'),
        loginPage: utils.$('#loginPage'),
        init() {
            if (!this.registerPage || !this.loginPage) {
                console.error('Register or Login page not found in DOM');
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
            // Khởi tạo trạng thái ban đầu dựa trên URL
            const currentPage = window.location.search.includes('page=register') ? 'registerPage' : 'loginPage';
            this.switchTo(currentPage);
        },
        switchTo(pageId) {
            if (!this.registerPage || !this.loginPage) {
                console.error('Cannot switch: One or both pages are missing');
                return;
            }
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
                } else if (!message) {
                    // Nếu không có message element, gửi form bình thường
                    form.submit();
                } else {
                    e.preventDefault();
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
                } else if (!message) {
                    form.submit();
                } else {
                    e.preventDefault();
                    utils.showMessage(message, 'Thông tin đăng nhập không chính xác!', 'error');
                }
            });
        },
        init() {
            this.initRegisterForm();
            this.initLoginForm();
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
                    const productName = productCard.dataset.name || productCard.querySelector('h3')?.textContent;
                    const price = productCard.dataset.price || productCard.querySelector('.product-price')?.textContent;
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
   
    slider.init();
    cart.init();
    lazyLoad.init();
});