<?php

include 'components/connect.php';

if(isset($_POST['verify'])){
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $code = filter_var($_POST['code'], FILTER_SANITIZE_STRING);

   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       $message[] = 'Email tidak valid!';
   } else {
       $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND verification_code = ?");
       $select_user->execute([$email, $code]);

       if($select_user->rowCount() > 0){
          $update_user = $conn->prepare("UPDATE `users` SET is_verified = 1 WHERE email = ?");
          $update_user->execute([$email]);
          $message[] = 'Email Anda telah berhasil diverifikasi!';
       } else {
          $message[] = 'Kode verifikasi tidak valid!';
       }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Verifikasi Email</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Verifikasi Email</h3>
      <?php
         if(isset($message)){
            foreach($message as $msg){
               echo "<p class='error'>$msg</p>";
            }
         }
      ?>
      <input type="email" name="email" required placeholder="Masukkan alamat email" maxlength="50"  class="box">
      <input type="text" name="code" required placeholder="Masukkan kode verifikasi" maxlength="4" class="box">
      <input type="submit" value="Verifikasi" class="btn" name="verify">
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
