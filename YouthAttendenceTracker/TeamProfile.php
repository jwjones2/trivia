<?php
	/********************************************************
	 * TeamProfile.php
	 *
	 * Description:  The profile of a team in the Attendence
	 *	Tracker db.  Name, Start Date, Notes, and list of
	 *	team members.  Controls to remove member or pick
	 *	more members and to make the team no longer active.
	 ********************************************************/

	 
	// output the header
	$title = "Team Points";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	/* OUTPUT ANY ERRORS */
    	if ( $error != "" )
    		printf('<div style="color: red; font-size: 22px;">%s</div>', $error);

    		/***************************************************************
    		 * php functionality
    		 *
    		 * 1. First, Check that the _submit_check field isset and if so delete
    		 *    the member from the TeamList table that is specified by the
    		 *    memberToRemove field submitted.
    		 * 2. Query the db and get the team info for the GET ID that was
    		 *    input.
    		 * 3. Display the team info.
    		 * 4. Page is self-submitting for removing team members and making
    		 *    the team inactive.
    		 **************************************************************/
    		/*  
    		    (1) Check for _submit_check; if isset then a member is to be deleted
    		    from the team
    		 */
    		if ( isset($_POST['_submit_check']) )
    		{
    			// build the DELETE query
    			$query = 'DELETE FROM teamlist WHERE AttendeeID=' . $_POST['memberToRemove'] . ';';
    			
    			// run the query
    			$db->query($query);
    			   			
    			// set the GET variable for team ID
    			$_GET['TeamID'] = $_POST['TeamID'];
    		}
    			
    	
    		/*  (2) Query the Attendence Tracker DB to get team info  */
    		if ( !isset($_GET['TeamID']) ) 
    			echo 'Error:  Cannot find the team id... <a href="./TeamList.php">Go Back to Teams List</a><br />';
		else
			$team_id = $_GET['TeamID'];
    		
    		// array to hold team data
    		$team = array();
    		
    		$query = 'SELECT * FROM team WHERE ID=' . $_GET['TeamID'] . ';';  // build the query string
    		
    		// query the db and test result
    		if ( $result = $db->query($query) ) 
    		{
    			while ( $obj = $result->fetch_object() ) 
    			{
    				// get team data and store in team array
    				$team['Name'] = $obj->Name;
    				$team['Start'] = $obj->Start;
    				$team['ID'] = $obj->ID;
    				$team['Notes'] = $obj->Notes;
    				$team['End'] = $obj->End;
    			}
    		}
    		
    	?>
    	
    	<div class="teamTitleSection">
    		<span class="teamTitle">Team "<?php echo $team['Name']; ?>"</span> <br />
    		<?php echo $team['Start']; ?>
    			<br />
		<span style="background-color: yellow; font-family: Sans-Serif;">
			<?php
				require_once('DBMiscClasses.php');
		
				$tPoints = new TeamPoints($db, $team['ID']);
				printf('Team points:  %s<br /><br />', number_format( $tPoints->get_points() ) );
			?>
		</span>
    	</div>
    	
    	<div class="teamMemberList">
    		<?php
    			/*  Query the teamlist and attendee tables and get the members for the team */
    			// arrays to hold the team member results
    			$member_ids = array();
    			$member_names = array();
    			
    			$query = 'SELECT attendee.ID, PictureURL, FirstName, LastName, DOB, Sex FROM attendee, teamlist WHERE TeamID=' . $_GET['TeamID'] . ' AND AttendeeID = attendee.ID ORDER BY LastName;';
   
    			if ( $result = $db->query($query ) ) // query the db and test result object
    			{
    				// loop while there are still rows to process
    				while ( $obj = $result->fetch_object() )   // use fetch_object to get result as an object to be able to reference by column name
    				{
    					$member_ids[] = array($obj->ID, $obj->PictureURL, $obj->FirstName, $obj->LastName, $obj->DOB, $obj->Sex); 
    				}
    			}
    	
    			/*  Now process the IDs to get the member names and assign to member_names array */
    			
    			echo '<table class="teamProfileTable">';  // start a table to hold members
    			
    			// counter for styling every other row
    	  		$counter = 0;
    			
    	  		// loop over member_ids getting the student data
    			foreach ( $member_ids as $data ) 
    			{
	  			if ( $counter % 2 == 0 ) // even row
    	  				echo '<tr class="even" id="attendee_' . $data[0] . '>'; 			              
    	  			else
    	  				echo '<tr id="attendee_' . $data[0] . '>';
    	  			// check if picture is null and print alternative if so 
    	  			if ( $data[1] == 'NULL' )  // ->PictureURL
    	  			{
    	  				if ( $data[5] == 'female' ) // ->Sex 
    	  					echo '<td><img src="./profile_pictures/Girl.gif" id="picture" /></td>';
    	  				else
    	  					echo '<td><img src="./profile_pictures/Boy.gif" id="picture" /></td>';
    	  			}
    	  			else
    	  				printf('<td><img src="./%s" id="picture" /></td>', $data->PictureURL);       // Put the picture at start of record ... ->PictureURL
    	  				printf('<td>%s %s</td>', $data[2], $data[3]);                 // Print the full name:  FirstName LastName  ... ->FirstName ->Lastname
    	  				printf('<td>%s</td>', $data[4]);				             // Print the DOB
    	 				printf('<form name="ViewProfile" method="POST" action="./ViewProfile.php"><input type="hidden" name="ID" value="%s" />', $data[0]); // set up form to submit the ID to ViewProfile when button is clicked ... ->ID
    	  				echo '<td><input name="submit" type="submit" value="View Profile" /></td></form>';  // print the view profile button
    	  				printf('<td><input type="button" class="deleteButton" value="x" onclick="removeTeamMember(%d, %d);" /></td>', $_GET['TeamID'], $data[0]);   							     
    	  				
    	  			$counter++;  // increment the counter    		 		
    		 	}
    		 	echo '</table>';  // end the table
    		?>
    		
    		<div class="teamProfilePickMembers">
			<a href="./PickTeamMembers.php?ID=<?php echo $team['ID']; ?>"><input type="button" value="Add Team Members" /></a>
		</div>	
    	</div>   

		
<?php include_once('Footer.php'); ?>
    	
