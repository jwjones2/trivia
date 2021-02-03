<html>
	<head>
		<title>Individual Student Points</title>
		
		<style type="text/css">
			.studentPoints {
				font-weight: bold;
			}
		</style>
	</head>
	<body>
<?php
	/*******************************************************************
	 * IndividualPointsList
	 * 
	 * Description:  Simply Lists all of the points for the current month
	 * 		for each active student.
	 *******************************************************************/
	
	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and set $error if not
	if ( $db->connect_error ) {
		$error = "There was a problem connecting to the database.";
	}
	
	/***
	 * Functionality:
	 * 
	 * 1. Query db for all active students ordered by lastname
	 * 2. Iterate and query services table for students attendence record.
	 * 3. Filter services by current month and add points to running total.
	 * 4. Print array of students and points.
	 ***/
	
	// variables for script
	$error = "";            // to hold any errors for printing
	$students = array();    // to hold student ids
	$points = array();      // to hold the student's points
	$month = date('n');     // to hold the current month
	$year = date('Y');      // to hold the current year
	$points = array();      // to hold students and points
	
	// require GetPoints for getPoints function
	require_once('GetPoints.php');
	
	// (1) Query students table
	$query = 'SELECT ID, FirstName, LastName FROM attendee WHERE active="yes" ORDER BY LastName';
	if ( $result = $db->query($query) ) {
		while ( $obj = $result->fetch_object() ) 
			array_push($students, array("$obj->ID", "$obj->FirstName", "$obj->LastName") );
	} else
		$error = "Could not get the student ids...";
		
		
	// (2) Iterate and query service table filtered by current month
	// get month from GET or set to current
	if ( isset($_GET['month']) && $_GET['month'] != "" )
		$month = $_GET['month'];
	else
		$month = date('m');

	// get year from GET or set to current	
	if ( isset($_GET['year']) && $_GET['year'] != "" )
		$year = $_GET['year'];
	else
		$year = date('Y');
	
	// build query
	$query = "SELECT ID FROM service WHERE MONTH(Date) = $month AND YEAR(Date) = $year;";
	if ( $result = $db->query($query) ) {
		while ( $obj = $result->fetch_object() ) {
			/***
			 * Now, for each service, pull every record in servicelist that matches ServiceID
			 *    and then calculate each student's points and store in points array.
			 ***/
			$query = "SELECT * FROM servicelist WHERE ServiceID=$obj->ID;";
			if ( $res = $db->query($query) ) {
				while ( $obj2 = $res->fetch_object() ) {
					// make sure attendee's id is set in points array or set to 0
					if ( !isset($points[$obj2->AttendeeID]) ) 
						$points[$obj2->AttendeeID] = 0;
						
					$points[$obj2->AttendeeID] += getPoints($obj2);
				}
			} else 
				$error = "Couldn't get the students points from servicelist.";
		}
	} else 
		$error = "Couldn't get the service from database.";
		
	foreach ( $students as $s ) {
		// dont print student points with 0
		if ( $points[$s[0]] != 0 ) 
			printf('<h2 class="studentPoints">For month of %s, %s %s had %s points.</h2>', date('F'), $s[1], $s[2], number_format($points[$s[0]]) );
	}

	// Print error if found
	if ( $error != "" ) 
		printf('<h2 style="color: Red;">%s</h2>', $error); 	
	
	$db->close();
?>
	</body>
</html>
