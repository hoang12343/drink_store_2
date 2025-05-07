document.addEventListener("DOMContentLoaded", () => {
  const chatboxToggle = document.querySelector(".chatbox-toggle");
  const chatboxContent = document.querySelector(".chatbox-content");
  const chatboxClose = document.querySelector(".chatbox-close");
  const chatboxMessages = document.getElementById("chatboxMessages");
  const chatboxInput = document.getElementById("chatboxInput");
  const chatboxSend = document.getElementById("chatboxSend");

  // Toggle chatbox
  chatboxToggle.addEventListener("click", () => {
    chatboxContent.style.display = "flex";
    chatboxToggle.style.display = "none";
  });

  chatboxClose.addEventListener("click", () => {
    chatboxContent.style.display = "none";
    chatboxToggle.style.display = "flex";
  });

  // Auto-resize textarea
  chatboxInput.addEventListener("input", () => {
    chatboxInput.style.height = "40px";
    chatboxInput.style.height = `${chatboxInput.scrollHeight}px`;
  });

  // Send message
  chatboxSend.addEventListener("click", sendMessage);
  chatboxInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  function sendMessage() {
    const messageText = chatboxInput.value.trim();
    if (!messageText) return;

    // Add user message
    const userMessage = document.createElement("div");
    userMessage.classList.add("message", "user-message");
    userMessage.innerHTML = `<p>${messageText}</p>`;
    chatboxMessages.appendChild(userMessage);

    // Clear input
    chatboxInput.value = "";
    chatboxInput.style.height = "40px";

    // Scroll to bottom
    chatboxMessages.scrollTop = chatboxMessages.scrollHeight;

    // Simulate AI response (replace with actual API call)
    setTimeout(() => {
      const botMessage = document.createElement("div");
      botMessage.classList.add("message", "bot-message");

      // Simple AI response logic (replace with API call)
      let response =
        "Tôi không chắc về câu hỏi của bạn. Bạn có thể hỏi thêm về sản phẩm không?";
      if (messageText.toLowerCase().includes("rượu vang")) {
        response =
          "Chúng tôi có nhiều loại rượu vang cao cấp! Bạn thích rượu vang đỏ hay trắng?";
      } else if (messageText.toLowerCase().includes("giá")) {
        response =
          "Giá sản phẩm phụ thuộc vào loại đồ uống. Bạn có thể cho tôi biết sản phẩm cụ thể không?";
      } else if (messageText.toLowerCase().includes("giao hàng")) {
        response =
          "Chúng tôi miễn phí giao hàng cho đơn từ 1.000.000 VNĐ. Bạn muốn đặt hàng ngay không?";
      }

      botMessage.innerHTML = `<p>${response}</p>`;
      chatboxMessages.appendChild(botMessage);
      chatboxMessages.scrollTop = chatboxMessages.scrollHeight;
    }, 1000);
  }

  // Replace the setTimeout with an actual API call in production
  /*
    async function getAIResponse(message) {
        try {
            const response = await fetch('YOUR_AI_API_ENDPOINT', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });
            const data = await response.json();
            return data.reply;
        } catch (error) {
            console.error('Error fetching AI response:', error);
            return 'Có lỗi xảy ra. Vui lòng thử lại sau!';
        }
    }
    */
});
