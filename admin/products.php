<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);
   $stock = $_POST['stock'];
   $stock = filter_var($stock, FILTER_SANITIZE_NUMBER_INT);

   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/'.$image_02;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'Nama produk sudah ada!';
   } else {
      $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, category, stock, image_01, image_02) VALUES(?,?,?,?,?,?,?)");
      $insert_products->execute([$name, $details, $price, $category, $stock, $image_01, $image_02]);

      if($insert_products){
         if($image_size_01 > 2000000 || $image_size_02 > 2000000){
            $message[] = 'Ukuran gambar terlalu besar!';
         } else {
            if($image_size_01 > 0) move_uploaded_file($image_tmp_name_01, $image_folder_01);
            if($image_size_02 > 0) move_uploaded_file($image_tmp_name_02, $image_folder_02);
            $message[] = 'Produk baru ditambahkan!';
         }
      }
   }  
}



if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   if($fetch_delete_image['image_01']) unlink('../uploaded_img/'.$fetch_delete_image['image_01']);
   if($fetch_delete_image['image_02']) unlink('../uploaded_img/'.$fetch_delete_image['image_02']);
   if($fetch_delete_image['image_03']) unlink('../uploaded_img/'.$fetch_delete_image['image_03']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:products.php');
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Produk</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">TAMBAH PRODUK</h1>

   <form action="" method="post" enctype="multipart/form-data">
   <div class="flex">
      <div class="inputBox">
         <span>Nama Produk (wajib)</span>
         <input type="text" class="box" required maxlength="100" placeholder="Masukkan nama produk" name="name">
      </div>
      <div class="inputBox">
         <span>Harga Produk (wajib)</span>
         <input type="number" min="0" class="box" required max="9999999999" placeholder="Masukkan harga produk" onkeypress="if(this.value.length == 10) return false;" name="price">
      </div>
      <div class="inputBox">
         <span>Stok Produk (wajib)</span>
         <input type="number" min="0" class="box" required placeholder="Masukkan jumlah stok produk" name="stock">
      </div>
      <div class="inputBox">
         <span>Kategori Produk (wajib)</span>
         <select name="category" class="box" required>
            <option value="">Pilih Kategori</option>
            <option value="makanan">Makanan</option>
            <option value="minuman">Minuman</option>
            <option value="perawatan">Perawatan</option>
            <option value="kesehatan">Kesehatan</option>
            <option value="freshfood">Fresh Food</option>
            <option value="bayi">Bayi</option>
         </select>
      </div>
      <div class="inputBox">
         <span>Gambar 01 (wajib)</span>
         <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
      </div>
      <div class="inputBox">
         <span>Gambar 02</span>
         <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
      </div>

      <div class="inputBox">
         <span>Deskripsi Produk (wajib)</span>
         <textarea name="details" placeholder="Masukkan deskripsi produk" class="box" required maxlength="500" cols="30" rows="10"></textarea>
      </div>
   </div>
   
   <input type="submit" value="Tambah Produk" class="btn" name="add_product">
</form>


</section>

<section class="show-products">

   <h1 class="heading">PRODUK</h1>

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      
      <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="price">Rp.<span><?= $fetch_products['price']; ?></span>,-</div><div class="stock">Stok: <?= $fetch_products['stock']; ?></div>
      
      <div class="details"><span><?= $fetch_products['details']; ?></span></div>
      <div class="flex-btn">
         <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">Update</a>
         <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Hapus produk ini?');">Hapus</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">Belum ada produk yang ditambahkan!</p>';
      }
   ?>
   
   </div>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>
