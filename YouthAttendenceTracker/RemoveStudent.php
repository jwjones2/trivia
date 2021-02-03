<?php
	// start session for service id
	session_start();

	// Turn off all error reporting
	error_reporting(0);
	
	/********************************************************
	 * RemoveStudent.php
	 *
	 * Description:  Deletes the record in the servicelist
	 * 	table.  First checks for visitors and alerts to
	 * 	delete any visitors first.
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
	
	// first, check to see if student to be removed had any visitors, if so, return an alert telling the user
	// to first delete any visitors associated with the student
	$query = "SELECT Visitors FROM servicelist WHERE AttendeeID = " . $_GET['id'] . ";";
	if ( $result = $db->query($query) ) {
		if ( $object = $result->fetch_object() ) {
			if ( $object->Visitors > 0 ) {
				// cannot remove since there are visitors for the student
				$query = "UPDATE servicelist SET Visitors = Visitors+1 WHERE AttendeeID = " . $_GET['aId'] . ";";
				$err = "You cannot delete a student who has visitors.  Please remove their visitors first.";
				if ( !$db->query($query) )
					$err += "  -----  THE UPDATE TO DATABASE FIXING VISITOR COUNT FOR ATTENDEE FAILED...PLEASE CONTACT ADMINISTRATOR!";
				
				echo "removestudent|removeerror|$err";
				$db->close();
				exit();
			} 
		} else {
			echo "removestudent|error|Error deleting the student.";
			$db->close();
			exit();
		}
	} else {
		echo "removestudent|error|Error deleting the student.";
		$db->close();
		exit();
	}
	
	// build query and execute
	$query = "DELETE FROM servicelist WHERE AttendeeID=" . $_GET['id'];
	
	if ( $db->query($query) ) {
		// check to see if student was a visitor, delete from visitor table if match
		$query = "DELETE FROM visitor WHERE VisitorID=" . $_GET['id'] . " AND ServiceID=" . $_SESSION['service_id'] . ";";
		if ( !$db->query($query) ) {
			echo "removestudent|error|Error deleting the student from the visitor table.";
			$db->close();
			exit();
		}
		
		echo "removestudent|true";
	} else {
		echo "removestudent|false";
	}
	
	$db->close;
?>
