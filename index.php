<?php
    ob_start(); //to store the header in the buffer, we will use the php ob_start() function 
    require_once("conn.php");
    $_SESSION["msg"] = " " 
?> 
<?php include("header.php"); ?>

 <!-- Sign in part -->

<?php  
    $_SESSION["authen"] = False;

    if(isset($_POST['signin'])){

    	$iid = $_POST["iid"]; 
        $password = mysqli_real_escape_string($conn, $_POST["password"]);  
        $pass = $password;

        $sql = "SELECT * FROM medical_institute WHERE i_id='$iid'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $_SESSION["pass"] = $row["password"];
                $p = $row["password"];
                if($pass == $p){
                    $_SESSION["msg"] = " ";
                } else {
                    $_SESSION["msg"] = "* Wrong password!";
                }
            }
        } else {
            $_SESSION["msg"] = "* Your institution not added yet!";
        }

        $sql = "SELECT * FROM medical_institute WHERE i_id='$iid' AND password='$pass'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $_SESSION["i_name"] = $row["name"];
                $_SESSION["i_id"] = $row["i_id"];
                $_SESSION["em"] = $row["email"];
                $_SESSION["phone"] = $row["phone"];
                $_SESSION["authen"] = True;
            }
        } else {
            $_SESSION["authen"] = False;
        }

        $change = $_SESSION["authen"];
        if($change == True){
            header("Location: medical_home.php");
            ob_end_flush(); //to clean the buffer, we will use the ob_end_flush() function
        }
    }
    $m = $_SESSION["msg"];
?>
<head>
    <link rel="stylesheet" type="text/css" href="css/login.css">
</head>
    
<body class="parallax">

	<div class="row" style="margin: 0;">
		<div class="col-sm">
			<div class="form" id="form">
			 <form method="POST" name="signin_form" action="">
			  <h1 style="color: black; margin-top: 20px;">Sign In</h1>
			  <div class="field email">
			    <div class="icon"></div>
			    <input class="input" id="email" name="iid" type="text" placeholder="Institute ID" autocomplete="on"/>
			  </div>
			  <div class="field password">
			    <div class="icon"></div>
			    <input class="input" id="password" name="password" type="password" placeholder="Password"/>
			  </div>
              <p style="color: red;text-align: left;font-size: 15px;margin: 5px;"><b><?php echo "$m"; ?></b></p>
			  <button class="button" id="submit" name="signin">LOGIN
			    <div class="side-top-bottom"></div>
			    <div class="side-left-right"></div>
			</form>
			</div>
		</div>
	</div>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>

</body>


<?php include("footer.html"); ?>
