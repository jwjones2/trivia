<?php
	/*******************************************************************
	 * ToggleActivate.php
	 * 
	 * Description:  Takes an ID and a flag from GET and sets the attendee
	 * 		referenced by ID according to the flag: active or inactive.
	 * 		Returns result or error.
	 *******************************************************************/
	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );	
	
	// Check that connection was successful and set $error if not
	if ( $db->connect_error ) {
		echo "error|There was a problem connecting to the database.";
	}
	
	// Query the db and set active on attendee at id
	// then return result
	// **Build query based on flag and set message also
	if ( $_GET['flag'] == "inactive" )
		$query = 'UPDATE attendee SET active="no" WHERE ID=' . $_GET['id'] . ';';
	else 
		$query = 'UPDATE attendee SET active="yes" WHERE ID=' . $_GET['id'] . ';'; 
		
	if ( !$db->query($query) ) {  // if error, print and close db and exit
		echo "error|Error accessing the Database.  Please try again.";
		$db->close();
		exit();         // exit to prevent further code execution
	}
	
	// success so return flag and message 
	echo "toggleactive|Changed!";
	$db->close();        // close the db
	exit();
?>
