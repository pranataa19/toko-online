<?php

include 'components/connect.php'; // Sertakan file koneksi database

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
 } else {
    $user_id = '';
 };

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $current_time = date("Y-m-d H:i:s");

    // Periksa token dan masa berlakunya
    $check_token = $conn->prepare("SELECT * FROM `users` WHERE reset_token = ? AND token_expiry > ?");
    $check_token->execute([$token, $current_time]);
    $user = $check_token->fetch(PDO::FETCH_ASSOC);

    if ($check_token->rowCount() > 0) {
        if (isset($_POST['reset_submit'])) {
            $new_password = sha1($_POST['new_password']);
            $confirm_password = sha1($_POST['confirm_password']);

            if ($new_password === $confirm_password) {
                // Update password dan reset token
                $update_password = $conn->prepare("UPDATE `users` SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
                $update_password->execute([$new_password, $token]);

                $message[] = 'Password berhasil direset. Anda dapat login dengan password baru.';
            } else {
                $message[] = 'Password tidak cocok!';
            }
        }
    } else {
        $message[] = 'Token tidak valid atau sudah kedaluwarsa.';
    }
} else {
    header('location:index.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css"> 

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">
    <form action="" method="post">
        <h3>Reset Password</h3>
        <input type="password" name="new_password" required placeholder="Masukkan password baru" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="password" name="confirm_password" required placeholder="Konfirmasi password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
        <input type="submit" value="Reset Password" class="btn" name="reset_submit">
        <?php
        if (isset($message)) {
            foreach ($message as $msg) {
                echo "<p class='error'>$msg</p>";
            }
        }
        ?>
    </form>
</section>

<?php include 'components/footer.php'; ?>

</body>
</html>
