<?php
require_once 'config.php';

class OrderHistory {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function getAllOrders() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.id, o.user_id, o.order_date, o.status, 
                       oi.menu_item_id, m.name AS menu_name, oi.quantity
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN menu_items m ON oi.menu_item_id = m.id
                ORDER BY o.order_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>
