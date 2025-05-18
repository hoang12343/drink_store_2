<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';

// Initialize variables
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT) ?? 1;
$page = max(1, (int)$page);
$comments_per_page = 10;
$success_message = '';
$error_message = '';
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

// Handle comment actions (approve, reject, delete)
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action) {
    try {
        $comment_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$comment_id) {
            throw new Exception('ID bình luận không hợp lệ.');
        }

        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE product_comments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$comment_id]);
            $success_message = 'Bình luận đã được duyệt thành công.';
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE product_comments SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$comment_id]);
            $success_message = 'Bình luận đã bị từ chối.';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM product_comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $success_message = 'Bình luận đã được xóa thành công.';
        } else {
            throw new Exception('Hành động không hợp lệ.');
        }
    } catch (Exception $e) {
        $error_message = 'Lỗi khi thực hiện hành động: ' . $e->getMessage();
        error_log("Error performing comment action: " . $e->getMessage());
    }
}

// Handle comment editing
if (isset($_POST['edit_comment'])) {
    try {
        $comment_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $comment_text = trim($_POST['comment_text'] ?? '');
        if (!$comment_id || !$comment_text) {
            throw new Exception('Dữ liệu bình luận không hợp lệ.');
        }

        $stmt = $pdo->prepare("UPDATE product_comments SET comment_text = ? WHERE id = ?");
        $stmt->execute([$comment_text, $comment_id]);
        $success_message = 'Bình luận đã được cập nhật thành công.';
    } catch (Exception $e) {
        $error_message = 'Lỗi khi cập nhật bình luận: ' . $e->getMessage();
        error_log("Error updating comment: " . $e->getMessage());
    }
}

// Fetch comments with pagination
try {
    $where = $search ? "AND pc.comment_text LIKE ?" : "";
    $params = $search ? ["%$search%"] : [];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM product_comments pc
        JOIN users u ON pc.user_id = u.id
        JOIN products p ON pc.product_id = p.id
        WHERE 1=1 $where
    ");
    $stmt->execute($params);
    $total_comments = $stmt->fetchColumn();
    $total_pages = ceil($total_comments / $comments_per_page);

    $offset = ($page - 1) * $comments_per_page;
    $stmt = $pdo->prepare("
        SELECT pc.id, pc.comment_text, pc.created_at, pc.status, u.full_name, p.name as product_name
        FROM product_comments pc
        JOIN users u ON pc.user_id = u.id
        JOIN products p ON pc.product_id = p.id
        WHERE 1=1 $where
        ORDER BY pc.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$comments_per_page, $offset]));
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy danh sách bình luận: ' . $e->getMessage();
    $comments = [];
    error_log("Error fetching comments: " . $e->getMessage());
}

// Fetch comment for editing
$edit_comment = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id) {
        try {
            $stmt = $pdo->prepare("
                SELECT pc.id, pc.comment_text, pc.status, u.full_name, p.name as product_name
                FROM product_comments pc
                JOIN users u ON pc.user_id = u.id
                JOIN products p ON pc.product_id = p.id
                WHERE pc.id = ?
            ");
            $stmt->execute([$edit_id]);
            $edit_comment = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error_message = 'Lỗi khi lấy thông tin bình luận: ' . $e->getMessage();
            error_log("Error fetching comment for edit: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bình luận - Quản trị</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin-products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/admin/admin-comments.js?v=<?php echo time(); ?>" defer></script>
    <style>
    .status-approved {
        color: #28a745;
        font-weight: bold;
    }

    .status-rejected {
        color: #dc3545;
        font-weight: bold;
    }

    .status-pending {
        color: #ffc107;
        font-weight: bold;
    }

    .edit-modal,
    .delete-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .edit-modal-content,
    .delete-modal-content {
        background: var(--white);
        padding: 20px;
        border-radius: 5px;
        width: 80%;
        max-width: 600px;
    }

    .edit-modal-content h2,
    .delete-modal-content h3 {
        margin-top: 0;
        color: var(--primary);
    }

    .edit-modal-content textarea {
        width: 100%;
        height: 100px;
        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .btn-cancel,
    .cancel-btn {
        background: var(--danger);
        color: var(--white);
    }

    .btn,
    .confirm-btn {
        background: var(--primary);
        color: var(--white);
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }

    .btn.small {
        padding: 6px 12px;
    }

    .btn.danger {
        background: var(--danger);
        color: var(--white);
    }

    .btn-toggle {
        background: var(--warning);
        color: var(--white);
        padding: 6px 12px;
        margin-right: 5px;
        border-radius: 4px;
    }

    .btn-toggle.approved {
        background: var(--success);
    }

    .btn-toggle.active {
        opacity: 0.7;
    }

    .products-table th,
    .products-table td {
        padding: 12px;
        text-align: left;
    }

    .search-container {
        margin-bottom: 20px;
    }

    .search-input {
        padding: 8px;
        width: 100%;
        max-width: 300px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .pagination {
        margin-top: 20px;
        text-align: center;
    }

    .pagination-btn {
        padding: 8px 12px;
        margin: 0 5px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        text-decoration: none;
        color: var(--primary);
    }

    .pagination-btn.active {
        background: var(--primary);
        color: var(--white);
    }

    .pagination-btn:hover {
        background: var(--primary-light);
    }

    @media (max-width: 768px) {

        .products-table th,
        .products-table td {
            padding: 8px;
            font-size: 14px;
        }

        .btn,
        .btn-toggle,
        .confirm-btn,
        .cancel-btn {
            padding: 6px 10px;
            font-size: 12px;
        }

        .search-input {
            max-width: 100%;
        }
    }
    </style>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý bình luận</h1>

        <?php if ($success_message): ?>
        <div class="form-message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php elseif ($error_message): ?>
        <div class="form-message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Search and List Comments -->
        <div class="admin-products">
            <h2>Danh sách bình luận</h2>
            <div class="search-container">
                <input type="text" id="comment-search" placeholder="Tìm kiếm bình luận theo nội dung..."
                    class="search-input">
            </div>
            <?php if (empty($comments)): ?>
            <p>Không có bình luận nào.</p>
            <?php else: ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Người dùng</th>
                        <th>Bình luận</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($comment['id']); ?></td>
                        <td><?php echo htmlspecialchars($comment['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($comment['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($comment['comment_text']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></td>
                        <td>
                            <span class="status-<?php echo htmlspecialchars($comment['status']); ?>">
                                <?php
                                        echo $comment['status'] === 'approved' ? 'Đã duyệt' :
                                             ($comment['status'] === 'rejected' ? 'Bị từ chối' : 'Đang chờ');
                                        ?>
                            </span>
                        </td>
                        <td>
                            <button type="button"
                                class="btn-toggle small <?php echo $comment['status'] === 'approved' ? 'approved active' : ''; ?> approve-comment-btn"
                                data-id="<?php echo $comment['id']; ?>" title="Duyệt bình luận">
                                <i class="fas fa-check"></i> Duyệt
                            </button>
                            <button type="button"
                                class="btn-toggle small <?php echo $comment['status'] === 'rejected' ? 'active' : ''; ?> reject-comment-btn"
                                data-id="<?php echo $comment['id']; ?>" title="Từ chối bình luận">
                                <i class="fas fa-ban"></i> Từ chối
                            </button>
                            <a href="?page=admin&subpage=admin-comments&edit=<?php echo $comment['id']; ?>"
                                class="btn small" title="Sửa bình luận">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <button type="button" class="btn small danger delete-comment-btn"
                                data-id="<?php echo $comment['id']; ?>"
                                data-text="<?php echo htmlspecialchars(substr($comment['comment_text'], 0, 50)); ?>..."
                                title="Xóa bình luận">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php
                        $query_params = $_GET;
                        unset($query_params['p']);
                        $base_url = 'index.php?' . http_build_query($query_params);
                        ?>
                <?php if ($page > 1): ?>
                <a href="<?php echo $base_url; ?>&p=<?php echo $page - 1; ?>" class="pagination-btn prev">Trước</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="<?php echo $base_url; ?>&p=<?php echo $i; ?>"
                    class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                <a href="<?php echo $base_url; ?>&p=<?php echo $page + 1; ?>" class="pagination-btn next">Sau</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Edit Comment Modal -->
        <?php if ($edit_comment): ?>
        <div class="edit-modal" id="edit-comment-modal" style="display: flex;">
            <?php else: ?>
            <div class="edit-modal" id="edit-comment-modal" style="display: none;">
                <?php endif; ?>
                <div class="edit-modal-content">
                    <h2>Sửa bình luận</h2>
                    <form action="?page=admin&subpage=admin-comments" method="post">
                        <input type="hidden" name="id" value="<?php echo $edit_comment['id'] ?? ''; ?>">
                        <div class="form-group">
                            <label for="comment_text">Nội dung bình luận</label>
                            <textarea name="comment_text" id="comment_text"
                                required><?php echo htmlspecialchars($edit_comment['comment_text'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="edit_comment" class="btn">Cập nhật</button>
                        <button type="button" class="btn-cancel" onclick="$('#edit-comment-modal').hide();">Hủy</button>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="delete-modal" id="delete-comment-modal" style="display: none;">
                <div class="delete-modal-content">
                    <h3>Xác nhận xóa bình luận</h3>
                    <p>Bạn có chắc muốn xóa bình luận "<span id="delete-comment-text"></span>"?</p>
                    <form action="?page=admin&subpage=admin-comments" method="post" id="delete-comment-form">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete-comment-id">
                        <button type="submit" class="confirm-btn">Xóa</button>
                        <button type="button" class="cancel-btn"
                            onclick="$('#delete-comment-modal').hide();">Hủy</button>
                    </form>
                </div>
            </div>
    </section>
</body>

</html>