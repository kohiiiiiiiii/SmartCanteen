<?php
session_start();

$saved_email = isset($_COOKIE['email']) ? $_COOKIE['email'] : "";
$remember_checked = !empty($saved_email) ? "checked" : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartCanteen | Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
      <a href="customer/index.php" class="nav-link active"><img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo"></a>
      <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between gap-3" id="navbarCollapse">
        <div class="navbar-nav gap-5 w-100 padding">
          <a href="customer/index.php" class="nav-link homecolor">Home</a>
          <a href="customer/about.php" class="nav-link aboutcolor">About</a>
        </div>
        <div class="navbar-nav position-relative d-flex align-items-center gap-3">
          <a href="login.php" class="nav-link logincolor loginbg">Login</a>
          <a href="register.php" class="nav-link registercolorr registerbg">Register</a>
        </div>
      </div>
  </div>
</nav>

<div class="login justify-content-center align-items-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card p-4">
          <img src="assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo d-block mx-auto">
          <h3 class="text-center logincolor2">Login</h3>
          <p class="text-center">Please log in to continue.</p>

          <!-- ✅ Form submits to process_login.php -->
          <form action="login_process.php" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Email Address:</label>
              <input type="email" class="form-control" id="email" name="email"
                     placeholder="Enter email"
                     value="<?php echo htmlspecialchars($saved_email); ?>" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password:</label>
              <input type="password" class="form-control" id="password" name="password"
                     placeholder="Enter password" required>
            </div>
            <div class="mb-3 form-check">
              <input class="form-check-input" type="checkbox" id="remember" name="remember"
                     <?php echo $remember_checked; ?>>
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-warning d-block mx-auto loginbtn">Login</button>
            <div class="text-center mt-3">
              <p>Don't have an account? <a href="register.php" class="registercolor">Register</a></p>
              <a href="forgot.php" class="forgotcolor">Forgot Password?</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
