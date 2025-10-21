<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartCanteen</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
      <a href="index.php" class="nav-link active">
        <img src="../assets/img/smartcanteenLogo.png" alt="SmartCanteen logo" class="logo">
      </a>
      <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between gap-3" id="navbarCollapse">
        <div class="navbar-nav gap-5 w-100 padding">
          <a href="index.php" class="nav-link homecolor">Home</a>
          <a href="about.php" class="nav-link aboutcolor">About</a>
        </div>
        <div class="navbar-nav position-relative d-flex align-items-center gap-3">
        <a href="login.php" class="nav-link logincolor loginbg">Login</a>
        <a href="register.php" class="nav-link registercolorr registerbg">Register</a>
    </div>
    </div>
  </div>
</nav>

<div class="forgot align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card p-4">
                    <img src="assets/img/forgotpass.png" class="logo mx-auto h-50" alt="Forgot Password">
                    <h3 class="text-center forgotcolor">Account Recovery</h3>
                    <p class="text-center mb-4">Enter your email address below to reset your password.</p>
                    <form action="forgot_password.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="login.html" class="btn btn-warning forgotbtn">Back</a>
                            <button type="submit" class="btn btn-warning forgotbtn">Send Reset Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>