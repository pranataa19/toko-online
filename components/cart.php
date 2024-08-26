<?php

if (isset($_POST['add_to_cart'])) {

    if ($user_id == '') {
        header('location:user_login.php');
    } else {
        $pid = $_POST['pid'];
        $pid = filter_var($pid, FILTER_SANITIZE_STRING);
        $name = $_POST['name'];
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $price = $_POST['price'];
        $price = filter_var($price, FILTER_SANITIZE_STRING);
        $image = $_POST['image'];
        $image = filter_var($image, FILTER_SANITIZE_STRING);
        $qty = $_POST['qty'];
        $qty = filter_var($qty, FILTER_SANITIZE_NUMBER_INT);

        // Check if the product is already in the cart
        $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE pid = ? AND user_id = ?");
        $check_cart_numbers->execute([$pid, $user_id]);

        if ($check_cart_numbers->rowCount() > 0) {
            // Product is already in the cart, update the quantity
            $cart_item = $check_cart_numbers->fetch(PDO::FETCH_ASSOC);
            $new_qty = $cart_item['quantity'] + $qty;

            // Fetch the product's stock
            $select_product = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
            $select_product->execute([$pid]);
            $product = $select_product->fetch(PDO::FETCH_ASSOC);
            $stock = $product['stock'];

            if ($new_qty > $stock) {
                $message[] = 'Jumlah yang diminta melebihi stok yang tersedia';
            } else {
                $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE pid = ? AND user_id = ?");
                $update_qty->execute([$new_qty, $pid, $user_id]);
                $message[] = 'Jumlah item dalam keranjang berhasil diperbarui';
            }
        } else {
            // Product is not in the cart, insert new item
            // Fetch the product's stock
            $select_product = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
            $select_product->execute([$pid]);
            $product = $select_product->fetch(PDO::FETCH_ASSOC);
            $stock = $product['stock'];

            if ($qty > $stock) {
                $message[] = 'Jumlah yang diminta melebihi stok yang tersedia';
            } else {
                $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
                $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
                $message[] = 'Item berhasil ditambahkan ke keranjang';
            }
        }
    }
}

?>
