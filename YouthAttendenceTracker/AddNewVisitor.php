<?php
	session_start();

	// Turn off all error reporting
	error_reporting(0);

	/********************************************************
	 * AddNewVisitr.php
	 *
	 * Description:  Adds a new Visitor to the database and
	 * 	adds into servicelist and possibly as visitor.
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

	include_once('Misc.php');
	// get values from post and parse for inserting
	list($first, $last) = parse_names_from_form($_POST['name']);
	$dob = date_form_to_mysql($_POST['dob']);
	$sex = $_POST['sex'];

	$query = "INSERT INTO attendee (FirstName, LastName, DOB, Sex) VALUES ('$first', '$last', '$dob', '$sex');";
	if ( $db->query($query) ) {
		// return the attendee id and first and last name
		echo "true_" . $db->insert_id . "|$first|$last";
	} else {
		echo "fail_";
	}

	$db->close();
	exit();
?>
