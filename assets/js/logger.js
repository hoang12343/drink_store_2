// assets/js/logger.js
class Logger {
  static log(message, context = {}, level = "ERROR") {
    const timestamp = new Date().toLocaleString("vi-VN", {
      timeZone: "Asia/Ho_Chi_Minh",
    });
    const userId = context.user_id || "Guest";
    const contextStr = Object.keys(context).length
      ? JSON.stringify(context, null, 2)
      : "";
    const logMessage = `[${timestamp}] [${level}] [User: ${userId}] ${message} ${contextStr}`;

    // In ra console dựa trên mức độ
    switch (level.toUpperCase()) {
      case "ERROR":
        console.error(logMessage);
        break;
      case "INFO":
        console.info(logMessage);
        break;
      case "DEBUG":
        console.debug(logMessage);
        break;
      default:
        console.log(logMessage);
    }
  }

  static error(message, context = {}) {
    this.log(message, context, "ERROR");
  }

  static info(message, context = {}) {
    this.log(message, context, "INFO");
  }

  static debug(message, context = {}) {
    this.log(message, context, "DEBUG");
  }
}

// Hàm để nhận log từ PHP qua inline script
function logFromPHP(messages) {
  if (Array.isArray(messages)) {
    messages.forEach(({ message, context, level }) => {
      Logger.log(message, context, level);
    });
  }
}
