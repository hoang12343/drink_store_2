const slider = {
  container: utils.$(".slider"),
  slides: utils.$("#slidesContainer"),
  prevBtn: utils.$("#sliderPrev"),
  nextBtn: utils.$("#sliderNext"),
  dotsContainer: utils.$("#sliderDots"),
  slideElements: null,
  currentIndex: 0,
  totalSlides: 0,
  intervalId: null,
  init() {
    if (!this.container || !this.slides) return;
    this.slideElements = this.slides.querySelectorAll(".slide");
    this.totalSlides = this.slideElements.length;
    if (this.totalSlides === 0) return;

    this.createDots();
    if (this.prevBtn)
      this.prevBtn.addEventListener("click", () => this.prevSlide());
    if (this.nextBtn)
      this.nextBtn.addEventListener("click", () => this.nextSlide());
    this.setupTouchEvents();
    this.startAutoplay();
    this.container.addEventListener("mouseenter", () => this.stopAutoplay());
    this.container.addEventListener("mouseleave", () => this.startAutoplay());
    this.updateDots();
  },
  createDots() {
    if (!this.dotsContainer) return;
    for (let i = 0; i < this.totalSlides; i++) {
      const dot = document.createElement("div");
      dot.classList.add("dot");
      dot.addEventListener("click", () => this.goToSlide(i));
      this.dotsContainer.appendChild(dot);
    }
  },
  updateDots() {
    if (!this.dotsContainer) return;
    const dots = this.dotsContainer.querySelectorAll(".dot");
    dots.forEach((dot, index) =>
      dot.classList.toggle("active", index === this.currentIndex)
    );
  },
  updatePosition() {
    if (!this.slides) return;
    const offset = -(this.currentIndex * 100);
    this.slides.style.transform = `translateX(${offset}%)`;
    this.updateDots();
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
  setupTouchEvents() {
    if (!this.container) return;
    let touchStartX = 0,
      touchEndX = 0;
    this.container.addEventListener(
      "touchstart",
      (e) => {
        touchStartX = e.changedTouches[0].screenX;
      },
      { passive: true }
    );
    this.container.addEventListener(
      "touchend",
      (e) => {
        touchEndX = e.changedTouches[0].screenX;
        this.handleSwipe(touchStartX, touchEndX);
      },
      { passive: true }
    );
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
  },
};

slider.init();
