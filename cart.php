<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:user_login.php');
    exit();
}

if (isset($_POST['delete'])) {
    $cart_id = $_POST['cart_id'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$cart_id]);
    $message[] = 'Item berhasil dihapus dari keranjang';
}

if (isset($_GET['delete_all'])) {
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
    $message[] = 'Semua item berhasil dihapus dari keranjang';
    header('location:cart.php');
    exit();
}


if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_NUMBER_INT); // Use FILTER_SANITIZE_NUMBER_INT for quantities

    // Fetch the product's stock
    $select_product = $conn->prepare("SELECT stock FROM `products` WHERE id = (SELECT pid FROM `cart` WHERE id = ?)");
    $select_product->execute([$cart_id]);
    $product = $select_product->fetch(PDO::FETCH_ASSOC);
    $stock = $product['stock'];

    if ($qty > $stock) {
        $message[] = 'Jumlah yang diminta melebihi stok yang tersedia';
    } else {
        $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
        $update_qty->execute([$qty, $cart_id]);
        $message[] = 'Jumlah item dalam keranjang berhasil diperbarui';
    }
}

if (isset($_POST['proceed_to_checkout'])) {
    // Check stock availability
    $select_cart = $conn->prepare("SELECT cart.*, products.stock FROM `cart` JOIN `products` ON cart.pid = products.id WHERE cart.user_id = ?");
    $select_cart->execute([$user_id]);
    $stock_valid = true;

    while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
        if ($fetch_cart['quantity'] > $fetch_cart['stock']) {
            $stock_valid = false;
            break;
        }
    }

    if ($stock_valid) {
        header('location:checkout.php');
        exit();
    } else {
        $message[] = 'Stok salah satu item tidak mencukupi. Silakan periksa kembali keranjang belanja Anda.';
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
   
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products shopping-cart">

    <h3 class="heading">Keranjang Belanja</h3>

    <div class="box-container">
        <?php
        $grand_total = 0;
        $select_cart = $conn->prepare("SELECT cart.*, products.stock FROM `cart` JOIN `products` ON cart.pid = products.id WHERE cart.user_id = ?");
        $select_cart->execute([$user_id]);
        if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                $stock = $fetch_cart['stock'];
                $stock_warning = ($fetch_cart['quantity'] > $stock) ? 'style="color: red;"' : '';
        ?>
        <form action="" method="post" class="box">
            <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
            <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
            <div class="name"><?= $fetch_cart['name']; ?></div>
            <div class="flex">
                <div class="price">Rp.<?= $fetch_cart['price']; ?>/-</div>
                <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="<?= $fetch_cart['quantity']; ?>" <?= $stock_warning; ?>>
                <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </div>
            
            <div class="stock">Stok: <?= $fetch_cart['stock']; ?></div>

            <div class="sub-total"> Sub Total : <span>Rp.<?= $sub_total; ?>/-</span> </div>
            <input type="submit" value="Hapus Item" onclick="return confirm('Hapus item ini dari keranjang?');" class="delete-btn" name="delete">
        </form>
        <?php
                $grand_total += $sub_total;
            }
        } else {
            echo '<p class="empty">Keranjang belanja anda kosong</p>';
        }
        ?>
    </div>

    <div class="cart-total">
        <p>Total Keseluruhan : <span>Rp.<?= $grand_total; ?>/-</span></p>
        <a href="shop.php" class="option-btn">Lanjutkan Belanja</a>
        <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('Hapus semua item dari keranjang?');">Hapus Semua Item</a>
        <form action="" method="post">
            <button type="submit" name="proceed_to_checkout" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Lanjutkan ke Pembayaran</button>
        </form>
    </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
