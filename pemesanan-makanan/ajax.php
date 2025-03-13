<?php
require_once 'config.php';

$database = new Database();
$pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'getMenu') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM menu_items");
            $stmt->execute();
            $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($menuItems);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Gagal mengambil data menu: ' . $e->getMessage()]);
        }
    } elseif ($action === 'placeOrder') {
        $orderItems = json_decode($_POST['orderItems'], true);
        $customerName = $_POST['customerName'];

        if (empty($orderItems)) {
            echo json_encode(['success' => false, 'message' => 'Keranjang kosong.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Simpan pesanan ke tabel orders
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_date, status) VALUES (?, NOW(), 'belum selesai')");
            $stmt->execute([$customerName]);
            $orderId = $pdo->lastInsertId();

            // Simpan item pesanan ke tabel order_items
            $stmtItems = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
            foreach ($orderItems as $item) {
                $stmtItems->execute([$orderId, $item['id'], $item['quantity']]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "Pesanan atas nama $customerName telah dibuat.", 'order_id' => $orderId]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Gagal membuat pesanan: ' . $e->getMessage()]);
        }
    } elseif ($action === 'getOrders') {
        try {
            $stmt = $pdo->prepare("
                SELECT o.id, o.user_id, o.order_date, o.status, 
                       oi.menu_item_id, m.name AS menu_name, oi.quantity
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN menu_items m ON oi.menu_item_id = m.id
                ORDER BY o.order_date DESC
            ");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Gagal mengambil pesanan: ' . $e->getMessage()]);
        }
    } elseif ($action === 'completeOrder') {
        $orderId = $_POST['orderId'];

        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'selesai' WHERE id = ?");
            $stmt->execute([$orderId]);
            echo json_encode(['success' => true, 'message' => 'Pesanan selesai.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Gagal menyelesaikan pesanan: ' . $e->getMessage()]);
        }
    } elseif ($action === 'addMenu') {
        //  Fitur Tambah Menu 
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];

        try {
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, category, price) VALUES (?, ?, ?)");
            $stmt->execute([$name, $category, $price]);
            echo json_encode(['success' => true, 'message' => 'Menu berhasil ditambahkan.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Gagal menambahkan menu: ' . $e->getMessage()]);
        }
    } else {                            
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
}

if ($_POST['action'] == 'deleteMenu') {
    $id = $_POST['id'];

    // Query untuk menghapus menu dari database
    $query = "DELETE FROM menu WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menghapus menu']);
    }
}

?>
