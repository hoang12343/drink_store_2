document.addEventListener("DOMContentLoaded", () => {
  const utils = {
    $(selector) {
      return document.querySelector(selector);
    },
    $$(selector) {
      return document.querySelectorAll(selector);
    },
  };

  const search = {
    searchForm: utils.$("#searchForm"),
    searchInput: utils.$("#searchInput"),
    suggestionsContainer: utils.$("#searchSuggestions"),
    init() {
      if (!this.searchForm || !this.searchInput || !this.suggestionsContainer) {
        console.warn("Search components not found. Search disabled.");
        return;
      }

      // Ngăn gửi form nếu input rỗng
      this.searchForm.addEventListener("submit", (e) => {
        if (!this.searchInput.value.trim()) {
          e.preventDefault();
          this.searchInput.focus();
        }
      });

      // Xử lý input với debounce
      this.searchInput.addEventListener(
        "input",
        this.debounce(() => {
          const query = this.searchInput.value.trim();
          if (query.length >= 2) {
            this.fetchSuggestions(query);
          } else {
            this.clearSuggestions();
          }
        }, 300)
      );

      // Hiển thị gợi ý khi focus vào input
      this.searchInput.addEventListener("focus", () => {
        const query = this.searchInput.value.trim();
        if (query.length >= 2) {
          this.fetchSuggestions(query);
        }
      });

      // Ẩn gợi ý khi click bên ngoài
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".search-bar")) {
          this.clearSuggestions();
        }
      });
    },
    debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },
    async fetchSuggestions(query) {
      try {
        const response = await fetch(
          `processes/search-suggestions.php?search=${encodeURIComponent(query)}`
        );
        if (!response.ok) throw new Error("Network response was not ok");
        const suggestions = await response.json();
        this.renderSuggestions(suggestions, query);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        this.clearSuggestions();
      }
    },
    renderSuggestions(suggestions, query) {
      this.clearSuggestions();
      if (suggestions.length === 0) {
        const div = document.createElement("div");
        div.className = "suggestion-item no-results";
        div.textContent = "Không tìm thấy sản phẩm";
        this.suggestionsContainer.appendChild(div);
        this.suggestionsContainer.classList.add("active");
        return;
      }

      suggestions.forEach((item) => {
        const div = document.createElement("div");
        div.className = "suggestion-item";
        const highlightedName = item.name.replace(
          new RegExp(query, "gi"),
          "<strong>$&</strong>"
        );
        div.innerHTML = `
            <img src="${item.image || "/api/placeholder/40/40"}" alt="${
          item.name
        }">
            <span>${highlightedName}</span>
          `;
        div.addEventListener("click", () => {
          window.location.href = `index.php?page=product-detail&id=${item.id}`;
          this.clearSuggestions();
        });
        this.suggestionsContainer.appendChild(div);
      });
      this.suggestionsContainer.classList.add("active");
    },
    clearSuggestions() {
      this.suggestionsContainer.innerHTML = "";
      this.suggestionsContainer.classList.remove("active");
    },
  };

  search.init();
});
