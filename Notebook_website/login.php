<?php
include("dbconnect.php");
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" type="text/css" href="style.css">

</head>

<body>

<div class="container">
  <div class="login-box">

    <h2><a href="Homepage.php">Dream Diary</a></h2>
    <h1>LOGIN</h1>

    <button class="google-btn">
      <img src="https://www.svgrepo.com/show/475656/google-color.svg">
      Continue with Google
    </button>

    <div class="divider">
      <span>OR</span>
    </div>

    <form method="POST" action="">

      <label>Username</label>
      <input type="text" name="name" id="name" placeholder="Enter your Username">

      <label>Password</label>
      <input type="password" name="pass" id="pass" placeholder="Enter your Password">

      <button type="submit" name="sub" class="login-btn" onclick="return validate()">Login</button>

    </form>

    <p class="signup">
      Don’t have an account? <a href="register.php">Sign up</a>
    </p>

  </div>
</div>

</body>
</html>


<script>

function validate(){

  var name = document.getElementById("name").value;
  var password = document.getElementById("pass").value;

  if(name == ""){
    alert("Please enter username");
    return false;
  }

  if(password == ""){
    alert("Please enter password");
    return false;
  }

  return true;
}

</script>


<?php

if(isset($_POST['sub'])){

    $name = $_POST['name'];
    $pass = $_POST['pass'];

    $query = "SELECT * FROM user WHERE username = ?";
    $stmt = mysqli_prepare($connect, $query);

    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if($user){

        if($pass === $user['password']){

            $_SESSION['user'] = $name;

            header("Location: Userpage.php");
            exit();

        } else {

            echo "<script>alert('Incorrect Password');</script>";

        }

    } else {

        echo "<script>alert('User not found');</script>";

    }

}

?>