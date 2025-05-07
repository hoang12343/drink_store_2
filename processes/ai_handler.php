<?php
require_once __DIR__ . '/../config.php'; // Kết nối cơ sở dữ liệu

// Cấu hình Dialogflow
$projectId = 'drinkstorebot'; // Thay bằng project_id từ file JSON
$sessionId = 'chatbox-session-' . session_id();
$languageCode = 'vi';

// Đọc file JSON key để lấy thông tin xác thực
$keyFile = __DIR__ . '/../keys/drinkstorebot-abc123.json';
$credentials = json_decode(file_get_contents($keyFile), true);
$clientEmail = $credentials['client_email'];
$privateKey = $credentials['private_key'];

// Tạo JWT để xác thực với Google API
function createJwt($clientEmail, $privateKey)
{
    $iat = time();
    $exp = $iat + 3600;
    $payload = [
        'iss' => $clientEmail,
        'sub' => $clientEmail,
        'aud' => 'https://dialogflow.googleapis.com/',
        'iat' => $iat,
        'exp' => $exp,
        'scope' => 'https://www.googleapis.com/auth/cloud-platform'
    ];

    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $base64Header = base64_encode(json_encode($header));
    $base64Payload = base64_encode(json_encode($payload));
    $unsignedToken = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header . '.' . $base64Payload);

    openssl_sign($unsignedToken, $signature, $privateKey, 'SHA256');
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $unsignedToken . '.' . $base64Signature;
}

// Lấy access token từ Google
$jwt = createJwt($clientEmail, $privateKey);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwt
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to authenticate with Google']);
    exit;
}

// Nhận tin nhắn từ client
$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim($input['message']) : '';

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing message']);
    exit;
}

// Gửi yêu cầu tới Dialogflow
$requestBody = [
    'queryInput' => [
        'text' => [
            'text' => $message,
            'languageCode' => $languageCode
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://dialogflow.googleapis.com/v2/projects/$projectId/agent/sessions/$sessionId:detectIntent");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: ' => 'application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
$intentName = $responseData['queryResult']['intent']['displayName'] ?? '';
$botResponse = $responseData['queryResult']['fulfillmentText'] ?? 'Tôi không hiểu câu hỏi của bạn.';

// Xử lý thêm thông tin từ cơ sở dữ liệu nếu có ý định cụ thể
if ($intentName === 'HoiVeRuouVang') {
    $stmt = $conn->prepare("SELECT name FROM products WHERE category_id = 2 LIMIT 1");
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $botResponse = "Chúng tôi có {$product['name']} thuộc danh mục rượu vang. Bạn thích rượu vang đỏ hay trắng?";
    }
} elseif ($intentName === 'GetProductPrice' || strpos(strtolower($message), 'giá') !== false) {
    $productName = trim(str_replace(['giá', 'của'], '', strtolower($message)));
    $stmt = $conn->prepare("SELECT name, price FROM products WHERE LOWER(name) LIKE :name LIMIT 1");
    $stmt->execute([':name' => "%$productName%"]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $botResponse = "Giá của {$product['name']} là " . number_format($product['price'], 0, ',', '.') . " VNĐ.";
    } else {
        $botResponse = "Xin lỗi, tôi không tìm thấy thông tin giá cho sản phẩm bạn hỏi. Bạn có thể cung cấp tên sản phẩm cụ thể hơn không?";
    }
} elseif ($intentName === 'CheckProductAvailability' || strpos(strtolower($message), 'có') !== false) {
    $productName = trim(str_replace(['có', 'không'], '', strtolower($message)));
    $stmt = $conn->prepare("SELECT name, stock FROM products WHERE LOWER(name) LIKE :name LIMIT 1");
    $stmt->execute([':name' => "%$productName%"]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $botResponse = $product['stock'] > 0 ? "Có, {$product['name']} còn {$product['stock']} sản phẩm trong kho." : "Xin lỗi, {$product['name']} hiện đã hết hàng.";
    } else {
        $botResponse = "Xin lỗi, tôi không tìm thấy sản phẩm bạn hỏi. Bạn có thể kiểm tra lại tên không?";
    }
}

// Trả về phản hồi
header('Content-Type: application/json');
echo json_encode(['response' => $botResponse]);
