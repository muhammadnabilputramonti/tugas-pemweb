<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Daftar Pesanan</h2>
        <table class="table table-bordered" id="orderTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Atas Nama</th>
                    <th>Tanggal Pesan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <script>
    $(document).ready(function () {
        loadOrders();

        function loadOrders() {
            $.ajax({
                url: 'ajax.php',
                method: 'POST',
                data: { action: 'getOrders' },
                success: function (response) {
                    let orders = JSON.parse(response);
                    let orderContent = '';

                    orders.forEach(function (order) {
                        orderContent += `
                            <tr>
                                <td>${order.id}</td>
                                <td>${order.customer_name}</td>
                                <td>${order.order_date}</td>
                                <td>${order.status}</td>
                                <td>
                                    ${order.status === 'pending' ? `
                                    <button class="btn btn-success complete-order" data-id="${order.id}">
                                        Selesaikan
                                    </button>` : 'Selesai'}
                                </td>
                            </tr>
                        `;
                    });

                    $('#orderTable tbody').html(orderContent);
                }
            });
        }

        $(document).on('click', '.complete-order', function () {
            let orderId = $(this).data('id');

            $.ajax({
                url: 'ajax.php',
                method: 'POST',
                data: {
                    action: 'completeOrder',
                    orderId: orderId
                },
                success: function (response) {
                    let result = JSON.parse(response);
                    if (result.success) {
                        Swal.fire('Berhasil!', 'Pesanan telah diselesaikan.', 'success');
                        loadOrders();
                    } else {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menyelesaikan pesanan.', 'error');
                    }
                }
            });
        });
    });
</script>

</body>
</html>
