
<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit();
}
// Fetch the latest orders
$latest_orders_query = $conn->prepare("SELECT * FROM `orders` ORDER BY `placed_on` DESC LIMIT 1");
$latest_orders_query->execute();

?>

<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="dashboard">

   <h1 class="heading">DASHBOARD</h1>

   <div class="box-container">

      <div class="box">
         <h3>Selamat Datang!</h3>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="update_profile.php" class="btn">Perbarui Profil</a>
      </div>
     
      <audio id="notificationSound" src="../audio/notification.mp3"></audio>
      
      <div class="box">
        <?php
            $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_pendings->execute(['pending']);
            $total_pendings = 0;
            if($select_pendings->rowCount() > 0){
                while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
                    $total_pendings += $fetch_pendings['total_price'];
                }
            }
        ?>
        <h3><span>Rp.</span><span id="pendingOrdersTotal"><?= $total_pendings; ?></span><span>,-</span></h3>
        <p>Total Pesanan Pending: <span id="pendingOrdersCount"><?= $select_pendings->rowCount(); ?></span></p>
        <a href="placed_orders.php" class="btn">Lihat Pesanan</a>
      </div>

      <div class="box">
         <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['completed']);
            if($select_completes->rowCount() > 0){
               while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
                  $total_completes += $fetch_completes['total_price'];
               }
            }
         ?>
         <h3><span>Rp.</span><?= $total_completes; ?><span>,-</span></h3>
         <p>Pesanan Selesai</p>
         <a href="placed_orders.php" class="btn">Lihat Pesanan</a>
      </div>

      <div class="box">
         <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $number_of_orders = $select_orders->rowCount();
         ?>
         <h3><?= $number_of_orders; ?></h3>
         <p>Total Pesanan</p>
         <a href="placed_orders.php" class="btn">Lihat Pesanan</a>
      </div>

      <div class="box">
         <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            $number_of_products = $select_products->rowCount();
         ?>
         <h3><?= $number_of_products; ?></h3>
         <p>Produk Ditambahkan</p>
         <a href="products.php" class="btn">Lihat Produk</a>
      </div>

      <div class="box">
         <?php
            $select_users = $conn->prepare("SELECT * FROM `users`");
            $select_users->execute();
            $number_of_users = $select_users->rowCount();
         ?>
         <h3><?= $number_of_users; ?></h3>
         <p>Pengguna Normal</p>
         <a href="users_accounts.php" class="btn">Lihat Pengguna</a>
      </div>

      <div class="box">
         <?php
            $select_admins = $conn->prepare("SELECT * FROM `admins`");
            $select_admins->execute();
            $number_of_admins = $select_admins->rowCount();
         ?>
         <h3><?= $number_of_admins; ?></h3>
         <p>Pengguna Admin</p>
         <a href="admin_accounts.php" class="btn">Lihat Admin</a>
      </div>

      <div class="box">
         <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $number_of_messages = $select_messages->rowCount();
         ?>
         <h3><?= $number_of_messages; ?></h3>
         <p>Pesan Baru</p>
         <a href="messages.php" class="btn">Lihat Pesan</a>
      </div>

   </div>
</section>

<button id="initAudio" style="display: none;">Init Audio</button>

<script>
    let previousPendingCount = 0;
    let previousPendingTotal = 0;
    let audioInitialized = false;

    // Fungsi untuk memainkan suara notifikasi
    function playNotificationSound() {
        const audioElement = document.getElementById('notificationSound');
        if (audioElement) {
            audioElement.play().then(() => {
                console.log('Notification sound played');
            }).catch(error => {
                console.error('Error playing notification sound:', error);
            });
        } else {
            console.log('Audio element not found.');
        }
    }

    // Fungsi untuk menampilkan notifikasi visual
    function showVisualNotification(message) {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.backgroundColor = 'yellow';
        notification.style.padding = '10px';
        notification.style.borderRadius = '5px';
        notification.style.zIndex = '1000';
        document.body.appendChild(notification);

        setTimeout(() => {
            document.body.removeChild(notification);
        }, 5000); // Hilangkan notifikasi setelah 5 detik
    }

    // Fungsi untuk memeriksa pesanan baru dan pending
    function checkOrders() {
        console.log('Checking orders...', new Date().toLocaleTimeString());
        
        fetch('check_orders.php')
            .then(response => response.json())
            .then(data => {
                console.log('Order data received:', data);

                let notificationMessage = '';

                if (data.newOrders > 0) {
                    notificationMessage += `Ada ${data.newOrders} pesanan baru! `;
                }
                
                const pendingOrdersElement = document.getElementById('pendingOrdersCount');
                if (pendingOrdersElement) {
                    pendingOrdersElement.textContent = data.pendingOrders;
                    if (data.pendingOrders !== previousPendingCount) {
                        notificationMessage += `Jumlah pesanan pending: ${data.pendingOrders}. `;
                    }
                    previousPendingCount = data.pendingOrders;
                }

                const pendingTotalElement = document.getElementById('pendingOrdersTotal');
                if (pendingTotalElement) {
                    pendingTotalElement.textContent = data.pendingTotal;
                    if (data.pendingTotal !== previousPendingTotal) {
                        notificationMessage += `Total nilai pesanan pending: ${data.pendingTotal}. `;
                    }
                    previousPendingTotal = data.pendingTotal;
                }

                if (notificationMessage) {
                    if (audioInitialized) {
                        playNotificationSound();
                    } else {
                        console.log('Audio not initialized. Please interact with the page.');
                        showVisualNotification('Klik tombol untuk mengaktifkan audio.');
                    }
                    showVisualNotification(notificationMessage);
                }
            })
            .catch(error => console.error('Error checking orders:', error));
    }

    // Inisialisasi audio dengan interaksi pengguna
    document.getElementById('initAudio').addEventListener('click', () => {
        audioInitialized = true;
        console.log('Audio initialized');
    });

    // Periksa pesanan setiap 30 detik
    setInterval(checkOrders, 10000);

    // Tambahkan event listener untuk interaksi pengguna
    document.addEventListener('click', () => {
        if (!audioInitialized) {
            document.getElementById('initAudio').click();
        }
    });
</script>

</body>
</html>


<script src="../js/admin_script.js"></script>
   
</body>
</html>
