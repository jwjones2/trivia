<?php
	/********************************************************
	 * Submit.php
	 *
	 * Description:  Pulls all the records from temp_signin
	 * 		that match the service_id in SESSION and adds
	 * 		them to servicelist.
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
	
	// start session for service id
	session_start();
	
	// use an insert into select function to do the moving from temp_signin to servicelist
	// ** It will also perform an update on any changed values if submitted again for the same service id
	$query = "INSERT INTO servicelist (ServiceID, AttendeeID, SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors, extrapoints)
			SELECT ServiceID, AttendeeID, SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors, extrapoints FROM temp_signin
			WHERE temp_signin.ServiceID = '" . $_SESSION['service_id'] . "' ON DUPLICATE KEY UPDATE servicelist.SunSchAttend=temp_signin.SunSchAttend,
			servicelist.SunMornAttend=temp_signin.SunMornAttend, servicelist.SunEvenAttend=temp_signin.SunEvenAttend,
			servicelist.Bible=temp_signin.Bible, servicelist.Visitors=temp_signin.Visitors, servicelist.extrapoints=temp_signin.extrapoints;";
	
	if ( $db->query($query) ) {
		// need to check temp_remove table.  Use any rows to delete from servicelist
		// first query temp_remove
		$query = "SELECT * FROM temp_remove WHERE ServiceID=" . $_SESSION['service_id'] . ";";
	
		if ( $result = $db->query($query) ) {
			while ( $object = $result->fetch_object() ) {
				$db->query("DELETE FROM servicelist WHERE AttendeeID=" . $object->AttendeeID . " AND ServiceID=" . $_SESSION['service_id'] . ";");
				// now remove the student from temp_remove
				$db->query("DELETE FROM temp_remove WHERE ID=" . $object->ID . ";");
			}
		} else {
			echo "error|THERE WAS AN ERROR UPDATING THE SERVICE...PLEASE CHECK WITH THE SITE ADMINISTRATOR.";
			$db->close();
			exit();
		}
	
		echo "submit|Service successfully submitted.";
	} else {
		echo "submit|Could not submit the service.  Please try again.";
	}
		
		
	/** Also update the service to reflect that it was submitted **/
	$query = "UPDATE service SET submitted='yes' WHERE ID=" . $_SESSION['service_id'] . ";";
	if ( !$db->query($query) ) {
		// log the update error
		$log = "There was an error setting the service with ID " . $_SESSION['service_id'] . " value of submitted to yes: error code:  " . $db->errno . "; error description:  " . $db->error . ".";
		$query = "INSERT INTO errorlog (log) VALUES ('$log');";
		$db->query($query);
	}
	$db->close();
?>