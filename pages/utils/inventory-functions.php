<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

function get_inventory($limit = 10, $page = 1)
{
    global $pdo;
    $offset = ($page - 1) * $limit;

    try {
        $stmt = $pdo->prepare("
            SELECT i.id, i.product_id, i.quantity, i.location, i.last_updated, p.code, p.name 
            FROM inventory i
            JOIN products p ON i.product_id = p.id
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $inventory = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT COUNT(*) FROM inventory");
        $total_records = $stmt->fetchColumn();

        return [
            'inventory' => $inventory,
            'total_records' => $total_records
        ];
    } catch (PDOException $e) {
        return ['inventory' => [], 'total_records' => 0, 'error' => $e->getMessage()];
    }
}

function get_inventory_by_id($id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT i.id, i.product_id, i.quantity, i.location, i.last_updated, p.code, p.name 
            FROM inventory i
            JOIN products p ON i.product_id = p.id
            WHERE i.id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}