<?php

include '../components/connect.php';

$orders = [];
$from_date = '';
$to_date = '';
$completed_total = 0;
$pending_total = 0;
$canceled_total = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $from_date = $_POST['from_date'] ?? '';
   $to_date = $_POST['to_date'] ?? '';

   // Validasi tanggal
   if ($from_date && $to_date) {
       $from_date = htmlspecialchars(strip_tags($from_date));
       $to_date = htmlspecialchars(strip_tags($to_date));

       // Pastikan format tanggal benar
       if (DateTime::createFromFormat('Y-m-d', $from_date) && DateTime::createFromFormat('Y-m-d', $to_date)) {
           // Query untuk mengambil semua pesanan dalam rentang tanggal
           $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE placed_on BETWEEN ? AND ?");
           $select_orders->execute([$from_date, $to_date]);
           $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

           // Hitung total pendapatan berdasarkan status
           $completed_total = $conn->prepare("SELECT SUM(total_price) AS total FROM `orders` WHERE placed_on BETWEEN ? AND ? AND payment_status = 'completed'");
           $completed_total->execute([$from_date, $to_date]);
           $completed_total = $completed_total->fetchColumn();

           $pending_total = $conn->prepare("SELECT SUM(total_price) AS total FROM `orders` WHERE placed_on BETWEEN ? AND ? AND payment_status = 'pending'");
           $pending_total->execute([$from_date, $to_date]);
           $pending_total = $pending_total->fetchColumn();

           $canceled_total = $conn->prepare("SELECT SUM(total_price) AS total FROM `orders` WHERE placed_on BETWEEN ? AND ? AND payment_status = 'canceled'");
           $canceled_total->execute([$from_date, $to_date]);
           $canceled_total = $canceled_total->fetchColumn();
       }
   }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Laporan Penjualan</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="sales-report-p">
   <h1 class="heading">Laporan Penjualan</h1>

   <div class="box-container">
      <div class="report-info">
         <?php if ($from_date && $to_date): ?>
            <p><strong>Dari Tanggal:</strong> <?= date('d M Y', strtotime($from_date)); ?></p>
            <p><strong>Hingga Tanggal:</strong> <?= date('d M Y', strtotime($to_date)); ?></p>
         <?php endif; ?>
      </div>

      <div class="box">
         <h3>Detail Laporan</h3>
         <?php if (!empty($orders)): ?>
            <table>
               <thead>
                  <tr>
                     <th>Tanggal Pemesanan</th>
                     <th>Nama</th>
                     <th>Alamat</th>
                     <th>Total Harga</th>
                     <th>Metode Pembayaran</th>
                     <th>Status Pembayaran</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($orders as $order): ?>
                     <tr>
                        <td><?= htmlspecialchars($order['placed_on']); ?></td>
                        <td><?= htmlspecialchars($order['name']); ?></td>
                        <td><?= htmlspecialchars($order['address']); ?></td>
                        <td>Rp.<?= number_format($order['total_price'], 0, ',', '.'); ?>,-</td>
                        <td><?= htmlspecialchars($order['method']); ?></td>
                        <td><?= htmlspecialchars($order['payment_status']); ?></td>
                     </tr>
                  <?php endforeach; ?>
               </tbody>
            </table>
         <?php else: ?>
            <p>Tidak ada data untuk periode yang dipilih.</p>
         <?php endif; ?>

         <div class="totals">
            <h3>Total Pendapatan</h3>
            <p><strong>Completed:</strong> Rp.<?= number_format($completed_total, 0, ',', '.'); ?>,-</p>
            <p><strong>Pending:</strong> Rp.<?= number_format($pending_total, 0, ',', '.'); ?>,-</p>
            <p><strong>Canceled:</strong> Rp.<?= number_format($canceled_total, 0, ',', '.'); ?>,-</p>
         </div>
      </div>
   </div>
   <button onclick="window.print();" class="btn print-btn">Cetak Laporan</button>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
