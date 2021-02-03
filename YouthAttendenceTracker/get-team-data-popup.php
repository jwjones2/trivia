<?php
    
	/********************************************************
	 * test-output.php
	 *
	 * Description:  Gets an ID from $_POST and uses Attendee
	 *	and Student (and Sponsor) classes to get the values
	 *	of a row in the correspondind tables, outputting
	 *	the values.
	 ********************************************************/
	// Turn off all error reporting
	//error_reporting(0);

	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );
        
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
        require_once('Misc.php');
        
        $team_id = $_GET['team_id'];
        // query db for team data
	$query = "SELECT * FROM team WHERE ID=$team_id;";
	if ( $result = $db->query($query) ) {
		if ( $object = $result->fetch_object() ) {
			$team_name = $object->Name;
			$team_start = $object->Start;
			$team_end = $object->End;
			$team_notes = $object->Notes;
			
			// check end, it could be '0000-00-00', if so set to blank
			if ( $team_end == '0000-00-00' )
				$team_end = '';
		}
        }
            
	printf('%s|', $team_name);
			// get the team members from teamlist and attendee
			$query = "SELECT t2.FirstName, t2.LastName, t2.ID, t2.PictureURL, t2.DOB, t2.Sex FROM teamlist as t1 INNER JOIN attendee as t2 ON t1.AttendeeID = t2.ID WHERE t1.TeamID = $team_id ORDER BY t2.LastName;";
			if ( $results = $db->query($query) ) {
				while ( $object = $results->fetch_object() ) {
					printf('<span id="attendee_%d" colspan="2"><img src="', $object->ID); //start the td for this member and the picture
					// check if picture is null and print alternative if so 
					if ( $object->PictureURL == 'NULL' ) { // ->PictureURL
						if ( $object->Sex == 'female' ) // ->Sex 
							echo './profile_pictures/Girl.gif" ';
						else
							echo './profile_pictures/Boy.gif" ';
					} else {
						printf('./%s"  ', $object->PictureURL);       // Put the picture at start of record ... ->PictureURL
					}
					echo 'id="picture" width="60px" height="60px" />';  // finish the image row
							
					printf('<span class="large-text">%s %s</span><br />', $object->FirstName, $object->LastName);                 // Print the full name:  FirstName LastName  ... ->FirstName ->Lastname
				} 
			} 
	
                
                $db->close();
                exit();

?>
