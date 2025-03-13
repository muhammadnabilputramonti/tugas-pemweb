<?php
require_once 'config.php';  
require_once 'classes/menu.php';
require_once 'classes/OrderHistory.php';

$orderHistory = new OrderHistory();
$orders = $orderHistory->getAllOrders();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'getMenu') {
        $menu = new Menu();
        $data = $menu->getAllItems();
        echo json_encode($data);
    } elseif ($action == 'placeOrder') {
        $orderItems = json_decode($_POST['orderItems'], true);
        // Lakukan pemrosesan pesanan (misalnya, simpan ke database)
        echo json_encode(['success' => true]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Ordering App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Aplikasi Pemesanan Makanan</h2>

        <!-- Tabel Menu -->
        <div class="table-container">
            <h4>Daftar Menu</h4>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMenuModal">Tambah Menu</button>
            <table id="menuTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Keranjang -->
        <div class="cart-container mt-5">
            <h4>Keranjang Belanja</h4>
            <input type="text" id="atasNama" class="form-control" placeholder="Atas Nama" aria-label="Username" aria-describedby="basic-addon1" style="margin-bottom: 10px; width: 20%; min-width: 200px;" required>
            <table class="table table-bordered" id="cartTable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <div class="text-end">
                <button id="placeOrder" class="btn btn-success">Pesan Sekarang</button>
            </div>
        </div><br><br><br><br><br>

        <!-- Riwayat Pesanan -->
        <h2 class="text-center">Riwayat Pesanan</h2><br>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>ID Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Tanggal Pesanan</th>
                    <th>Status</th>
                    <th>Menu</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['user_id']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td>
                                <?php if ($order['status'] == 'Pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($order['menu_name']) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada riwayat pesanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Modal Tambah Menu -->
        <div class="modal fade" id="addMenuModal" tabindex="-1" aria-labelledby="addMenuLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMenuLabel">Tambah Menu Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addMenuForm">
                            <div class="mb-3">
                                <label for="menuName" class="form-label">Nama Menu</label>
                                <input type="text" class="form-control" id="menuName" required>
                            </div>
                            <div class="mb-3">
                                <label for="menuCategory" class="form-label">Kategori</label>
                                <input type="text" class="form-control" id="menuCategory" required>
                            </div>
                            <div class="mb-3">
                                <label for="menuPrice" class="form-label">Harga</label>
                                <input type="number" class="form-control" id="menuPrice" required>
                            </div>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
