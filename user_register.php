<?php
include 'components/connect.php';
include 'components/mail_function.php'; // Sertakan file mail_functions.php

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select_user->execute([$email]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if ($select_user->rowCount() > 0) {
        $message[] = 'Email sudah terdaftar!';
    } else {
        $verification_code = rand(1000, 9999);

        $insert_user = $conn->prepare("INSERT INTO `users` (name, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, 0)");
        $insert_user->execute([$name, $email, $pass, $verification_code]);

        $result = sendVerificationEmail($email, $verification_code);

        if ($result === true) {
            $message[] = 'Registrasi berhasil, silakan cek email Anda untuk kode verifikasi!';
            $_SESSION['email_verification'] = $email;
        } else {
            $message[] = $result;
        }
    }
}

if (isset($_POST['verify'])) {
    $verification_code = filter_var($_POST['verification_code'], FILTER_SANITIZE_STRING);
    $email = $_SESSION['email_verification'];

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND verification_code = ?");
    $select_user->execute([$email, $verification_code]);

    if ($select_user->rowCount() > 0) {
        $update_user = $conn->prepare("UPDATE `users` SET is_verified = 1 WHERE email = ?");
        $update_user->execute([$email]);

        $message[] = 'Email berhasil diverifikasi, silakan login!';
        unset($_SESSION['email_verification']);
        header('Location: user_login.php');
        exit;
    } else {
        $message[] = 'Kode verifikasi salah!';
    }
}

if (isset($_POST['resend_code'])) {
    $email = $_SESSION['email_verification'];
    $result = resendVerificationCode($email, $conn);

    if ($result === true) {
        $message[] = 'Kode verifikasi telah dikirim ulang ke email Anda!';
    } else {
        $message[] = $result;
    }
}

if (isset($_POST['cancel_verification'])) {
    unset($_SESSION['email_verification']);
    header('Location: user_register.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <section class="form-container">

        <?php if (!isset($_SESSION['email_verification'])): ?>
            <form action="" method="post">
                <h3>Daftar Akun</h3>
                <?php
                if (isset($message)) {
                    foreach ($message as $msg) {
                        echo "<p class='error'>$msg</p>";
                    }
                }
                ?>
                <input type="text" name="name" required placeholder="Username " maxlength="20" class="box">
                <input type="email" name="email" required placeholder="Alamat e-mail" maxlength="50" class="box"
                    title="Masukkan alamat email yang valid, seperti contoh@email.com">
                <div class="password-container">
                    <input type="password" id="pass" name="pass" required placeholder="Password" minlength="8" maxlength="20"
                        class="box" title="Minimal 8 karakter dan Gunakan sandi yang rumit">
                    <i class="fa fa-eye toggle-password" onclick="togglePassword('pass')"></i>
                </div>
                <div class="password-container">
                    <input type="password" id="cpass" name="cpass" required placeholder="Konfirmasi password" minlength="8"
                        maxlength="20" class="box" title="Tulis ulang sandi!">
                    <i class="fa fa-eye toggle-password" onclick="togglePassword('cpass')"></i>
                </div>
                <input type="submit" value="Daftar Sekarang" class="btn" name="submit">
                <p>Sudah memiliki akun?</p>
                <a href="user_login.php" class="option-btn">Masuk Sekarang</a>
            </form>
        <?php else: ?>
            <form action="" method="post">
                <h3>Verifikasi Email</h3>
                <?php
                if (isset($message)) {
                    foreach ($message as $msg) {
                        echo "<p class='error'>$msg</p>";
                    }
                }
                ?>
                <input type="text" name="verification_code" required placeholder="Masukkan kode verifikasi" maxlength="4"
                    class="box">
                <input type="submit" value="Verifikasi" class="btn" name="verify">
            </form>
            <form action="" method="post" style="margin-top: 1em;">
                <input type="submit" value="Kirim Ulang Kode" class="option-btn" name="resend_code">
                <input type="submit" value="Batal" class="option-btn" name="cancel_verification">
            </form>
        <?php endif; ?>

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
    </script>

    <script src="js/script.js"></script>

</body>

</html>
