(function () {
  if (window.BannerSlider) {
    console.warn("BannerSlider already defined, skipping initialization.");
    return;
  }

  const BannerSlider = {
    container: null,
    slides: null,
    prevBtn: null,
    nextBtn: null,
    dotsContainer: null,
    slideElements: null,
    currentIndex: 0,
    totalSlides: 0,
    intervalId: null,

    init() {
      this.container = document.querySelector(".banner-slider");
      this.slides = this.container?.querySelector(".banner-slides");
      this.prevBtn = this.container?.querySelector(".banner-prev");
      this.nextBtn = this.container?.querySelector(".banner-next");
      this.dotsContainer = this.container?.querySelector(".banner-dots");

      if (!this.container || !this.slides) {
        console.error("Banner slider container or slides not found.");
        return;
      }

      this.slideElements = this.slides.querySelectorAll(".banner-slide");
      this.totalSlides = this.slideElements.length;

      if (this.totalSlides === 0) {
        console.warn("No slides found in the banner slider.");
        return;
      }

      this.createDots();
      this.bindEvents();
      this.updatePosition();
      this.startAutoplay();
    },

    createDots() {
      if (!this.dotsContainer) return;
      this.dotsContainer.innerHTML = "";
      for (let i = 0; i < this.totalSlides; i++) {
        const dot = document.createElement("span");
        dot.classList.add("banner-dot");
        dot.setAttribute("aria-label", `Go to slide ${i + 1}`);
        dot.setAttribute("role", "button");
        dot.addEventListener("click", () => this.goToSlide(i));
        this.dotsContainer.appendChild(dot);
      }
    },

    updateDots() {
      if (!this.dotsContainer) return;
      const dots = this.dotsContainer.querySelectorAll(".banner-dot");
      dots.forEach((dot, index) => {
        dot.classList.toggle("active", index === this.currentIndex);
        dot.setAttribute("aria-selected", index === this.currentIndex);
      });
    },

    updatePosition() {
      if (!this.slides) return;
      this.slides.style.transform = `translateX(-${this.currentIndex * 100}%)`;
      this.updateDots();
      this.lazyLoadImages();
    },

    lazyLoadImages() {
      const currentSlide = this.slideElements[this.currentIndex];
      const img = currentSlide.querySelector("img");
      if (img && img.dataset.src) {
        img.src = img.dataset.src;
        img.removeAttribute("data-src");
      }
    },

    goToSlide(index) {
      this.currentIndex = (index + this.totalSlides) % this.totalSlides;
      this.updatePosition();
    },

    prevSlide() {
      this.currentIndex =
        this.currentIndex > 0 ? this.currentIndex - 1 : this.totalSlides - 1;
      this.updatePosition();
    },

    nextSlide() {
      this.currentIndex =
        this.currentIndex < this.totalSlides - 1 ? this.currentIndex + 1 : 0;
      this.updatePosition();
    },

    bindEvents() {
      if (this.prevBtn) {
        this.prevBtn.addEventListener("click", () => this.prevSlide());
        this.prevBtn.setAttribute("aria-label", "Previous slide");
      }
      if (this.nextBtn) {
        this.nextBtn.addEventListener("click", () => this.nextSlide());
        this.nextBtn.setAttribute("aria-label", "Next slide");
      }

      let touchStartX = 0;
      this.container.addEventListener(
        "touchstart",
        (e) => (touchStartX = e.changedTouches[0].screenX),
        { passive: true }
      );
      this.container.addEventListener(
        "touchend",
        (e) => {
          const touchEndX = e.changedTouches[0].screenX;
          const threshold = 50;
          if (touchStartX - touchEndX > threshold) this.nextSlide();
          else if (touchEndX - touchStartX > threshold) this.prevSlide();
        },
        { passive: true }
      );

      this.container.addEventListener("mouseenter", () => this.stopAutoplay());
      this.container.addEventListener("mouseleave", () => this.startAutoplay());

      this.container.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") this.prevSlide();
        if (e.key === "ArrowRight") this.nextSlide();
      });
    },

    startAutoplay() {
      this.stopAutoplay();
      this.intervalId = setInterval(() => this.nextSlide(), 5000);
    },

    stopAutoplay() {
      if (this.intervalId) clearInterval(this.intervalId);
    },
  };

  window.BannerSlider = BannerSlider;
  document.addEventListener("DOMContentLoaded", () => BannerSlider.init());
})();
