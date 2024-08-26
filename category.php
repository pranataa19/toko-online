<?php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}
include 'components/cart.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori</title>
   
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

    <h1 class="heading">Kategori: <?= htmlspecialchars($_GET['category']) ?></h1>

    <div class="box-container">

    <?php
    if (isset($_GET['category'])) {
        $category = filter_var($_GET['category'], FILTER_SANITIZE_STRING);
        $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
        $select_products->execute([$category]);
        if ($select_products->rowCount() > 0) {
            while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <form action="" method="post" class="box">
        <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
        <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
        <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
        <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
        <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
        <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
        <div class="name"><?= $fetch_product['name']; ?></div>
        <div class="flex">
            <div class="price"><span>Rp.</span><?= $fetch_product['price']; ?><span>/-</span></div>
            <div class="stock">Stok: <?= $fetch_product['stock']; ?></div>

            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
        </div>
        <input type="submit" value="Tambah ke Keranjang" class="btn" name="add_to_cart">
    </form>
    <?php
            }
        } else {
            echo '<p class="empty">Tidak ada produk ditemukan!</p>';
        }
    } else {
        echo '<p class="empty">Kategori tidak ditentukan!</p>';
    }
    ?>

    </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
