<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
define('ROOT_PATH', __DIR__ . '/..');
// Kiểm tra nếu BASE_URL chưa được định nghĩa để tránh lỗi
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
}

require_once ROOT_PATH . '/includes/db_connect.php';
require_once ROOT_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$success_message = filter_input(INPUT_GET, 'success', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$error_message = filter_input(INPUT_GET, 'error', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$search_name = filter_input(INPUT_GET, 'search_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$search_email = filter_input(INPUT_GET, 'search_email', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$search_subject = filter_input(INPUT_GET, 'search_subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$current_page = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]) ?: 1;
$items_per_page = 10;
$offset = ($current_page - 1) * $items_per_page;
$contacts = [];
$total_pages = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $contact_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($contact_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = :id");
                $stmt->bindValue(':id', $contact_id, PDO::PARAM_INT);
                $stmt->execute();

                if (function_exists('glob')) {
                    array_map('unlink', glob(ROOT_PATH . '/cache/contacts_*.cache'));
                }

                $success_message = 'Xóa tin nhắn thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi xóa tin nhắn: ' . $e->getMessage();
            }
        }
    }
}

$query = "SELECT * FROM contacts WHERE 1=1";
$params = [];
if ($search_name) {
    $query .= " AND name LIKE :name";
    $params[':name'] = "%$search_name%";
}
if ($search_email) {
    $query .= " AND email LIKE :email";
    $params[':email'] = "%$search_email%";
}
if ($search_subject) {
    $query .= " AND subject LIKE :subject";
    $params[':subject'] = "%$search_subject%";
}

$cache_key = 'contacts_' . md5($query . serialize($params) . $offset . $items_per_page);
$cache_file = ROOT_PATH . '/cache/' . $cache_key . '.cache';
$cache_time = 300;

try {
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        $cached_data = unserialize(file_get_contents($cache_file));
        $contacts = $cached_data['contacts'] ?? [];
        $total_pages = $cached_data['total_pages'] ?? 1;
    } else {
        $count_stmt = $pdo->prepare($query);
        $count_stmt->execute($params);
        $total_records = $count_stmt->rowCount();
        $total_pages = ceil($total_records / $items_per_page);

        $query .= " ORDER BY created_at DESC LIMIT :offset, :items_per_page";
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!is_dir(ROOT_PATH . '/cache')) {
            mkdir(ROOT_PATH . '/cache', 0755, true);
        }
        file_put_contents($cache_file, serialize([
            'contacts' => $contacts,
            'total_pages' => $total_pages
        ]));
    }
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy dữ liệu: ' . $e->getMessage();
    $contacts = [];
    $total_pages = 1;
}

if (isset($_GET['export']) && $_GET['export'] === 'xlsx') {
    try {
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh sách Liên hệ');

        $headers = ['ID', 'Họ và Tên', 'Email', 'Số Điện Thoại', 'Tiêu Đề', 'Nội Dung', 'Ngày Gửi', 'Đã Đọc', 'Quan Trọng'];
        $sheet->fromArray($headers, NULL, 'A1');

        $row = 2;
        foreach ($contacts as $contact) {
            $sheet->fromArray([
                $contact['id'],
                $contact['name'],
                $contact['email'],
                $contact['phone'] ?: 'N/A',
                $contact['subject'],
                $contact['message'],
                date('d/m/Y H:i', strtotime($contact['created_at'])),
                $contact['is_read'] ? 'Có' : 'Không',
                $contact['is_important'] ? 'Có' : 'Không'
            ], NULL, "A$row");
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'contacts_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    } catch (Exception $e) {
        $error_message = 'Lỗi khi xuất Excel: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Liên hệ - Quản trị</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-contacts.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="<?php echo BASE_URL; ?>assets/js/admin.js" defer></script>
    <!-- Thêm SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/admin/admin-contacts.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý Liên hệ</h1>

        <?php if ($success_message): ?>
            <div class="form-message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="form-message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Form lọc -->
        <form action="?page=admin&subpage=admin-contacts" method="get" class="report-filter-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="subpage" value="admin-contacts">
            <div class="form-group">
                <label for="search_name">Họ và Tên</label>
                <input type="text" id="search_name" name="search_name"
                    value="<?php echo htmlspecialchars($search_name); ?>" placeholder="Tìm theo tên">
            </div>
            <div class="form-group">
                <label for="search_email">Email</label>
                <input type="text" id="search_email" name="search_email"
                    value="<?php echo htmlspecialchars($search_email); ?>" placeholder="Tìm theo email">
            </div>
            <div class="form-group">
                <label for="search_subject">Tiêu Đề</label>
                <input type="text" id="search_subject" name="search_subject"
                    value="<?php echo htmlspecialchars($search_subject); ?>" placeholder="Tìm theo tiêu đề">
            </div>
            <button type="submit" class="btn">Lọc</button>
            <button type="button" class="btn btn-export" onclick="exportContacts()">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </button>
        </form>

        <!-- Bảng liên hệ -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ và Tên</th>
                        <th>Email</th>
                        <th>Số Điện Thoại</th>
                        <th>Tiêu Đề</th>
                        <th>Nội Dung</th>
                        <th>Ngày Gửi</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">Không có tin nhắn nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($contact['id']); ?></td>
                                <td data-label="Họ và Tên"><?php echo htmlspecialchars($contact['name']); ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($contact['email']); ?></td>
                                <td data-label="Số Điện Thoại"><?php echo htmlspecialchars($contact['phone'] ?: 'N/A'); ?></td>
                                <td data-label="Tiêu Đề"><?php echo htmlspecialchars($contact['subject']); ?></td>
                                <td data-label="Nội Dung"><?php echo nl2br(htmlspecialchars($contact['message'])); ?></td>
                                <td data-label="Ngày Gửi"><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?>
                                </td>
                                <td data-label="Trạng Thái">
                                    <button class="btn btn-toggle-read" data-id="<?php echo $contact['id']; ?>"
                                        data-read="<?php echo $contact['is_read']; ?>">
                                        <?php echo $contact['is_read'] ? 'Đã đọc' : 'Chưa đọc'; ?>
                                    </button>
                                    <button class="btn btn-toggle-important" data-id="<?php echo $contact['id']; ?>"
                                        data-important="<?php echo $contact['is_important']; ?>">
                                        <?php echo $contact['is_important'] ? 'Quan trọng' : 'Bình thường'; ?>
                                    </button>
                                </td>
                                <td data-label="Hành Động">
                                    <form action="?page=admin&subpage=admin-contacts" method="post" class="delete-form"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                    <button class="btn btn-reply" data-id="<?php echo $contact['id']; ?>"
                                        data-email="<?php echo htmlspecialchars($contact['email']); ?>"
                                        data-subject="<?php echo htmlspecialchars($contact['subject']); ?>">
                                        <i class="fas fa-reply"></i> Trả lời
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=admin&subpage=admin-contacts&p=<?php echo $current_page - 1; ?>&search_name=<?php echo urlencode($search_name); ?>&search_email=<?php echo urlencode($search_email); ?>&search_subject=<?php echo urlencode($search_subject); ?>"
                        class="btn">« Trước</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=admin&subpage=admin-contacts&p=<?php echo $i; ?>&search_name=<?php echo urlencode($search_name); ?>&search_email=<?php echo urlencode($search_email); ?>&search_subject=<?php echo urlencode($search_subject); ?>"
                        class="btn <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&subpage=admin-contacts&p=<?php echo $current_page + 1; ?>&search_name=<?php echo urlencode($search_name); ?>&search_email=<?php echo urlencode($search_email); ?>&search_subject=<?php echo urlencode($search_subject); ?>"
                        class="btn">Sau »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Modal trả lời -->
        <div class="modal" id="replyModal" style="display: none;">
            <div class="modal-content">
                <h2>Trả lời Tin Nhắn</h2>
                <form id="replyForm" action="<?php echo BASE_URL; ?>processes/reply_contact.php" method="post">
                    <input type="hidden" name="contact_id" id="replyContactId">
                    <div class="form-group">
                        <label for="reply_email">Gửi tới</label>
                        <input type="email" id="reply_email" name="email" readonly>
                    </div>
                    <div class="form-group">
                        <label for="reply_subject">Tiêu đề</label>
                        <input type="text" id="reply_subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="reply_message">Nội dung</label>
                        <textarea id="reply_message" name="message" required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn">Gửi</button>
                        <button type="button" class="btn btn-cancel" onclick="closeReplyModal()">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
?>