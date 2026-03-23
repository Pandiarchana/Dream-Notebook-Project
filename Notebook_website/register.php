<?php
include("dbconnect.php");
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
  <title>Sign up – DreamDiary</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="container">

    <div class="login-box">
      <h2><a href="Homepage.html">Dream Diary</a></h2>
      <h1>Sign up</h1>

      <button class="google-btn">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="google">
        Continue with Google
      </button>

      <div class="divider">
        <span>OR</span>
      </div>

      <form method="POST" action="" onsubmit="return validate()">

      <label>Email</label>
      <input type="email" name="email" id="email" placeholder="Enter your email" required>

      <label>Username</label>
      <input type="text" name="name" id="name" placeholder="Create a Username" required>

      <label>Password</label>
      <input type="password" name="pass" id="pass" placeholder="Create a password" required>

      <button type="submit" name="sub" class="login-btn">Signup</button>

    </form>

    <p class="signup">
      Already have an account? <a href="login.php">Log in</a>
    </p>

    </div>
  </div>

  <script type="text/javascript">
    function validate(){

      var email = document.getElementById("email").value
      if(email == ""){
        alert("Please Enter email ")
        return false
      }
      var password = document.getElementById("pass").value
      if(password==""){
        alert("Please Enter password")
        return false
      }

      var uname = document.getElementById("name").value
      if(uname == ""){
        alert("Please Enter Username ")
        return false
      }

      var email = document.getElementById("email").value
      var pattern = /^[a-zA-Z0-9.-_]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$/
      if(!pattern.test(email)){
        alert("Enter valid email:")
        return false
      }
    }
  </script>
</body>
</html>

<?php
if(isset($_POST['sub'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];

$query = "insert into user(username,email,password) values('$name','$email','$pass')";

//execute
mysqli_query($connect,$query) or die("Insert Error");

header("Location: login.php?msg=Registered Successfully");
        exit();
}

?>