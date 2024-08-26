<?php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header('location:user_login.php');
    exit();
}

// Fetch user details
$user_query = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('location:user_login.php');
    exit();
}

if (isset($_POST['order'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $total_products = $_POST['total_products'];
    $total_price = $_POST['total_price'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Email tidak valid!';
    } else {
        $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $check_cart->execute([$user_id]);

        if ($check_cart->rowCount() > 0) {
            $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, '']);

            $order_id = $conn->lastInsertId();

            if ($method == 'transfer bank' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == UPLOAD_ERR_OK) {
                $payment_proof = $_FILES['payment_proof']['name'];
                $payment_proof_tmp = $_FILES['payment_proof']['tmp_name'];
                $payment_proof_folder = 'uploaded_proofs/' . $payment_proof;

                if (move_uploaded_file($payment_proof_tmp, $payment_proof_folder)) {
                    $update_payment_proof = $conn->prepare("UPDATE `orders` SET payment_proof = ? WHERE id = ?");
                    $update_payment_proof->execute([$payment_proof, $order_id]);
                } else {
                    $message[] = 'Gagal mengunggah bukti transfer!';
                }
            }

            // Reduce stock for each product in the cart
            $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart_items->execute([$user_id]);

            while ($cart_item = $select_cart_items->fetch(PDO::FETCH_ASSOC)) {
                $product_id = $cart_item['pid'];
                $quantity = $cart_item['quantity'];

                // Get current stock
                $select_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
                $select_stock->execute([$product_id]);
                $product = $select_stock->fetch(PDO::FETCH_ASSOC);
                $current_stock = $product['stock'];

                // Update stock
                $new_stock = $current_stock - $quantity;
                $update_stock = $conn->prepare("UPDATE `products` SET stock = ? WHERE id = ?");
                $update_stock->execute([$new_stock, $product_id]);
            }

            // Clear cart
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);

            $message[] = $method == 'transfer bank' ? 'Pesanan berhasil dilakukan! Silakan Tunggu Konfirmasi Dari Admin' : 'Pesanan berhasil dilakukan!';
        } else {
            $message[] = 'Keranjang Anda kosong!';
        }
    }
}

// Fetch user addresses
$user_addresses = $conn->prepare("SELECT * FROM `addresses` WHERE user_id = ? ORDER BY is_primary DESC LIMIT 1");
$user_addresses->execute([$user_id]);
$primary_address = $user_addresses->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

    <form action="" method="POST" enctype="multipart/form-data">
        <h3>Pesanan Anda</h3>

        <div class="display-orders">
        <?php
            $grand_total = 0;
            $cart_items = [];
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
                while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                    $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
                    $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
        ?>
            <p> <?= htmlspecialchars($fetch_cart['name']); ?> <span>(<?= 'Rp' . htmlspecialchars($fetch_cart['price']) . '/- x ' . htmlspecialchars($fetch_cart['quantity']); ?>)</span> </p>
        <?php
                }
                $total_products = implode($cart_items);
            } else {
                echo '<p class="empty">Keranjang Anda kosong!</p>';
            }
        ?>
            <input type="hidden" name="total_products" value="<?= htmlspecialchars($total_products); ?>">
            <input type="hidden" name="total_price" value="<?= htmlspecialchars($grand_total); ?>">
            <div class="grand-total">Total Keseluruhan: <span>Rp.<?= htmlspecialchars($grand_total); ?>/-</span></div>
        </div>

        <h3>Tempatkan Pesanan Anda</h3>

        <?php if (!$primary_address): ?>
            <p class="empty">Alamat Anda kosong! <a href="manage_addresses.php" class="btn">Tambah Alamat</a></p>
        <?php else: ?>
            <div class="manage-address-btn">
                <a href="manage_addresses.php">
                    <i class="fas fa-edit"></i> <span>Edit Alamat</span>
                </a>
            </div>

            <div class="inputBox">
                <span>Nama Anda:</span>
                <input type="text" name="name" value="<?= htmlspecialchars($primary_address['recipient_name']); ?>" class="box" maxlength="100" required>
            </div>
            <div class="inputBox">
                <span>Nomor Telepon Anda:</span>
                <input type="text" name="number" value="<?= htmlspecialchars($primary_address['recipient_phone']); ?>" class="box" maxlength="100" required>
            </div>
            <div class="inputBox">
                <span>Email Anda:</span>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" class="box" maxlength="100" required>
            </div>
            <div class="inputBox">
                <span>Alamat Anda:</span>
                <select name="address" class="box" required>
                    <option value="<?= htmlspecialchars($primary_address['address']); ?>"><?= htmlspecialchars($primary_address['recipient_name']) . ' - ' . htmlspecialchars($primary_address['address']); ?></option>
                </select>
            </div>
            <div class="inputBox">
                <span>Metode Pembayaran:</span>
                <select name="method" class="box" id="payment-method" onchange="togglePaymentProof()" required>
                    <option value="cash on delivery">Cash On Delivery</option>
                    <option value="transfer bank">Transfer Bank</option>
                </select>
                <p id="bank-transfer-info" style="display:none;">Silakan transfer ke rekening berikut: <br>BCA 123456789 a.n Nama Anda. Jangan lupa unggah bukti transfer!</p>
            </div>
            <div class="inputBox" id="payment-proof-box" style="display:none;">
                <span>Unggah Bukti Transfer:</span>
                <input type="file" name="payment_proof" class="box">
            </div>

            <input type="submit" name="order" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" value="Tempatkan Pesanan">
        <?php endif; ?>
    </form>

</section>

<script>
function togglePaymentProof() {
    var method = document.getElementById('payment-method').value;
    if (method === 'transfer bank') {
        document.getElementById('payment-proof-box').style.display = 'block';
        document.getElementById('bank-transfer-info').style.display = 'block';
    } else {
        document.getElementById('payment-proof-box').style.display = 'none';
        document.getElementById('bank-transfer-info').style.display = 'none';
    }
}
</script>

</body>
</html>
