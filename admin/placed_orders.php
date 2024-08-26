<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_POST['update_payment'])) {
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'Payment status updated!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

// Function to calculate total sales
function calculateTotalSales($conn) {
   $total_sales = 0;
   $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'completed'");
   $select_orders->execute();
   while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
      $total_sales += $fetch_orders['total_price'];
   }
   return $total_sales;
}

// Function to calculate monthly sales
function calculateMonthlySales($conn, $month, $year) {
   $total_sales = 0;
   $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'completed' AND MONTH(placed_on) = ? AND YEAR(placed_on) = ?");
   $select_orders->execute([$month, $year]);
   while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
      $total_sales += $fetch_orders['total_price'];
   }
   return $total_sales;
}

// Function to generate sales report
function generateSalesReport($conn, $from_date, $to_date) {
   $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE placed_on BETWEEN ? AND ?");
   $select_orders->execute([$from_date, $to_date]);
   $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);
   return $orders;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      .order-details {
         display: none;
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="orders">
   <h1 class="heading">PENJUALAN</h1>

   <div class="box-container">

   <?php
   $select_orders = $conn->prepare("SELECT * FROM `orders`");
   $select_orders->execute();
   if ($select_orders->rowCount() > 0) {
      while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <p> Nama : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> Total Harga : <span>Rp.<?= number_format($fetch_orders['total_price'], 0, ',', '.'); ?>,-</span> </p>
      <p><button onclick="toggleDetails(<?= $fetch_orders['id']; ?>)"><i class="fa fa-angle-down" aria-hidden="true"></i></button></p>
      <div class="order-details" id="details-<?= $fetch_orders['id']; ?>">
         <p> Tanggal Pemesanan: <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> Nomor Telepon : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> Alamat : <span><?= $fetch_orders['address']; ?></span> </p>
         <p> Total Produk : <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> Metode Pembayaran : <span><?= $fetch_orders['method']; ?></span> </p>
         <p><a href="view_payment_proof.php?order_id=<?= $fetch_orders['id']; ?>" target="_blank">Lihat Bukti Pembayaran</a></p>
         <form action="" method="post">
            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
            <select name="payment_status" class="select">
               <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
               <option value="pending">Pending</option>
               <option value="in_progress">Dalam Proses</option>
               <option value="completed">Selesai</option>
               <option value="cancelled">Dibatalkan</option>
            </select>
            <div class="flex-btn">
               <input type="submit" value="Update" class="option-btn" name="update_payment">
               <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Hapus pesanan ini?');">Hapus</a>
            </div>
         </form>
      </div>
   </div>
   <?php
      }
   } else {
      echo '<p class="empty">Belum ada pesanan yang ditempatkan!</p>';
   }
   ?>

   </div>
</section>

<section class="sales-report">
   <h1 class="heading">LAPORAN</h1>

      <div class="box">
         <h3>Total Penjualan</h3>
         <p>Rp.<?= number_format(calculateTotalSales($conn), 0, ',', '.'); ?>,-</p>
      
         <h3>Penjualan Bulan Ini</h3>
         <?php
            $current_month = date('m');
            $current_year = date('Y');
            $monthly_sales = calculateMonthlySales($conn, $current_month, $current_year);
         ?>
         <p>Rp.<?= number_format($monthly_sales, 0, ',', '.'); ?>,-</p>
  
      <form action="generate_report.php" method="post">
         <label for="from_date">Dari Tanggal:</label>
         <input type="date" id="from_date" name="from_date" required>
         <label for="to_date">Hingga Tanggal:</label>
         <input type="date" id="to_date" name="to_date" required>
         <button type="submit" class="btn">Buat Laporan</button>
      </form>
   </div>

</section>

<script>
function toggleDetails(orderId) {
   var details = document.getElementById('details-' + orderId);
   var button = document.querySelector('button[onclick="toggleDetails(' + orderId + ')"]');

   if (details.style.display === "none" || details.style.display === "") {
      details.style.display = "block";
      button.innerHTML = '<i class="fa fa-angle-up" aria-hidden="true"></i>'; // Ubah teks tombol
   } else {
      details.style.display = "none";
      button.innerHTML= '<i class="fa fa-angle-down" aria-hidden="true"></i>'; // Ubah teks tombol
   }
}
</script>
</body>
</html>
