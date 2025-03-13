$(document).ready(function () {
  loadMenu();
  let cart = [];

  // Fungsi untuk memuat menu dari server
  function loadMenu() {
    $.ajax({
      url: "index.php",
      method: "POST",
      data: { action: "getMenu" },
      success: function (response) {
        try {
          let items = JSON.parse(response);
          let tableContent = "";

          items.forEach(function (item) {
            tableContent += `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.category}</td>
                    <td>Rp ${item.price.toLocaleString()}</td>
                    <td>
                        <button class="btn btn-primary add-to-cart" data-id="${
                          item.id
                        }" data-name="${item.name}" data-price="${item.price}">
                            Tambah
                        </button>
                        <button class="btn btn-warning edit-item" data-id="${
                          item.id
                        }" data-name="${item.name}" data-category="${
              item.category
            }" data-price="${item.price}">
                            Edit
                        </button>
                    </td>
                </tr>
            `;
          });

          $("#menuTable tbody").html(tableContent);
          $("#menuTable").DataTable();
        } catch (error) {
          Swal.fire(
            "Error!",
            "Gagal memuat menu. Cek format respons dari server.",
            "error"
          );
        }
      },
      error: function () {
        Swal.fire("Error!", "Tidak dapat terhubung ke server.", "error");
      },
    });
  }

  // Fungsi untuk menampilkan keranjang belanja
  function renderCart() {
    let cartContent = "";
    let totalAmount = 0;

    cart.forEach(function (item, index) {
      let itemTotal = item.price * item.quantity;
      totalAmount += itemTotal;

      cartContent += `
                <tr>
                    <td>${item.name}</td>
                    <td>Rp ${item.price.toLocaleString()}</td>
                    <td>${item.quantity}</td>
                    <td>Rp ${itemTotal.toLocaleString()}</td>
                    <td>
                        <button class="btn btn-success increase-quantity" data-index="${index}">+</button>
                        <button class="btn btn-warning decrease-quantity" data-index="${index}">-</button>
                        <button class="btn btn-danger remove-from-cart" data-index="${index}">Hapus</button>
                    </td>
                </tr>
            `;
    });

    cartContent += `
            <tr>
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td>Rp ${totalAmount.toLocaleString()}</td>
                <td></td>
            </tr>
        `;

    $("#cartTable tbody").html(cartContent);
  }

  // Event klik untuk menambahkan item ke keranjang
  $(document).on("click", ".add-to-cart", function () {
    const id = $(this).data("id");
    const name = $(this).data("name");
    const price = parseInt($(this).data("price"));

    const existingItem = cart.find((item) => item.id === id);
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      cart.push({ id, name, price, quantity: 1 });
    }

    renderCart();
    Swal.fire(
      "Ditambahkan!",
      "Item telah ditambahkan ke keranjang.",
      "success"
    );
  });

  // Event klik untuk menghapus item dari keranjang
  $(document).on("click", ".remove-from-cart", function () {
    const index = $(this).data("index");
    cart.splice(index, 1);
    renderCart();
    Swal.fire("Dihapus!", "Item telah dihapus dari keranjang.", "info");
  });

  // Event klik untuk menambah kuantitas item
  $(document).on("click", ".increase-quantity", function () {
    const index = $(this).data("index");
    cart[index].quantity += 1;
    renderCart();
  });

  // Event klik untuk mengurangi kuantitas item
  $(document).on("click", ".decrease-quantity", function () {
    const index = $(this).data("index");
    if (cart[index].quantity > 1) {
      cart[index].quantity -= 1;
    } else {
      Swal.fire(
        "Perhatian!",
        "Kuantitas tidak boleh kurang dari 1.",
        "warning"
      );
    }
    renderCart();
  });

  // Event klik untuk memproses pemesanan
  $("#placeOrder").on("click", function () {
    const customerName = $("#atasNama").val().trim();

    if (!customerName) {
      Swal.fire(
        "Nama Kosong",
        "Silakan masukkan nama terlebih dahulu.",
        "warning"
      );
      return;
    }

    if (cart.length === 0) {
      Swal.fire(
        "Keranjang Kosong",
        "Silakan tambahkan item ke keranjang terlebih dahulu.",
        "warning"
      );
      return;
    }

    $.ajax({
      url: "ajax.php", // Sebelumnya salah, harus ke ajax.php bukan index.php
      method: "POST",
      data: {
        action: "placeOrder",
        orderItems: JSON.stringify(cart),
        customerName: customerName,
      },
      success: function (response) {
        try {
          const result = JSON.parse(response);
          if (result.success) {
            Swal.fire(
              "Berhasil!",
              `Pesanan atas nama ${customerName} telah dibuat.`,
              "success"
            );
            cart = [];
            renderCart();
            $("#atasNama").val("");
          } else {
            Swal.fire(
              "Gagal!",
              result.error || "Terjadi kesalahan saat memproses pesanan.",
              "error"
            );
          }
        } catch (error) {
          Swal.fire("Error!", "Respons server tidak valid.", "error");
        }
      },
      error: function () {
        Swal.fire("Error!", "Tidak dapat terhubung ke server.", "error");
      },
    });
  });
});

$(document).on("click", ".edit-item", function () {
  let menuId = $(this).data("id");
  let menuName = $(this).data("name");
  let menuCategory = $(this).data("category");
  let menuPrice = $(this).data("price");

  $("#addMenuLabel").text("Edit Menu");
  $("#menuName").val(menuName);
  $("#menuCategory").val(menuCategory);
  $("#menuPrice").val(menuPrice);
  $("#addMenuForm").attr("data-id", menuId);

  // Menampilkan modal secara eksplisit
  $("#addMenuModal").modal("show");
});

$("#addMenuForm").submit(function (e) {
  e.preventDefault();

  let menuId = $(this).attr("data-id");
  let menuName = $("#menuName").val();
  let menuCategory = $("#menuCategory").val();
  let menuPrice = $("#menuPrice").val();
  let actionType = menuId ? "editMenu" : "addMenu";

  $.ajax({
    url: "ajax.php",
    type: "POST",
    data: {
      action: actionType,
      id: menuId,
      name: menuName,
      category: menuCategory,
      price: menuPrice,
    },
    success: function (response) {
      let data = JSON.parse(response);
      if (data.success) {
        Swal.fire(
          "Berhasil!",
          menuId ? "Menu berhasil diperbarui!" : "Menu berhasil ditambahkan!",
          "success"
        ).then(() => {
          $("#addMenuModal").modal("hide");
          location.reload();
        });
      } else {
        Swal.fire("Gagal!", "Terjadi kesalahan.", "error");
      }
    },
  });
});
