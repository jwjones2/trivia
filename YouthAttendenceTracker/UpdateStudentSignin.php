<?php
	/********************************************************
	 * UpdateStudentSignin.php
	 *
	 * Description:  Takes values--AttendeeID, field, and value--and
	 * 	updates the value for the attendee in temp_signin
	 * 	table.
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
		
	// start a session to get the session variables
	session_start();
	
	// get service id from session
	$service_id = $_SESSION['service_id'];
	
	// variables from GET for simplicity and readability
	$attendee_id = $_GET['id'];
	$field = $_GET['field'];
	$value = $_GET['value'];
	
	// query the database
	$query = "UPDATE servicelist SET $field='$value' WHERE ServiceID='$service_id' AND AttendeeID=$attendee_id;";

	if ( $db->query($query) ) 
		echo "update|true";
	else 
		echo "update|Couldn't update the student.  Try reloading the page.";

	
	$db->close();
	exit();
	
?>