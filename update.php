<?php require_once("conn.php"); ?>

<!-- Updating status in database -->
<?php
    use \Firebase\JWT\JWT;   
    $status = 0;
    $u = 0;
    $name = '';
    $data = json_decode(file_get_contents("php://input"), true);
    if(isset($_POST['st'])){
        $status = $_POST['st'];
        $u = $_SESSION["u_id"]; 
        $name = $_SESSION["i_name"];
    } else {
       $status = $data["uStatus"];
       $u = $data["uid"];
       $name = "User himself";
    }
	
	
    
//       date_default_timezone_set("Asia/Dhaka");
	// $today = date("Y-m-d h:i:a");
	$sql = "UPDATE user_status SET u_status='$status', updated_by='$name' WHERE u_id='$u'";
	if (mysqli_query($conn, $sql)) {
		// echo '<script language="javascript">';
		// echo 'alert("Data updated successfully!")';
		// echo '</script>';

	} else {
		$message = "Error: " . $sql . "<br/>" . mysqli_error($conn);
    }

//  Updating close contacts (close contact algorithm)

  if($status == 2) {                 // if the status changed to affected than the below code will be executed
    //$u = $_SESSION["u_id"];
    $timeline = 14;  //setting a timeline upto which it will extract the location > here extracting data from last 14 days
    $time_range = 5;                 
    $last_date_time = '';                  
    $date_location = array();

    // retrieving name of the affected person
    $sql = "SELECT * FROM users WHERE u_id='$u'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $affected = $row["name"];
        }
    }

    // retrieving date of the last location input of the affected person
    $sql = "SELECT date_time from user_locations WHERE u_id='$u' order by serial DESC limit 1";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $last_date_time = $row["date_time"];
        }
    }

    $last_date = explode(" ", $last_date_time);
    
    $ld = $last_date[0];
    $t = $timeline-1;
    $sql = "SELECT * FROM user_locations WHERE date_time >= ( '$ld' - INTERVAL $t DAY ) AND u_id='$u'";
    $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
                $location = array();
                array_push($location, $row['date_time'],$row['longitude'],$row['latitude'],$row['altitude']);
                array_push($date_location, $location);
            }
            // print_r($date_location);
            foreach ($date_location as $date_result){
                $i = 0;
                if(!isset($date_result[0])){  // the first value of each row of the array is always a date followed by longitude, latitude & altitude
                    $date_location[$i][0] = '';     // avoids null values in an array 
                } else {
                    $search_date = $date_result[0];
                    //print($search_date);

                    //finding max and min of lat and lng of a given point if distance is 6feet
                    $points = getBoundingBox($date_result[2], $date_result[1], (6 / 5280)); //converting 6feet to miles
                    $minLat = $points[0];
                    $maxLat = $points[1];
                    $minLng = $points[2];
                    $maxLng = $points[3];

                    $sql = "SELECT * FROM user_locations WHERE date_time BETWEEN ('$search_date' - INTERVAL $time_range MINUTE) and ('$search_date' + INTERVAL $time_range MINUTE) and u_id !='$u' and latitude BETWEEN $minLat and $maxLat and longitude BETWEEN $minLng and $maxLng"; 
                    // here a query is set to filter the users of same date and time
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            //search if the location is same or not
                            if(isset($result)) {  // && ($date_result[3] == $row['altitude']) altitude not taken
                                $user_id = $row["u_id"];
                                $t = $search_date;
                            }  else {
                                $user_id = NULL;
                            }
                            //echo "$user_id";
                            if( isset($user_id) ){    // if cross check result is positive then it will change the status of that person 
                                $new_status = 3;
                                $n_affected = 1;
                                $by = 'May have a Close Contact with an affected person at ' . $t;
                                // date_default_timezone_set("Asia/Dhaka");
                                // $today = date("h:i:a  |  d-m-Y"); 
                                $sql = "UPDATE user_status SET u_status='$new_status', updated_by='$by', affected_person='$affected' WHERE u_id='$user_id' and u_status='$n_affected'";
                                mysqli_query($conn, $sql);
                            } else {
                                // otherwise do nothing
                            }
                        }
                    }
                    $search_date = null;
                }
                $i++;
            }
        }
  } else {
        // this block will get executed if the person's status is not changed to affected
  }

  
  //algorithmm to find the bounding box of a point

function getBoundingBox($lat_degrees,$lon_degrees,$distance_in_miles) {

    $radius = 3963.1; // of earth in miles

    // bearings - FIX   
    $due_north = deg2rad(0);
    $due_south = deg2rad(180);
    $due_east = deg2rad(90);
    $due_west = deg2rad(270);

    // convert latitude and longitude into radians 
    $lat_r = deg2rad($lat_degrees);
    $lon_r = deg2rad($lon_degrees);

    // find the northmost, southmost, eastmost and westmost corners $distance_in_miles away

    $northmost  = asin(sin($lat_r) * cos($distance_in_miles/$radius) + cos($lat_r) * sin ($distance_in_miles/$radius) * cos($due_north));
    $southmost  = asin(sin($lat_r) * cos($distance_in_miles/$radius) + cos($lat_r) * sin ($distance_in_miles/$radius) * cos($due_south));

    $eastmost = $lon_r + atan2(sin($due_east)*sin($distance_in_miles/$radius)*cos($lat_r),cos($distance_in_miles/$radius)-sin($lat_r)*sin($lat_r));
    $westmost = $lon_r + atan2(sin($due_west)*sin($distance_in_miles/$radius)*cos($lat_r),cos($distance_in_miles/$radius)-sin($lat_r)*sin($lat_r));


    $northmost = rad2deg($northmost);
    $southmost = rad2deg($southmost);
    $eastmost = rad2deg($eastmost);
    $westmost = rad2deg($westmost);

    // sort the lat and long so that we can use them for a between query        
    if ($northmost > $southmost) { 
        $lat1 = $southmost;
        $lat2 = $northmost;

    } else {
        $lat1 = $northmost;
        $lat2 = $southmost;
    }


    if ($eastmost > $westmost) { 
        $lon1 = $westmost;
        $lon2 = $eastmost;

    } else {
        $lon1 = $eastmost;
        $lon2 = $westmost;
    }

    return array($lat1,$lat2,$lon1,$lon2);
}

?>