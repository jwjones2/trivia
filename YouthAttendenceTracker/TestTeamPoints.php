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
	
	// get the team id from get
	if ( isset($_GET['team_id']) && $_GET['team_id'] != '' ) {
		$team_id = $_GET['team_id'];
	} else {
		echo '<h1 style="position: absolute; top: 50px; width: 100%; background-color: White; color: Red; font-size: 25px; z-index: 999;">ERROR:  The Team ID could not be retrieved...</h1>';
		exit();
	}
    include_once('DBMiscClasses.php');
					$team = new TeamPoints($db, $team_id);
					printf('Current Team Points -- %s', number_format($team->get_points()));
?>