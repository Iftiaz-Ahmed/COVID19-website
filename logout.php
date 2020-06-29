<?php 
    if(isset($_POST['logout'])){
    session_destroy(); //session destroy followed by s.start is given so that the cart details doesn't show in other users cart
    session_start();
    $_SESSION["authen"] = False;
    header("Location: index.php");
    ob_end_flush();
  }
  header("refresh:1; url=index.php");
?> 