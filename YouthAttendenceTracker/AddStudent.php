<?php
	session_start();

	// Turn off all error reporting
	error_reporting(0);

	/********************************************************
	 * AddStudent.php
	 *
	 * Description:  This script inserts a student into the
	 * 	servicelist table and also checks that a duplicate
	 * 	was not inserted.  Handles students and visitors.
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

	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation

	// get the attendee id by splitting pickStudent by |, first element will be id
	$student_data = explode('|', $_POST['pickStudent']);

	// constant for checking mysql duplicate insert error, the code to check
	define('MYSQL_DUPLICATE_ERROR_CODE', 1062);

	// Query the servicelist table and add the student
	$query = "INSERT INTO servicelist (ServiceID, AttendeeID) VALUES ('" . $_SESSION['service_id'] . "', '" . $student_data[0]  . "');";

	// query and return
	if ( $db->query($query) ) {
		// check the attendee id value sent in POST to check if this student is being added as a visitor.
		// If aId is 0, not a visitor, else add them to visitor table (aId is the attendee that added if not 0)
		if ( isset($_POST['aId']) && $_POST['aId'] != 0 ) {
			$query = "INSERT INTO visitor (ServiceID, AttendeeID, VisitorID) VALUES ('" . $_SESSION['service_id'] . "', '" . $_POST['aId'] . "', '" . $student_data[0] . "');";
			if ( !$db->query($query) )  {
				// failed to add the visitor so roll back the insert into servicelist and add back to temp_remove
				$query = "DELETE FROM servicelist WHERE ServiceID = '" . $_SESSION['service_id'] . "' AND AttendeeID = '" . $student_data[0] . "';";
				if ( !$db->query($query) )
					echo "error|Critical Error with Adding Student and Visitor...please check Database integrity.";

				$db->close();
				exit();
			}
		}

		// get the student's PictureURL to send back
		$query = "SELECT PictureURL FROM attendee WHERE ID=" . $student_data[0] . ";";
		if ( $result = $db->query($query) ) {
			if ( $obj = $result->fetch_object() ) {
				$picture_url = $obj->PictureURL;
			}
		}
		if ( !$picture_url )
			$picture_url = "NULL";

		/******* CHECK IF STUDENT IS INACTIVE AND SET TO ACTIVE *********************/
		// If a student was inactive but added to a service then they need to be set to active.
		$query = 'UPDATE attendee SET active="yes" WHERE ID=' . $student_data[0] . ';';
		$db->query($query);  // no real check needed 

		printf('student|%s|add|%s', $_POST['pickStudent'], $picture_url);  // send back student data with ret[0] of student
		$db->close();
		exit();
	} else if ( $db->errno == MYSQL_DUPLICATE_ERROR_CODE ) { // catch if a duplicate and return appropriate erro
		echo "error|You cannot add the student twice." . $db->error . "-----" . $_SESSION['service_id'];
		$db->close();
		exit();
	} else { // error adding student to team so return error
		printf('error|%s', 'Could not add student.  Please try again.');
		$db->close();
		exit();
	}

?>
