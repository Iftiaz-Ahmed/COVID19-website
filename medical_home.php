<?php require_once("conn.php"); ?>
<?php include("header.php"); ?>

<?php 
	
	if(isset($_POST["clear"])){
		$_SESSION["u_id"] = " ";
		$_SESSION["date"] = " ";
		$_SESSION["temp_no"] = " ";
	}


?>

<?php 
if (isset($_SESSION["authen"])){  
    if ($_SESSION["authen"]){
 ?>

  <section class="medical">
    <div class="container" id="app">
        <h4>WELCOME</h4>
        <h2 style="color: var(--cyan);margin-bottom: 50px;font-weight: 900;"><?php $n = $_SESSION["i_name"]; echo $n; ?></h2>
        <form method="post">
	        <label>Search the Person</label><br>
	        <input type="text" name="number" placeholder="Enter mobile number">
	        <button type="submit" name="search">Search</button>	
        </form>
        
    </div>
  </section>
  <hr>

  <?php 
  	if(isset($_POST["search"]) || isset($_POST["update"])){
  		if(isset($_POST["number"])){
  			if($_POST["number"] != " "){
  				$_SESSION["temp_no"] = $_POST["number"];
  			}
  		}
		$mobile = $_SESSION["temp_no"];
		$query = $mobile; 
		// gets value sent over search form
		$min_length = 8;
		// you can set minimum length of the query if you want
		if(strlen($query) >= $min_length){ // if query length is more or equal minimum length then
			$query = htmlspecialchars($query); 
			// changes characters used in html to their equivalents, for example: < to &gt;
			$query = mysqli_real_escape_string($conn,$query);
			// makes sure nobody uses SQL injection
			$sql = "SELECT * FROM users INNER JOIN user_status ON user_status.u_id = users.u_id WHERE (`phone` LIKE '%".$query."%')" or  die(mysql_error());
			$raw_results = mysqli_query($conn,$sql);
			if(mysqli_num_rows($raw_results) > 0){ // if one or more rows are returned do following
   ?>
   <head>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" />
    <script src="https://api.tiles.mapbox.com/mapbox-gl-js/v1.10.0/mapbox-gl.js"></script>
    <link href="https://api.tiles.mapbox.com/mapbox-gl-js/v1.10.0/mapbox-gl.css" rel="stylesheet" />
   </head>

  <section class="result">
  	<div style="text-align: right; padding: 2px 8% 5px;">
  		<form method="post"><button style="" class="editt" name="clear">Clear All</button></form>
  	</div>
  	<div class="container">
  	  <div class="table-responsive">
	  	<table class="table">
		  <thead class="thead-dark">
		    <tr>
		      <th scope="col">Name</th>
			  <th scope="col">Age</th>
			  <th scope="col">Gender</th>
		      <th scope="col">Phone</th>
		      <th scope="col">NID</th>
		      <th scope="col">Status</th>
		    </tr>
		  </thead>
		  <tbody>
		  	<?php 
	
				while($row = mysqli_fetch_assoc($raw_results)){
					$_SESSION["u_id"] = $row["u_id"];
					$name = $row["name"];
					$p = $row["phone"];
					$nid = $row["NID"];
					$age = $row["age"];
					$gender = $row["gender"];
					$status = $row["u_status"];
					$by = $row["updated_by"];
					$affected_person = $row["affected_person"];
					$u_date = $row["u_date"];

					if($status == 1) {
						$s = "Not affected!";
						$color = "#28a745";
					} else if($status == 2) {
						$s = "Affected!";
						$color = "#dc3545";
					} else if($status == 3) {
						$s = "Close Contact!";
						$color = "#fd7e14";
					} else if($status == 4) {
						$s = "Recovered";
						$color = "#007bff";
					} else if($status == 5) {
						$s = "Dead";
						$color = "#17a2b8";
					} else {
						$s = " ";
					}
			?>
		    <tr style="text-transform: uppercase;">
		      <td><?php echo $name; ?></td>
			  <td><?php echo $age; ?></td>
			  <td><?php echo $gender; ?></td>
		      <td><?php echo $p; ?></td>
		      <td><?php echo $nid; ?></td>
		      <td>
		      	<span style="color: <?php print $color; ?>; font-weight: 900;"><?php echo $s; ?></span>
		      	<button class="edit" onclick="toggler('fname')"><i class="fas fa-pen"></i></button>
			    <form method="POST" id="fname" style="display: none;">     <!-- action="javascript:AnyFunction();" -->
			    	<select name="status" id="status" required>
					  <option value="1">Not Affected</option>
					  <option value="2">Affected</option>
					  <option value="4">Recovered</option>
					  <option value="5">Passed Away</option>
					</select>
					<button type="submit" id="update-button" name="update">Update</button>
				</form>
		      </td>
		    </tr>
			<?php } ?>
		  </tbody>
		</table>
		
		<?php 
			if ($status == 3) {
		?>
				<p style="color: #fd7e14;"> <?php echo $by; ?> </p>
				<p style="color: #dc3545;"> Suspected person: <span style="font-weight: 600;"> <?php echo $affected_person; ?> </span> </p>
		<?php
			} else if ($status == 5){
		?>
				<p style="color: #17a2b8;">
					The person died on <span style="font-weight: 600;"> <?php echo $u_date; ?> </span> <br>
					according to <span style="font-weight: 600;"> <?php echo $by; ?> </span>
				</p>
		<?php
			}
		?>

		  <section class="location">
		  	<div class="container">
		  		<?php 
		  			
		  			$uid = $_SESSION["u_id"];

		  			$sql = "SELECT * FROM user_locations WHERE u_id='$uid' ORDER BY serial DESC";
		  			$result = mysqli_query($conn, $sql);

			        if (mysqli_num_rows($result) > 0) {
			        	$location_date = array();
			            while($row = mysqli_fetch_assoc($result)) {
			            	$location = array();
			            	array_push($location, $row['longitude'],$row['latitude'],$row['date_time']);
			            	// $_SESSION["date"] = $row["date"];
			            	array_push($location_date, $location);
			            }
			            $long = array();
			            $lat = array();

			  			foreach ($location_date as $value) {
			  				array_push($long, $value[0]);
			  				array_push($lat, $value[1]);
			  			}
			            
			        ?>
		  	</div><br>
		  
		  	<h6>Showing the person's location data<b></b></h6>
			   <?php
			        }
		  		?>
		  </section>

		  <div id="map"></div>

		  </script>
			<?php 
			} else{ // if there is no matching rows do following
				$_SESSION["u_id"] = " ";
			?>
				<h5 style="margin-top: 20px;">No results found for <span style="font-weight: 900;">"<?php echo $query; ?>"</span></h5>
		<?php }
			} else{ // if query length is less than minimum
				echo "<h5 style='color:red;'>Mobile data entry is inaccurate!</h5>";      
			}
		
		?>
	  </div>
	</div>
  </section>

  <script type="text/javascript">
  	var coordinates = <?php echo json_encode($location_date) ?>;
  	var long = <?php echo json_encode($long) ?>;
    var lat = <?php echo json_encode($lat) ?>;

	// Initialize and add the map
	function initMap() {

	  var cntr = {lat: <?php echo $lat[0] ?>, lng: <?php echo $long[0] ?>};
	
	  var map = new google.maps.Map(
	      document.getElementById('map'), {zoom: 14, center: cntr});

	  var infowindow = new google.maps.InfoWindow();
	
	  <?php foreach ($location_date as $values): ?>
	  	var markerLatlng = new google.maps.LatLng(<?php echo $values[1] ?>, <?php echo $values[0] ?>);

	  	<?php 
	  		$ts = explode(" ", $values[2]);
	  	?>
	
	    var iwContent = '<h5 style="color: red;"><b>Timestamp</b></h5><p><b>Date: </b><?php echo $ts[0] ?></p><p><b>Time: </b><?php echo $ts[1] ?></p>';
	    createMarker(markerLatlng , iwContent);
	  <?php endforeach ?>

	  function createMarker(latlon,iwContent) {
	    var marker = new google.maps.Marker({
	        position: latlon,	     
	        map: map
	    });


		google.maps.event.addListener(marker, 'click', function () {
		    infowindow.setContent(iwContent);
		    infowindow.open(map, marker);
		    });

      }
 
	}
    </script>
    <!--Load the API from the specified URL
    * The async attribute allows the browser to render the page while the API loads
    * The key parameter will contain your own API key (which is not needed for this tutorial)
    * The callback parameter executes the initMap() function
    -->
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=apiKEY&callback=initMap">
    </script>
  
 	<!-- <div id="result"></div> -->

<?php
} else {}
}else{
echo "<h4 style='color:red;text-align:center;margin-top:10%;'>You are not logged in!</h4>";
}} else {echo "<h4 style='color:red;text-align:center;margin-top:10%;'>You are not logged in!</h4>";} ?>


<!-- JQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
<script>

$(document).ready(function() {

$("#update-button").click(function() {  

	var status = $('#status').val();
	// alert(status);                  

	$.ajax({    //create an ajax request to display.php
		type: "POST",
		url: "update.php",             
		data: {st: status},   //expect html to be returned                
		cache: false,
		success: function(response){                    
			// $("#result").html(response); 
			// alert(response);
		}

});
});
});

</script>

<?php include("footer.html") ?>
