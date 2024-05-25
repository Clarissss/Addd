<?php
session_start();
include('server/connection.php');

if (isset($_SESSION['logged_in'])) {
}

// if (isset($_SESSION['logged_in_adm'])) {
//   header('Location: adminDashboard.php');
//   exit;
// }

if (isset($_POST['login_btn'])) {
  $login_credential = $_POST['login_credential'];
  $password = $_POST['password'];

  // Check if user is an admin
  $query = "SELECT * FROM admins WHERE admin_name =? AND admin_password =? LIMIT 1";
  $stmt_login = $conn->prepare($query);
  $stmt_login->bind_param('ss', $login_credential, $password);
  $stmt_login->execute();
  $stmt_login->store_result();

  if ($stmt_login->num_rows() == 1) {
    $stmt_login->bind_result($admin_id, $admin_name, $admin_password, $admin_phone, $admin_photo);
    $stmt_login->fetch();
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['admin_name'] = $admin_name;
    $_SESSION['admin_phone'] = $admin_phone;
    $_SESSION['admin_photo'] = $admin_photo;
    $_SESSION['logged_in_adm'] = true;
    header('Location: adminDashboard.php');
    exit;
  } else {
    // Check if user is a regular user
    $query = "SELECT * FROM users WHERE (user_email =? OR user_phone =?) AND user_password =? LIMIT 1";
    $stmt_login = $conn->prepare($query);
    $stmt_login->bind_param('sss', $login_credential, $login_credential, $password);
    $stmt_login->execute();
    $stmt_login->store_result();

    if ($stmt_login->num_rows() == 1) {
      $stmt_login->bind_result($user_id, $user_name, $user_email, $user_password, $user_address, $user_phone, $user_photo);
      $stmt_login->fetch();
      $_SESSION['user_id'] = $user_id;
      $_SESSION['user_name'] = $user_name;
      $_SESSION['user_email'] = $user_email;
      $_SESSION['user_address'] = $user_address;
      $_SESSION['user_phone'] = $user_phone;
      $_SESSION['user_photo'] = $user_photo;
      $_SESSION['logged_in'] = true;
      header('Location: index.php');
      exit;
    } else {
      header('Location: index.php?error=1');
      exit;
    }
  }
}

if (isset($_GET['logout'])) {
  if (isset($_SESSION['logged_in'])) {
    unset($_SESSION['logged_in']);
    unset($_SESSION['user_email']);
    header('location: index.php');
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Computopia</title>

  <!-- icon -->
  <link rel="icon" href="img/ajazz2.svg" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,300;0,400;0,700;1,700&display=swap" rel="stylesheet" />

  <!-- feather icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <!-- my style -->
  <link rel="stylesheet" href="css/style.css" />

  <!-- alpineJs -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- app -->
  <script src="src/app.js"></script>
</head>

<body>
  <!-- page loader start -->
  <div class="loader">
    <span class="loader_dot" style="--d: 200ms"></span>
    <span class="loader_dot" style="--d: 400ms"></span>
    <span class="loader_dot" style="--d: 600ms"></span>
    <span class="loader_dot" style="--d: 800ms"></span>
    <span class="loader_dot" style="--d: 1000ms"></span>
  </div>
  <!-- page loader end -->

  <!-- navbar start -->
  <nav class="navbars" x-data>
    <a href="index.php" class="navbars-logo">Compu<span>topia</span> .</a>

    <div class="navbars-nav">
      <a href="index.php">Home</a>
      <a class="active" href="about.php">About</a>
      <a href="shop.php">Shop</a>
      <a href="contact.php">Contact</a>
      <button class="btnLogin-popup" x-show="!isLoggedIn"> Login </button>
    </div>
    <div class="navbars-extra">
      <a href="#" id="search-button"> <i data-feather="search"></i></a>
      <a href="#" id="shopping-cart-button">
        <i data-feather="shopping-cart"></i>
        <span class="quantity-badge" x-show="$store.cart.quantity" x-text="$store.cart.quantity"></span>
      </a>
      <div class="dropdown" id="user-dropdown" x-show="isLoggedIn">
        <a href="#" id="user" class="dropdown-toggle"> <i data-feather="user"></i></a>
        <ul class="dropdown-menu">
          <li><a href="userProfile.php" class="dropdown-item">User Profile</a></li>
          <li><a href="#" class="dropdown-item">Recent Orders</a></li>
          <li><a href="index.php?logout=1" class="dropdown-item">Logout</a></li>
        </ul>
      </div>
      <a href="#" id="hamburger-menu"> <i data-feather="menu"></i></a>
    </div>


    <!-- search form start -->
    <div class="search-form">
      <input type="search" id="search-box" placeholder="Search Here..." />
      <label for="search-box"><i data-feather="search"></i></label>
    </div>
    <!-- search form end -->

    <!-- shopping cart start -->
    <div class="shopping-cart">
      <template x-for="(item, index) in $store.cart.items" x-keys="index">
        <div class="cart-item">
          <img :src="`img/products/${item.img}`" :alt="item.name" />
          <div class="item-detail">
            <h3 x-text="item.name"></h3>
            <div class="item-price">
              <span x-text="rupiah(item.price)"></span> &times;
              <button id="remove" @click="$store.cart.remove(item.id)">&minus;</button>
              <span x-text="item.quantity"></span>
              <button id="add" @click="$store.cart.add(item)">&plus;</button> &equals;
              <span x-text="rupiah(item.total)"></span>
            </div>
          </div>
        </div>
      </template>
      <h4 x-show="!$store.cart.items.length" style=" margin-top: 1rem;">Cart is Empty</h4>
      <h4 x-show="$store.cart.items.length">Total : <span x-text="rupiah($store.cart.total)"></span></h4>

      <div class="form-container" x-show="$store.cart.items.length">
        <form action="" id="checkoutForm">
          <h5>Customer Detail</h5>
          <label for="name">
            <span>Name</span>
            <input type="text" name="name" id="name">
          </label>

          <label for="email">
            <span>Email</span>
            <input type="email" name="email" id="email">
          </label>

          <label for="phone">
            <span>Phone</span>
            <input type="number" name="phone" id="phone" autocomplete="off">
          </label>

          <button class="checkout-button" type="button" id="checkout-button" value="checkout">
            Checkout
          </button>

        </form>
      </div>
    </div>
    <!-- shopping cart end -->
  </nav>
  <!-- navbar end -->

  <!-- login pop-up start -->
  <div class="wrapper">
    <span span class="icon-close">
      <i data-feather="x-circle"></i>
    </span>

    <div class="form-box login">
      <h2>Login</h2>
      <form method="post">
        <div class="input-box">
          <span class="icon">
            <i data-feather="user"></i>
          </span>
          <input type="text" name="login_credential" required>
          <label>Username, Email, or Phone Number</label>
        </div>
        <div class="input-box">
          <span class="icon">
            <i data-feather="lock"></i>
          </span>
          <input type="password" name="password" required>
          <label>Password</label>
        </div>
        <div class="remember-forgot">
          <label><input type="checkbox"> Remember me </label>
        </div>
        <button type="submit" class="btnUser" name="login_btn">Login</button>
        <div class="login-register">
          <p>Don't have an account? <a href="#" class="register-link">Register</a></p>
        </div>
      </form>
    </div>

    <div class="form-box register">
      <h2>Register</h2>
      <form method="post" action="registerLog.php">
        <div class="input-box">
          <span class="icon">
            <i data-feather="user"></i>
          </span>
          <input type="text" name="user_name" required>
          <label>Username</label>
        </div>
        <div class="input-box">
          <span class="icon">
            <i data-feather="mail"></i>
          </span>
          <input type="mail" name="user_email" required>
          <label>Email</label>
        </div>
        <div class="input-box">
          <span class="icon">
            <i data-feather="lock"></i>
          </span>
          <input type="password" name="user_password" required>
          <label>Password</label>
        </div>
        <div class="remember-forgot">
          <label><input type="checkbox"> I agree to the terms & conditions </label>
        </div>
        <button type="submit" class="btnRegister">Register</button>
        <div class="login-register">
          <p>Already have an account? <a href="#" class="login-link">Login</a></p>
        </div>
      </form>
    </div>
  </div>
  <!-- login pop-up end -->

  <!-- about section start -->
  <section id="about" class="about">
    <br><br>
    <h2><span>About</span> Us</h2>

    <div class="row">
      <div class="about-img">
        <img src="img/login.svg" alt="About Us" />
      </div>
      <div class="content">
        <h3>Kenapa memilih product kami?</h3>
        <p>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Harum
          ratione delectus voluptas tempora nulla repudiandae, eius, ipsam
          minus sapiente debitis sunt eum ipsa quibusdam blanditiis et aut ut.
          Aliquam, esse.
        </p>
      </div>
    </div>
  </section>
  <!-- about section end -->

  <!-- team section start -->
  <section class="kita">
    <h2>Our Team</h2>
    <div class="team">
      <div class="container">
        <input type="radio" name="slide" id="c1">
        <label for="c1" class="card">
          <div class="row">
            <div class="icon">1</div>
            <div class="description">
              <h4>Ridho Sami Rabbani</h4>
              <p>Software Tester</p>
            </div>
          </div>
        </label>
        <input type="radio" name="slide" id="c2">
        <label for="c2" class="card">
          <div class="row">
            <div class="icon">2</div>
            <div class="description">
              <h4>Egi Prayogi</h4>
              <p>Backend Developer</p>
            </div>
          </div>
        </label>
        <input type="radio" name="slide" id="c3">
        <label for="c3" class="card">
          <div class="row">
            <div class="icon">3</div>
            <div class="description">
              <h4>Bill Hafidz Zufarino</h4>
              <p>Frontend Developer</p>
            </div>
          </div>
        </label>
        <input type="radio" name="slide" id="c4">
        <label for="c4" class="card">
          <div class="row">
            <div class="icon">4</div>
            <div class="description">
              <h4>Ilyas Kalamullah</h4>
              <p>Analyst</p>
            </div>
          </div>
        </label>
      </div>
    </div>
  </section>
  <!-- team section end -->

  <!-- footer start -->
  <footer>
    <div class="socials">
      <a href="#"><i data-feather="instagram"></i></a>
      <a href="#"><i data-feather="twitter"></i></a>
      <a href="#"><i data-feather="facebook"></i></a>
    </div>

    <div class="links">
      <a href="index.php">Home</a>
      <a class="active" href="about.php">About Us</a>
      <a href="shop.php">Shop</a>
      <a href="contact.php">Contact</a>
    </div>

    <div class="credits">
      <p>Created by <a href="">Kelompok Computopia</a>. | &copy; 2024.</p>
    </div>
  </footer>
  <!-- footer end -->

  <!-- feather icons -->
  <script>
    feather.replace();
  </script>

  <!-- my javasript -->
  <script src="js/script.js"></script>

  <script>
    let isLoggedIn = <?php echo isset($_SESSION['logged_in']) ? 'true' : 'false'; ?>;
  </script>
</body>

</html>