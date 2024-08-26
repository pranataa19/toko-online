<?php

include 'components/connect.php';

session_start();

// Memeriksa apakah pengguna sudah login
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pesanan Anda</title>
   
   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <!-- CSS khusus halaman ini -->
   <style>
      /* Gaya kustom tambahan di sini */
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="orders">

   <h1 class="heading">Pesanan Anda</h1>

   <div class="box-container">

   <?php
      // Pesan ketika pengguna belum login
      if ($user_id == '') {
         echo '<p class="empty">Silakan login untuk melihat pesanan Anda</p>';
      } else {
         // Mengambil pesanan untuk pengguna yang sudah login
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         
         if ($select_orders->rowCount() > 0) {
            // Menampilkan detail setiap pesanan
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <p>Produk: <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span></p>
      <p>Dipesan pada: <span><?= date('d F Y H:i', strtotime($fetch_orders['placed_on'])); ?></span></p>
      <p>Total Harga: <span>Rp.<?= number_format($fetch_orders['total_price'], 0, ',', '.'); ?>,-</span></p>
      <button onclick="toggleDetails(<?= $fetch_orders['id']; ?>)"><i class="fa fa-angle-down" aria-hidden="true"></i></button>
      <div class="order-details" id="details-<?= $fetch_orders['id']; ?>" style="display:none;">
         <p>Nama: <span><?= htmlspecialchars($fetch_orders['name']); ?></span></p>
         <p>Email: <span><?= htmlspecialchars($fetch_orders['email']); ?></span></p>
         <p>Nomor Telepon: <span><?= htmlspecialchars($fetch_orders['number']); ?></span></p>
         <p>Alamat: <span><?= htmlspecialchars($fetch_orders['address']); ?></span></p>
         <p>Metode Pembayaran: <span><?= htmlspecialchars($fetch_orders['method']); ?></span></p>
         <p>Status: <span style="color:<?php echo ($fetch_orders['payment_status'] == 'pending') ? 'red' : 'green'; ?>"><?= htmlspecialchars($fetch_orders['payment_status']); ?></span></p>
      </div>
   </div>
   <?php
            }
         } else {
            // Pesan jika tidak ada pesanan untuk pengguna ini
            echo '<p class="empty">Belum ada pesanan yang ditempatkan!</p>';
         }
      }
   ?>

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
      button.innerHTML = '<i class="fa fa-angle-down" aria-hidden="true"></i>'; // Ubah teks tombol
   }
}
</script>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
