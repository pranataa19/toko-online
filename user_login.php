<?php

date_default_timezone_set('Asia/Jakarta'); // Konversi ke zona waktu lokal saat menampilkan

include 'components/connect.php';
include 'components/mail_function.php'; // Mengimpor fungsi email

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['login_submit'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL); // Updated filter
    $pass = $_POST['password'];
    $pass = filter_var($pass, FILTER_SANITIZE_SPECIAL_CHARS); // Updated filter
    $pass = sha1($pass);

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
    $select_user->execute([$email, $pass]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if ($select_user->rowCount() > 0) {
        $_SESSION['user_id'] = $row['id'];
        header('location:index.php');
        exit;
    } else {
        $message[] = 'Email atau password salah!';
    }
}

if (isset($_POST['forgot_submit'])) {
    $email = filter_var($_POST['forgot_email'], FILTER_SANITIZE_EMAIL);

    $check_email = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $check_email->execute([$email]);

    if ($check_email->rowCount() > 0) {
        // Generate token dan masa berlaku
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime('+5 minutes'));

        // Update token dan masa berlaku di database
        $update_token = $conn->prepare("UPDATE `users` SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update_token->execute([$token, $expiry, $email]);

        $reset_link = "http://localhost/penjualan/reset_password.php?token=$token";
        $result = sendPasswordResetEmail($email, $reset_link);

        if ($result === true) {
            $message[] = 'Link reset password telah dikirim ke email Anda.';
        } else {
            $message[] = $result; // Menampilkan pesan error dari PHPMailer
        }
    } else {
        $message[] = 'Email tidak terdaftar.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <section class="form-container">

        <form action="" method="post">
            <h3>Login</h3>
            <?php
            if (isset($message)) {
                foreach ($message as $msg) {
                    echo "<p class='error'>$msg</p>";
                }
            }
            ?>
            <input type="email" name="email" required placeholder="Masukkan email Anda" maxlength="50" class="box"
                oninput="this.value = this.value.replace(/\s/g, '')">
            <div class="password-container">
                <input type="password" id="login-pass" name="password" required placeholder="Password" minlength="8"
                    maxlength="20" class="box" title="Masukkan sandi Anda">
                <i class="fa fa-eye toggle-password" onclick="togglePassword('login-pass')"></i>
            </div>
            <input type="submit" value="Login Sekarang" class="btn" name="login_submit">

            <p>Belum punya akun?</p>
            <a href="user_register.php" class="option-btn">Klik Ini</a>

            <p><a href="#" id="forgotPasswordLink">Lupa kata sandi</a>
            <p>
        </form>

        <form action="" method="post" id="forgotPasswordForm" style="display:none;">
            <h3>Lupa Kata Sandi</h3>
            <?php
            if (isset($message)) {
                foreach ($message as $msg) {
                    echo "<p class='error'>$msg</p>";
                }
            }
            ?>
            <input type="email" name="forgot_email" required placeholder="Masukkan email Anda" maxlength="50"
                class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Kirim Pesan Autentikasi" class="btn" name="forgot_submit">
            <p><a href="user_login.php" id="backToLoginLink">Kembali ke Login</a>
            <p>
        </form>

    </section>

    <?php include 'components/footer.php'; ?>

    <script>
        function togglePassword(id) {
            var passwordField = document.getElementById(id);
            var type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Update icon based on type
            var icon = document.querySelector(`#${id} ~ .toggle-password`);
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('forgotPasswordLink').addEventListener('click', function () {
            document.querySelector('form[action=""]').style.display = 'none';
            document.getElementById('forgotPasswordForm').style.display = 'block';
        });

        document.getElementById('backToLoginLink').addEventListener('click', function () {
            document.querySelector('form[action=""]').style.display = 'block';
            document.getElementById('forgotPasswordForm').style.display = 'none';
        });
    </script>

    <script src="js/script.js"></script>

</body>

</html>
