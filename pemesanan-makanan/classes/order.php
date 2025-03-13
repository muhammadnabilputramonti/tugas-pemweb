<?php
require_once '../config/database.php';

class Order {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function placeOrder($user_id, $orderItems) {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO orders (user_id) VALUES (:user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $order_id = $this->conn->lastInsertId();

            foreach ($orderItems as $item) {
                $query = "INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (:order_id, :menu_item_id, :quantity)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':menu_item_id', $item['id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $exception) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
