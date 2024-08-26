<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_GET['order_id'])) {
   $order_id = $_GET['order_id'];
   $select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
   $select_order->execute([$order_id]);
   $order = $select_order->fetch(PDO::FETCH_ASSOC);

   if ($order) {
      $payment_proof = $order['payment_proof']; // Assuming 'payment_proof' column stores the filename of the proof
      $proof_path = "../uploaded_proofs/" . $payment_proof;
   } else {
      $message[] = "Order not found!";
   }
} else {
   header('location:placed_orders.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Lihat Bukti Pembayaran</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="payment-proof">
   <h1 class="heading">Bukti Pembayaran</h1>

   <div class="box-container">
      <?php if (isset($proof_path) && file_exists($proof_path)): ?>
         <div class="box">
            <img src="<?= $proof_path; ?>" alt="Bukti Pembayaran" class="proof-image">
         </div>
      <?php else: ?>
         <p class="empty">Bukti pembayaran tidak ditemukan atau belum diunggah!</p>
      <?php endif; ?>
   </div>
</section>

</body>
</html>
