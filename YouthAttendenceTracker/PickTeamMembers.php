<?php
	/********************************************************
	 * PickTeamMembers.php
	 *
	 * Description:  
	 ********************************************************/

	 
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
	
	/** FLAG FOR SUBMISSION CHECK AND FORM VALIDATION BY PHP **/
	$failed_submit = false;
	
	function checkForFail ( $name )
	{
		if ( isset($failed_submit) && $failed_submit )
                {
                	printf(' value="%s" ', $_POST[$name]);  // put the name submitted back in form 
                	if ( isset($blank_name) )  // if blank_name is set then highlight the form field
                		echo ' style="background-color: yellow;" ';
                }
        }
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
        <title>Pick Team Members</title>
        
        <!--favicon icon-->
        <link rel="shortcut icon" href="./favicon.ico" />
   
        <!-- Metadata Section:  Description of Page and Keywords for Site. -->
        <meta name="description" content="" />
        <meta name="keywords" content="" />
   
        <!-- Link to Stylesheet -->
        <link rel="stylesheet" href="./main.css" type="text/css" />
        
        <!-- Link to Javascript code for page -->
        <script type="text/javascript" language="Javascript" src="./main.js"> </script>
        
        <script type="text/javascript" language="Javascript">

        </script>
        
        <style type="text/css">
        	
        </style>
        
    </head>
    
    <body class="formBody">
    	<?php
    		/* OUTPUT THE SITE'S MENU */
    		write_menu();
    	?>
    
    	<?php
    		/***************************************************************
    		 * Main PHP Functionality
    		 * 
    		 * 1. Query the db with the ID submitted to the page.
    		 *      a. Get the new team's info
    		 *	b. Query the teamlist table for any team members on the team.
    		 * 2. Write the appropriate information to the form and get input for
    		 *    new team members.
    		 * 3. Upon submitting new team members, call team list page.
    		 *
    		 **************************************************************/
    		 // error variable to handle errors and display to user
    		 $errors = "";
    		 
    		 // check that ID isset and is valid
    		 if ( !isset($_GET['ID']) || $_GET['ID'] == "" )
    		 	 $errors .= 'Could not retrieve team from db.  Please try again later.';
    		 
    		 /*  (1) Query the db with ID submitted and get info on team and 
    		 	 current team members.
    		  */
    		 // Query team db and get team info
    		 $team = array();  // array to hold team info
    		 $query = 'SELECT * FROM team WHERE ID=' . $_GET['ID'] . ';';  // build the query string
    		 $result = $db->query($query);  // query the db
    		 if ( $result ) // test result object
    		 {
    		 	 if ( $obj = $result->fetch_object() )   // use fetch_object to get result as an object to be able to reference by column name
    		 	 {
    		 	 	 $team['Name'] = $obj->Name;     
    		 	 	 $team['Notes'] = $obj->Notes;
    		 	 }
    		 }
    		 
    		 // arrays to hold the team member results
    		 $member_ids = array();
    		 $member_names = array();
    		 
    		 $query = 'SELECT AttendeeID FROM teamlist WHERE TeamID=' . $_GET['ID'] . ';';  // build the query string
   
    		 if ( $result = $db->query($query ) ) // query the db and test result object
    		 {
    		 	 // loop while there are still rows to process
    		 	 while ( $obj = $result->fetch_object() )   // use fetch_object to get result as an object to be able to reference by column name
    		 	 {
    		 	 	 $member_ids[] = $obj->AttendeeID; 
    		 	 }
    		 }
    		 /*  Now process the IDs to get the member names and assign to member_names array */
    		 // loop over member_ids and query attendee db using id to get member name
    		 foreach ( $member_ids as $mid ) 
    		 {
    		 	// query the db
    		 	$query = 'SELECT FirstName, LastName, NickName FROM attendee WHERE ID=' . $mid . ';'; // build the query
    		 	
    		 	if ( $result = $db->query($query) )
    		 	{
    		 		if ( $obj = $result->fetch_object() ) 
    		 		{
    		 			if ( $obj->NickName != 'NULL' ) 
    		 				$member_names[] = $obj->FirstName . " " . $obj->LastName . " (" . $obj->NickName . ")";
    		 			else
    		 				$member_names[] = $obj->FirstName . " " . $obj->LastName;
    		 		}
    		 	}
    		 
    		 }
    		 
    		 
    		 
    		 // Check that form was submitted and add any newly selected members to teamlist table
    		 if ( isset($_POST['_submit_check']) )
    		 {
    		 	/*******************************************************
    		 	 * 1. Get the members from the form select box.
    		 	 * 2. Loop over selected members and add each to the 
    		 	 *    teamlist table.
    		 	 * 3. Upon completion send to team's profile page.
    		 	 ******************************************************/
    		 	// loop over selected members stored in members array of POST
    		 	// use variables to keep track of how many members added or 
    		 	// failed to add.
    		 	$members_added = 0;
    		 	$members_failed = 0;
    		 	foreach ( $_POST['members'] as $member_id ) 
    		 	{
    		 		// build query and run against table
    		 		$query = 'INSERT INTO teamlist (TeamID, AttendeeID) VALUES (' . $_POST['TeamID'] . ', ' . $member_id . ');';
    		 		
    		 		// query and check results
    		 		if ( $db->query($query) )
    		 			$members_added++;  // increment count success
    		 		else
    		 			$members_failed++; // increment count of failed
    		 	}
    		 	
    		 	/*** DONE ADDING MEMBERS SO REDIRECT ***/
        		/* Redirect browser */
        		header("Location: ./TeamList.php");
        		/* Make sure that code below does not get executed when we redirect. */
        		exit;
    		 }	
    	    		 
	?>  	
        
 <!-- ERROR section -->
 	<div id="showErrors" class="showErrors">

 	</div>
 
 <!-- Form section -->
        <div id="formSection" class="profileFormSection">
            <!-- top of form -->
            <span class="profileFormTop center"><span class="moveDown"><?php printf('Team:  %s', $team['Name']); ?><br /><span class="labelBig">Pick Team Members</span></span></span>
        			
            <!-- Bottom of form **Placed at top so that form contents will overlap it placing its content behind form body content -->
            <span class="profileFormBottom"></span>
           
            
            
			<form id="teamForm" name="teamForm" class="teamForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return validateTeamForm()">
				<table cellpadding="3px">
                    <!-- Row 1 -->
                    			<tr>
                    				<td class="label center" colspan="3">Current Members:  </td>
                    			</tr>
                    			
                    			<tr>
                    				<?php
                    					// counter for checking where to write to a different row
                    					$counter = 0;
 
                    					// loop over member_names array and write to form
                    					foreach ( $member_names as $names ) 
                    					{
                    						if ( $counter % 3 == 0 ) // use modulo of counter to figure when to start a new row
                    							echo '</tr><tr>';
                    						
                    						// print the names
                    						printf('<td colspan="3">%s</td>', $names);
                    						
                    						$counter++;  // increment the counter
                    					}
                    				?>
                    			</tr>
                    				
                    <!-- Row 2 -->
                    			<tr>
                    				<td colspan="3" class="label center">Available Students:</td>
                    			</tr>
                    			
                    <!-- Row 3 -->
                    			<tr>
                    				<td colspan="3" class="center">	
                    					<!-- MULTIPLE select box for selecting new members -->
                    					<select name="members[]" multiple="multiple">
                    				<?php
                    					/*  
                    					    Query the attendee table getting all active students ordered by last name
                    					    and then determine which of these students are not currently on another team
                    					    by querying the teamlist db searching by active teams' ids.
                    					 */
                    					$counter = 0;  // to use to know when to start a new row
                    					
                    					$active_students = array();  // holds all active students and their information in an associative array
                    					$team_members = array();     // holds the IDs of attendees already on a team
                    					
                    					/***
                    					 * First, Query the db to get all active attendees and store in active_students array.
                    					 ***/
                    					$query = 'SELECT ID, FirstName, LastName, NickName, MiddleName FROM attendee WHERE Active="yes" ORDER BY LastName;';  // build query string
                    					           					
                    					// check result and then loop, getting object from result with fetch_object, printing the names
                    					if ( $result = $db->query($query) )
                    					{
                    						while ( $obj = $result->fetch_object() ) 
                    						{                    		
                    							// use ID as the index and create another array for the value holding
                    							// the attendee data
                    							$active_students[$obj->ID] = array($obj->FirstName, $obj->LastName, $obj->NickName, $obj->MiddleInitial);
                    						}
                    					}
                    					
                    					/***
                    					 * Query the db activeteam and get the IDs of active teams to then use to query the
                    					 * teamlist db and get all attendee IDs to add to the team_members array.
                    					 ***/
                    					$query = 'SELECT TeamID FROM activeteam;';
                    					
                    					if ( $result = $db->query($query) ) 
                    					{
                    						while ( $obj = $result->fetch_object() ) 
                    						{
                    							// Use TeamID to query the teamlist table and add team members to team_members array
                    							$q = 'SELECT AttendeeID FROM teamlist WHERE TeamID = ' . $obj->TeamID . ';';
                    							
                    							if ( $r = $db->query($q) ) 
                    							{
                    								while ( $o = $r->fetch_object() ) 
                    								{
                    									// Push the Attendee ID onto team_members array
                    									$team_members[] = $o->AttendeeID;
                    								}
                    							}
                    						}
                    					}
                    					
                    					/***
                    					 * Compare the arrays active_students and team_members, removing from active_students
                    					 * any that have their IDs listed in team_members since they are currently already on
                    					 * a team.
                    					 ***/
                    					// loop over team_members array
                    					foreach ( $team_members as $memberID ) 
                    					{
                    						// remove members whose IDs are held in $memberID from the active_students array
                    						if ( isset($active_students[$memberID]) )
                    							unset($active_students[$memberID]);
                    					}
                    					
                    					/***
                    					 * active_students now holds all active attendees that are not on an active team, so loop
                    					 * over and use associative array to get the data and output to the page.
                    					 ***/
                    					foreach ( array_keys($active_students) as $attendeeID )  // use array_keys so that attendeeID holds the ID which is the index of active_students 
                    					{
                    						// check if there is a nick name
                    						if ( $active_students[$attendeeID][2] != "" && $active_students[$attendeeID][2] != "NULL" ) 
                    							printf('<option value="%d">%s %s (%s)</option>', $attendeeID, $active_students[$attendeeID][0], $active_students[$attendeeID][1], $active_students[$attendeeID][2]);
                    						else
                    							printf('<option value="%d">%s %s</option>', $attendeeID, $active_students[$attendeeID][0], $active_students[$attendeeID][1]);
                    								
                    						$counter++;  // increment the counter
                    					}
                    				?>
                    					</select>
                    				</td>
                    			</tr>
                    			
                    <!-- Row 4 -->
                    			<tr>
                    				<td class="center"><a href="./Home.html"><input type="button" value="Cancel" /></td>
                    				<td class="center"><input type="submit" value="Create Team" id="submit" name="submit" /></td>
                    			</tr>
                    			
                    		</table>
                    		
                    		<input type="hidden" name="TeamID" value="<?php echo $_GET['ID']; ?>" />
                    		<input type="hidden" name="_submit_check" value="1" /> 
                    	</form>
                    	
                    <div class="notes"><?php printf('Team Notes: %s', $team['Notes']); ?></div>
                    
                    
            </div>
            
        <!-- HOME LOGO SECTION -->
        <div class="homeLogoButton"><a href="./home.php"><img src="./site_images/Homelogo.gif" /></a></div>
            
	</body>
<html>
