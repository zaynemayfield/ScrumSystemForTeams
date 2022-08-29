<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Scrum Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
  <link rel="stylesheet" href="theme.css" type="text/css">
</head>

<body>
  <div class="py-5 text-center" style="background-image: url('/img/cover-bubble-dark.svg');background-size:cover;" >
    <div class="container">
      <div class="row">
        <div class="mx-auto col-md-6 col-10 bg-white p-5">
          <h1 class="mb-4">Scrum Log in</h1>
          <?php
            if (isset($_GET['reg']) == 1) {
                echo "<h4>Thank you for registering, please Login.</h4>";
                }
          ?>
          <form method="post" action="login.php" >
            <div class="form-group"> <input type="text" class="form-control" placeholder="Enter username" id="usernamef" name="usernamef"> </div>
            <div class="form-group mb-3"> <input type="password" class="form-control" placeholder="Password" id="password" name="password"> <small class="form-text text-muted text-right">
              </small> </div> <button type="submit" name="submit" class="btn btn-primary">Submit</button>
          </form>
          <br>
          <a href="/register.php">Register Your Account</a>
        </div>
      </div>
    </div>
  </div>
  <div class="py-3">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
          <p class="mb-0">Powered By DTEKED © <?php echo date("Y"); ?> DTEKED LLC. All rights reserved</a></p>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
