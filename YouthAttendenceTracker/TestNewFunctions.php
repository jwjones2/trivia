<html>
<head><title>Test new function</title>

</head>
<body>
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
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	
	// test StudentPoints
	require_once('DBMiscClasses.php');
	
	$team = new TeamPoints($db, 6);
	
	printf('Team Crouch Potatoes points:  %s<br /><br />', $team->get_points());
	
	$team = new TeamPoints($db, 8);
	
	printf('Team WORD points:  %s', $team->get_points());
	
	

?>
</body>
</html>
