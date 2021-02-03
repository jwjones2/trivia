<?php
	// start session for service id
	session_start();

	// Turn off all error reporting
	error_reporting(0);
	
	/********************************************************
	 * RemoveVisitor.php
	 *
	 * Description:  Deletes the record in servicelist and
	 * 	visitor tables.
	 ********************************************************/
	
	 /********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and send if not
	if ( $db->connect_error ) {
		echo 'error|There was a problem connecting to the database.';
		exit();  // cannot continue since no connection established
	}
	
	// build query and execute to remove from servicelist
	// need to select from servicelist first so that a rollback can be performed if necessary
	$query = "SELECT * FROM servicelist WHERE AttendeeID=" . $_GET['vId'] . ";";
	if ( $results = $db->query($query) ) {
		if ( !$object = $results->fetch_array() ) {
			// echo error if failed and exit script
			echo "error|There was a problem with the database.  Please try again.";
			exit();
		}
	}
	$query = "DELETE FROM servicelist WHERE AttendeeID=" . $_GET['vId'];
	
	if ( $db->query($query) ) {
		// successful so remove from visitor table.  **If cannot remove from visitor
		// then rollback by inserting back into servicelist and alerting user.
		$query = 'DELETE FROM visitor WHERE AttendeeID=' . $_GET['aId'] . ' AND VisitorID=' . $_GET['vId'] . ';';
		if ( $db->query($query) ) {
			echo "success|";	
		} else {
			// rollback delete from servicelist
			$query = 'INSERT INTO servicelist (ServiceID, AttendeeID, SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors, extrapoints) VALUES (';
			$query .= $object[1] . ', ' . $object[2] . ', "' . $object[3] . '", "' . $object[4] . '", "' . $object[5] . '", "' . $object[6] . '", ' . $object[7] . ', "' . $object[8] . '");';
			if ( $db->query($query) )
				echo "error|Had to rollback the deletion.  Please try again.  There was a problem with the database.";
			else
				echo "error|The deletion failed and could not rollback!  Please contact the program administrator.";
		}
	} else { echo "error|There was a problem removing the student.  Please try again."; }
	
	$db->close;
?>
