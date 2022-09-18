<?php
  include("inc/functions.php");
  $class = new Main();
  $conn = $class->conn;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>
    Login - <?php echo $class->getSiteSettings("site_name") ?>
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="assets/css/soft-ui-dashboard.css?v=1.0.6" rel="stylesheet" />
  <!-- Custom Css -->
  <link rel="stylesheet" type="text/css" href="assets/css/custom.css?v=<?php echo time() ?>">
  <!-- Notifaya Css -->
  <link rel="stylesheet" type="text/css" href="assets/notifaya/notifaya.css">
</head>

<body class=""> 
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
              <span class="login-logo-box">
                  <img src="assets/img/logo.png">
                </span>
              <div class="card card-plain">
                <div class="card-header pb-0 text-left bg-transparent login-header">
                  <img src="assets/img/brand-logo.png">
                  <!-- <h3 class="font-weight-bolder text-info text-gradient">Churchaly</h3> -->
                  <p class="mb-0">Enter your email and password to sign in</p>
                </div>
                <div class="card-body">
                  <form role="form">
                    <label>Email</label>
                    <div class="mb-3">
                      <input type="email" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="email-addon">
                    </div>
                    <label>Password</label>
                    <div class="mb-3">
                      <input type="email" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="password-addon">
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="rememberMe" checked="">
                      <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <div class="text-center">
                      <button type="button" class="btn bg-gradient-info w-100 mt-4 mb-0 signinbtn">Sign in</button>
                    </div>
                  </form>
                </div>
                <div class="card-footer pt-0 px-lg-2 px-1 login-footer">
                  <p class="mb-4 text-sm mx-auto">
                    Forgot your password?
                    <a href="javascript:;" class="text-info text-gradient font-weight-bold">Reset</a>
                  </p>
                  <p class="mb-0 text-secondary ">
                    Copyright © <?php echo $class->getSiteSettings("site_name") ?> <?php echo date("Y") ?>. Powered by <a href="https://tutapis.com" target="_blank">Tutapis</a>.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('assets/img/login-bg.jpeg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!--   Core JS Files   -->
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script type="text/javascript" src="assets/js/jquery.min.js"></script>
  <script type="text/javascript" src="assets/js/notifaya.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
    $(document).ready(function() {
      const notifaya = new Notifaya();
      $('.signinbtn').on('click', function () {
            notifaya.notify("Hi")
        })
      
    });
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="assets/js/soft-ui-dashboard.min.js?v=1.0.6"></script>
</body>

</html>