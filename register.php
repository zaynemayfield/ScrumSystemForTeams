<?php
require "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// primary validate function
  function validate($str)
  {
    return trim(htmlspecialchars($str));
  }

  if (empty($_POST['fname'])) {
    $nameError = 'First name should be filled';
  } else {
    $fname = validate($_POST['fname']);
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $fname)) {
      $nameError = 'First name can only contain letters, numbers and white spaces';
    }
  }
  if (empty($_POST['lname'])) {
    $lnameError = 'Last Name should be filled';
  } else {
    $lname = validate($_POST['lname']);
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $lname)) {
      $nameError = 'Last name can only contain letters, numbers and white spaces';
    }
  }

  if (empty($_POST['usernamef'])) {
    $usernameError = 'Please enter your username';
  } else {
    $usernamef = $_POST['usernamef'];
  }

  if (empty($_POST['password'])) {
    $passwordError = 'Password cannot be empty';
  } else {
    $password = validate($_POST['password']);
    if (strlen($password) < 6) {
      $passwordError = 'Pasword should be longer than 6 characters';
    }
  }
  if (empty($_POST['password2'])) {
    $confirmPasswordError = 'Confirm Password cannot be empty';
  } else {
    $password2 = validate($_POST['password2']);
    if ($password === $password2) {
      $password = password_hash($password, PASSWORD_DEFAULT);
    } else { $confirmPasswordError = 'Passwords did not match';}
  }
    if (!empty($_POST['terms'])) {
        $terms = "agree";
      } else {
        $termsError = 'Please Agree to Terms and Services';
      }
  if (empty($fnameError) && empty($lnameError) && empty($usernameError) && empty($passwordError) && empty($confirmPasswordError) && empty($termsError)) {
    // great form filling
    $usernamef = strtolower($usernamef);
$sql = "SELECT * FROM user WHERE username ='$usernamef'";
$count = $connection->query($sql)->fetchColumn();
    if ($count <1) {

    try {
      $new_user = array(
        "fname" => $fname,
        "lname" => $lname,
        "username" => $usernamef,
        "password" => $password,
        "terms" => $terms
      );

      $sql = sprintf(
        "INSERT INTO %s (%s) values (%s)",
        "user",
        implode(", ", array_keys($new_user)),
        ":" . implode(", :", array_keys($new_user))
      );

      $statement = $connection->prepare($sql);
      $statement->execute($new_user);
    } catch (PDOException $error) {
      echo $sql . "<br>" . $error->getMessage();
    }

    header('Location: signin.php?reg=1');
    die();
  } else { $usernameError = 'This username is already registered.';}
}
}



?>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Scrum Registration</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
  <link rel="stylesheet" href="theme.css" type="text/css">
  <style type="text/css">.error {color:red;}</style>
</head>

<body>
  <div class="py-5 text-center" style="background-image: url('/img/cover-bubble-dark.svg');background-size:cover;" >
    <div class="container">
      <div class="row">
        <div class="mx-auto col-lg-6 col-10 bg-white">
          <h1>Register Your Account</h1>
          <form method="post" class="text-left">
            <div class="form-group">
            <label for="fname">First Name</label>
            <input type="text" name="fname" id="fname" class="form-control" placeholder="Johann" value="<?php if (isset($fname)) echo $fname ?>" required>
            <span class="error"><?php if (isset($fnameError)) echo $fnameError ?></span>
            </div>
			<div class="form-group">
      <label for="lname">Last Name</label>
      <input type="text" name="lname" id="lname" class="form-control" placeholder="Goethe" value="<?php if (isset($lname)) echo $lname ?>" required>
      <span class="error"><?php if (isset($lnameError)) echo $lnameError ?></span>
      </div>
            <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" name="usernamef" id="usernamef" placeholder="batman" value="<?php if (isset($usernamef)) echo $usernamef ?>" required>
            <span class="error"><?php if (isset($usernameError)) echo $usernameError ?></span>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="password">Password (6 characters minimum)</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="" value="<?php if (isset($_POST['password']) && empty($passwordError) && empty($confirmPasswordError)) echo $_POST['password'] ?>" required>
                <span class="error"><?php if (isset($passwordError)) echo $passwordError ?></span>
              </div>
              <div class="form-group col-md-6">
                <label for="password2">Confirm Password (6 characters minimum)</label>
                <input type="password" class="form-control" name="password2" id="password2" placeholder="" value="<?php if (isset($_POST['password2']) && empty($passwordError) && empty($confirmPasswordError)) echo $_POST['password'] ?>" required>
                <span class="error"><?php if (isset($confirmPasswordError)) echo $confirmPasswordError ?></span>
              </div>
            </div>
			      <div class="form-group">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms" name="terms" value="on">
                <label class="form-check-label" for="terms"> I Agree with <a href="terms.php" target="_blank">Term and Conditions</a> of the service </label>
                <span class="error"><?php if (isset($termsError)) echo $termsError ?></span>
              </div>
            </div> <button type="submit" name="submit" class="btn btn-primary">Register</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="py-3">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
          <p class="mb-0">Powered By DTEKED © <?php echo date("Y"); ?> DTEKED LLC. All rights reserved</p>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
