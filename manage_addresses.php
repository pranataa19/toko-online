<?php
include 'components/connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['add_address'])) {
    $recipient_name = filter_var($_POST['recipient_name'], FILTER_SANITIZE_STRING);
    $recipient_phone = filter_var($_POST['recipient_phone'], FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

    $insert_address = $conn->prepare("INSERT INTO `addresses` (user_id, recipient_name, recipient_phone, email, address) VALUES (?, ?, ?, ?, ?)");
    $insert_address->execute([$user_id, $recipient_name, $recipient_phone, $email, $address]);
}

if (isset($_POST['set_primary'])) {
    $address_id = $_POST['address_id'];

    // Set all addresses to non-primary
    $update_all = $conn->prepare("UPDATE `addresses` SET is_primary = 0 WHERE user_id = ?");
    $update_all->execute([$user_id]);

    // Set selected address to primary
    $update_primary = $conn->prepare("UPDATE `addresses` SET is_primary = 1 WHERE id = ?");
    $update_primary->execute([$address_id]);
}

if (isset($_POST['delete_address'])) {
    $address_id = $_POST['address_id'];

    // Check if address to be deleted is primary
    $check_primary = $conn->prepare("SELECT is_primary FROM `addresses` WHERE id = ?");
    $check_primary->execute([$address_id]);
    $address_data = $check_primary->fetch(PDO::FETCH_ASSOC);

    // Delete address
    $delete_address = $conn->prepare("DELETE FROM `addresses` WHERE id = ?");
    $delete_address->execute([$address_id]);

    // If the deleted address was primary, set another address as primary if available
    if ($address_data['is_primary']) {
        $set_new_primary = $conn->prepare("SELECT id FROM `addresses` WHERE user_id = ? LIMIT 1");
        $set_new_primary->execute([$user_id]);
        $new_primary = $set_new_primary->fetch(PDO::FETCH_ASSOC);
        if ($new_primary) {
            $update_primary = $conn->prepare("UPDATE `addresses` SET is_primary = 1 WHERE id = ?");
            $update_primary->execute([$new_primary['id']]);
        }
    }
}

// Fetch user email
$user = $conn->prepare("SELECT email FROM `users` WHERE id = ?");
$user->execute([$user_id]);
$user_data = $user->fetch(PDO::FETCH_ASSOC);
$user_email = $user_data['email'];

// Fetch addresses
$addresses = $conn->prepare("SELECT * FROM `addresses` WHERE user_id = ?");
$addresses->execute([$user_id]);
$address_list = $addresses->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Alamat</title>
       
   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>
<h1 class="heading">Alamat</h1>
<section class="address-management">
   

    <form action="" method="POST">
        <h4>Tambah Alamat Baru</h4>
        <div class="inputBox" id="recipient_name_box">
            <span>Nama Penerima:</span>
            <input type="text" name="recipient_name" placeholder="Masukkan nama penerima" class="box" maxlength="100" required>
        </div>
        <div class="inputBox" id="recipient_phone_box">
            <span>Nomor Telepon:</span>
            <input type="text" name="recipient_phone" placeholder="Masukkan nomor telepon" class="box" maxlength="13" required>
        </div>
        <div class="inputBox" id="email_box">
            <span>Email:</span>
            <input type="email" name="email" value="<?= $user_email; ?>" class="box" readonly>
        </div>
        <div class="inputBox" id="address_box">
            <span>Alamat:</span>
            <input type="text" name="address" placeholder="Masukkan alamat" class="box" maxlength="500" required>
        </div>
        <input type="submit" name="add_address" class="btn" value="Tambah Alamat">
    </form>

    
    <table>
        <thead>
            <tr>
                <th>Nama Penerima</th>
                <th>Nomor Telepon</th>
                <th>Email</th>
                <th>Alamat</th>
                <th>Utama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($address_list as $address): ?>
                <tr>
                    <td><?= $address['recipient_name']; ?></td>
                    <td><?= $address['recipient_phone']; ?></td>
                    <td><?= $address['email']; ?></td>
                    <td><?= $address['address']; ?></td>
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="address_id" value="<?= $address['id']; ?>">
                            <input type="submit" name="set_primary" class="btn <?= $address['is_primary'] ? 'disabled' : ''; ?>" value="<?= $address['is_primary'] ? 'Alamat Utama' : 'Set Utama'; ?>">
                        </form>
                    </td>
                    <td>
                        <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                            <input type="hidden" name="address_id" value="<?= $address['id']; ?>">
                            <input type="submit" name="delete_address" class="btn delete-btn" value="Hapus">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include 'components/footer.php'; ?>
<script src="../js/admin_script.js"></script>
</body>

</html>
