<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_POST['update'])){

   $pid = $_POST['pid'];
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $stock = $_POST['stock'];
   $stock = filter_var($stock, FILTER_SANITIZE_NUMBER_INT);

   $update_product = $conn->prepare("UPDATE `products` SET name = ?, price = ?, details = ?, category = ?, stock = ? WHERE id = ?");
   $update_product->execute([$name, $price, $details, $category, $stock, $pid]);

   $message[] = 'Produk berhasil diperbarui!';

   $old_image_01 = $_POST['old_image_01'];
   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   if(!empty($image_01)){
      if($image_size_01 > 2000000){
         $message[] = 'Ukuran gambar terlalu besar!';
      }else{
         $update_image_01 = $conn->prepare("UPDATE `products` SET image_01 = ? WHERE id = ?");
         $update_image_01->execute([$image_01, $pid]);
         move_uploaded_file($image_tmp_name_01, $image_folder_01);
         unlink('../uploaded_img/'.$old_image_01);
         $message[] = 'Gambar 01 berhasil diperbarui!';
      }
   }

   $old_image_02 = $_POST['old_image_02'];
   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/'.$image_02;

   if(!empty($image_02)){
      if($image_size_02 > 2000000){
         $message[] = 'Ukuran gambar terlalu besar!';
      }else{
         $update_image_02 = $conn->prepare("UPDATE `products` SET image_02 = ? WHERE id = ?");
         $update_image_02->execute([$image_02, $pid]);
         move_uploaded_file($image_tmp_name_02, $image_folder_02);
         unlink('../uploaded_img/'.$old_image_02);
         $message[] = 'Gambar 02 berhasil diperbarui!';
      }
   }
   
    // Redirect after update
    header('location:products.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Product</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="update-product">

   <h1 class="heading">Update Produk</h1>

   <?php
      $update_id = $_GET['update'];
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
      $select_products->execute([$update_id]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="old_image_01" value="<?= $fetch_products['image_01']; ?>">
      <input type="hidden" name="old_image_02" value="<?= $fetch_products['image_02']; ?>">

      <div class="image-container">
         <div class="main-image">
            <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
         </div>
   
      </div>
      <span>Update Nama</span>
      <input type="text" name="name" required class="box" maxlength="100" placeholder="Masukkan nama produk" value="<?= $fetch_products['name']; ?>">
      <span>Update Harga</span>
      <input type="number" name="price" required class="box" min="0" max="9999999999" placeholder="Masukkan harga produk" onkeypress="if(this.value.length == 10) return false;" value="<?= $fetch_products['price']; ?>">
      <span>Update Stok</span>
      <input type="number" name="stock" required class="box" min="0" placeholder="Masukkan jumlah stok produk" value="<?= $fetch_products['stock']; ?>">

      <span>Update Detail</span>
      <textarea name="details" class="box" required cols="30" rows="10"><?= $fetch_products['details']; ?></textarea>
      <span>Update Kategori</span>
      <select name="category" class="box" required>
         <option value="">Pilih Kategori</option>
         <option value="dapur" <?= $fetch_products['category'] == 'Dapur' ? 'selected' : ''; ?>>Dapur</option>
         <option value="makanan" <?= $fetch_products['category'] == 'makanan' ? 'selected' : ''; ?>>Makanan</option>
         <option value="minuman" <?= $fetch_products['category'] == 'minuman' ? 'selected' : ''; ?>>Minuman</option>
         <option value="perawatan" <?= $fetch_products['category'] == 'perawatan' ? 'selected' : ''; ?>>Perawatan</option>
         <option value="kesehatan" <?= $fetch_products['category'] == 'kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
         <option value="freshfood" <?= $fetch_products['category'] == 'freshfood' ? 'selected' : ''; ?>>Fresh Food</option>
         <option value="bayi" <?= $fetch_products['category'] == 'bayi' ? 'selected' : ''; ?>>Bayi</option>
      </select>
      <span>Update Gambar 01</span>
      <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
      <span>Update Gambar 02</span>
      <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">

      <div class="flex-btn">
         <input type="submit" name="update" class="btn" value="Update">
         <a href="products.php" class="option-btn">Kembali</a>
      </div>
   </form>
   
   <?php
         }
      }else{
         echo '<p class="empty">Produk tidak ditemukan!</p>';
      }
   ?>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>
