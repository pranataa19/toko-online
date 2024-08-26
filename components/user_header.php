<?php
   if (isset($message)) {
      foreach ($message as $msg) {
         echo '
         <div class="message">
            <span>'.$msg.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<header class="header">

   <section class="flex">

      <strong><a href="index.php" class="logo">Toko<span>Maya</span></a></strong>

      <nav class="navbar">
         <a href="index.php">Beranda</a>
         <a href="orders.php">Pesanan</a>
         <a href="shop.php">Belanja Sekarang</a>
         <a href="contact.php">Hubungi Kami</a>
      </nav>

      <div class="icons">
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_counts = $count_cart_items->rowCount();
         ?>
         <div id="menu-btn" class="fas fa-bars"></div>
         <a href="search_page.php"><i class="fas fa-search"></i>Cari</a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_counts; ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php          
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            if ($select_profile->rowCount() > 0) {
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile["name"]; ?></p>
         <a href="update_user.php" class="btn">Perbarui Profil.</a>
         <a href="manage_addresses.php" class="btn">Alamat</a> <!-- Updated link to Addresses -->
         <a href="components/user_logout.php" class="delete-btn" onclick="return confirm('Logout dari website?');">Logout</a> 
         <?php
            } else {
         ?>
         <p>Silakan Masuk atau Daftar terlebih dahulu untuk melanjutkan!</p>
         <div class="flex-btn">
            <a href="user_register.php" class="option-btn">Daftar</a>
            <a href="user_login.php" class="option-btn">Masuk</a>
         </div>
         <?php
            }
         ?>      
         
      </div>

   </section>

</header>
