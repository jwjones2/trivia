<?php
	session_start();
	error_reporting(0);
	/********************************************************
	 * Signin.php
	 *
	 * Description:  This is a Master page for signins.  Handles
	 * 	individual signins.  As each student is entered, the
	 * 	database is adjusted (temp table) and then if submitted
	 * 	the data is entered in servicelist.
	 *
	 * 	??Can be called with an id to get summary mode??
	 * 	
	 ********************************************************/
 	 
	// output the header
	$title = "Create Team";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	/*** HANDLE FORM SUBMISSION IF APPLICABLE ***/
	if ( isset($_POST['submitted']) ) {
		/***
		 * Process the team's data.
		 *
		 * 1. First create the team record by inserting the name, start, and end dates.
		 * 2. Then, loop over members and insert into teamlist.
		 ***/
		
		// constant for checking mysql duplicate insert error, the code to check
		define('MYSQL_DUPLICATE_ERROR_CODE', 1062);
		
		// error variable to use to display messages to the user
		$error = false;
	
		// first need to change date into mysql format (YYYY-MM-DD)'
		include_once('Misc.php');
		$start = date_form_to_mysql($_POST['startDate']);
		if ( $_POST['endDate'] != '' ) 
			$end = date_form_to_mysql($_POST['endDate']);
		else
			$end = '1000-01-01';
		$query = 'INSERT INTO team (Name, Start, End, Notes) values("' . $_POST['teamName'] . '", "' . $start;
		$query .= '", "' . $end . '", "' . $_POST['notes'] . '");';
		echo "<h1>$query</h1>";
		if ( $db->query($query) ) {
			/***
			 * Team was inserted so now get the students and add to teamlist.
			 ***/
			// user insert id from team insertion and add student id and team id to teamlist table
			$team_id = $db->insert_id;
			foreach ( $_POST['members'] as $member_id ) {
				if ( !$db->query( "INSERT INTO teamlist (TeamID, AttendeeID) VALUES ('$team_id', '$member_id');" ) ) {
					echo '<h1 style="position: absolute; background-color: White; z-index: 993">' . $query . 'ERROR: Could not add the student to the team... Please go to edit team and check the members.';
					exit();	
				}
			}
			
			// success, so now redirect to Team.php instead of going back to CreateTeam.php below
			header ( get_header_location('Team.php?team_id=' . $team_id) );
		} else if ( $db->errno == MYSQL_DUPLICATE_ERROR_CODE ) { // catch if a duplicate and return appropriate erro
			$error = "Another team already has this name.  Either choose a new name or delete the old team with the same name.";
		} else {
			echo "<h1 style=\"position: absolute; background-color: White; z-index: 993\">ERROR: Could not create the team.";
			exit();
		}
	}
?>

	<?php
	// check for error and alert user
	if ( $error ) : ?>
		<script>
			message.show("<?= $error ?>", "", "error", 3000);
		</script>
	<?php endif; ?>

<div class="container team-points">
	<!-- List of team points -->
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return verifyTeamCreate();">
	<table>
		<tr>
			<th align="center" colspan="2">Create a New Team</th>
		</tr>
		<tr>
			<td>Team Name:</td>
			<td><input type="text" name="teamName" id="teamName" /></td>
		</tr>
		<tr>
			<td>Start Date:</td>
			<td><input type="text" name="startDate" id="startDate" class="date" value="<?php echo date('m-d-Y'); ?>" /></td>
		</tr>
		<tr>
			<td>End Date (optional):</td>
			<td><input type="text" name="endDate" id="endDate" class="date" value="" /></td>
		</tr>
		<tr>
			<td>Notes (optional):</td>
			<td><textarea name="notes"></textarea></td>
		</tr>
			<br />
		<tr>
			<td colspan="2" class="center">Choose Team Members:</td>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<ul>
					<?php
						include_once('WebControls.php');
						$list = new ListView($db, 'attendee', 'ID|FirstName|LastName', '<li><input type="checkbox" name="members[]" value="%d" />%s %s</li>', 'Active="yes" ORDER BY LastName;');
						$list->print_results();
					?>
				</ul>
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="submitted" value="yes" />
	
	<input type="submit" class="newTeamSubmit" value="Create Team" />
	</form>
</div>

<script>
	$('.date').datepicker({ dateFormat: "mm-dd-yy" });
</script>

<?php require_once('footer.php'); ?>