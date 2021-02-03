<?php
	/*******************************************************
	 * CustomPointsList.php
	 *
	 * Description:  Lists the points for students for a custom
	 *		time period given in POST.
	 *******************************************************/

	// output the header
	$title = "Student Points";
	require_once('Header.php');

	require_once('Misc.php');
?>

		<div id="points-listing">
<?php
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
	 * 3. Filter services by given month and date parameters.
	 * 4. Print array of students and points.
	 ***/

	// variables for script
	$error = "";                  // to hold any errors for printing
	$students = array();          // to hold student ids
	$points = array();            // to hold the student's points
	$month = date('n');           // to hold the current month
	$year = date('Y');            // to hold the current year
	$points = array();            // to hold students and points
	$attendence_count = array();  // to count the number of times the student was here

	// require GetPoints for getPoints function
	require_once('GetPoints.php');

	// (1) Query students table
	$query = 'SELECT ID, FirstName, LastName FROM attendee WHERE active="yes" ORDER BY LastName';
	if ( $result = $db->query($query) ) {
		while ( $obj = $result->fetch_object() ) {
			$students[$obj->ID] = "$obj->FirstName $obj->LastName";
		}
	} else {
		$error = "Could not get the student ids...";
	}


	// (2) Iterate and query service table filtered by given dates
	$start = date('Y-m-01'); # default first day of month
	$end = date('Y-m-d');  # default current date
	if ( isset($_GET['start']) && $_GET['start'] != "" )
		$start = $_GET['start'];

	if ( isset($_GET['end']) && $_GET['end'] != "" )
		$end = $_GET['end'];


	$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

	printf('<h2>Custom Points List: Starting: %s  - Ending: %s</h2>', date_human_readable($start), date_human_readable($end) );

	// build query
	$query = "SELECT ID FROM service WHERE Date BETWEEN '$start' AND '$end';";
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
					// also, make sure attendence count for the student is set
					if ( !isset($points[$obj2->AttendeeID]) ) {
						$points[$obj2->AttendeeID] = 0;
						$attendence_count[$obj2->AttendeeID] = 0;
					}

					$points[$obj2->AttendeeID] += getPoints($obj2);
					$attendence_count[$obj2->AttendeeID]++;
				}
			} else
				$error = "Couldn't get the students points from servicelist.";
		}
	} else
		$error = "Couldn't get the service from database.";

	// now loop over points and sort and print
	/*
	$student_points = array();  // hold the points to sort
	foreach ( $students as $s ) {
		// dont print student points with 0
		if ( $points[$s[0]] != 0 )
			$student_points[$s[1] . " " . $s[2]] = $points[$s[0]];
	}
	*/
?>
			<table>
<?php
	arsort($points);
	foreach ( $points as $key => $val )
		printf('<tr><td>%s (%d)</td><td>%s</td></tr>', $students[$key], $attendence_count[$key], number_format($val) );


	// Print error if found
	if ( $error != "" )
		printf('<h2 style="color: Red;">%s</h2>', $error);

	$db->close();
?>
			</table>
		</div>
	</body>
</html>
