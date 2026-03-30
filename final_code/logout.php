<?php
session_start();
session_unset();
session_destroy();

//Homepage.html
header("Location: Homepage.html");
exit();
?>