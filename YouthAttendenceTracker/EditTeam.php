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
	$title = "Edit Team";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	include_once('Misc.php');          // for extra functions (like processing the date)
	
	// get the team id from get
	if ( isset($_GET['team_id']) && $_GET['team_id'] != '' ) {
		$team_id = $_GET['team_id'];
	} else {
		echo '<h1 style="position: absolute; top: 50px; width: 100%; background-color: White; color: Red; font-size: 25px; z-index: 999;">ERROR:  The Team ID could not be retrieved...</h1>';
		exit();
	}
	
	/*** HANDLE FORM SUBMISSION IF APPLICABLE -- for making changes to the team ***/
	if ( isset($_POST['submitted']) ) {
		/***
		 * Process the team's data.
		 *
		 * 1. Query the table to edit the team's data.
		 * 2. Then, loop over any checked new members and insert into teamlist.
		 ***/
		
		// constant for checking mysql duplicate insert error, the code to check
		define('MYSQL_DUPLICATE_ERROR_CODE', 1062);
		
		// error variable to use to display messages to the user
		$error = false;
	
		// first need to change date into mysql format (YYYY-MM-DD)'
		$start = date_form_to_mysql($_POST['startDate']);
		if ( $_POST['endDate'] != '' ) 
			$end = date_form_to_mysql($_POST['endDate']);
		else
			$end = 'NULL';
		$query = 'UPDATE team SET Name = "' .  $_POST['teamName'] . '", Start = "' . $start;
		$query .= '", End = "' . $end . '", Notes = "' . $_POST['notes'] . '" WHERE ID = ' . $team_id . ';';
		if ( $db->query($query) ) {
			/***
			 * Team was updated so add any new students
			 ***/
			foreach ( $_POST['members'] as $member_id ) {
				if ( !$db->query( "INSERT INTO teamlist (TeamID, AttendeeID) VALUES ('$team_id', '$member_id');" ) ) {
					echo '<h1 style="position: absolute; background-color: White; z-index: 993">ERROR: Could not add the student to the team... Please go to edit team and check the members.</h1>';
					exit();	
				}
			}
			
			// success, so now redirect to Team.php instead of going back to EditTeam.php below
			header ( get_header_location('Team.php?team_id=' . $team_id) );
		} else {
			echo "<h1 style=\"position: absolute; background-color: White; z-index: 993\"> ERROR: Could not edit the team.</h1>";
			exit();
		}
	}
	
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
		} else {
			echo '<h1 style="position: absolute; top: 50px; width: 100%; background-color: White; color: Red; font-size: 25px; z-index: 999;">ERROR:  The Team\'s information could not be retrieved...</h1>';
			exit();
		}
	} else {
		echo '<h1 style="position: absolute; top: 50px; width: 100%; background-color: White; color: Red; font-size: 25px; z-index: 999;">ERROR:  The Team\'s information could not be retrieved...</h1>';
		exit();
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
	<!-- List of team data -->
	<form action="<?php echo $_SERVER['PHP_SELF'] . '?team_id=' . $team_id; ?>" method="post" onsubmit="return verifyTeamCreate();">
	<table>
		<tr>
			<th align="center" colspan="2">Edit Team</th>
		</tr>
		<tr>
			<td>Team Name:</td>
			<td><input type="text" name="teamName" id="teamName" value="<?= $team_name; ?>" /></td>
		</tr>
		<tr>
			<td>Start Date:</td>
			<td><input type="text" name="startDate" id="startDate" class="date" value="<?= date_mysql_to_form($team_start); ?>" /></td>
		</tr>
		<tr>
			<td>End Date (optional):</td>
			<td><input type="text" name="endDate" id="endDate" class="date" value="<?= date_mysql_to_form($team_end); ?>" /></td>
		</tr>
		<tr>
			<td>Notes (optional):</td>
			<td><textarea name="notes"><?= $team_notes; ?></textarea></td>
		</tr>
			<br />
		<tr>
			<th colspan="2" class="center">Current Team Members</th>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<?php
					// get the team members from teamlist and attendee
					$query = "SELECT t2.FirstName, t2.LastName, t2.ID FROM teamlist as t1 INNER JOIN attendee as t2 ON t1.AttendeeID = t2.ID WHERE t1.TeamID = $team_id ORDER BY t2.LastName;";
					if ( $results = $db->query($query) ) {
						while ( $object = $results->fetch_object() ) {
							printf('<span id="attendee_%d"><input type="button" value="x" class="deleteButton" onclick="removeTeamMember(%d, %d)" />%s %s<br /></span>', $object->ID, $team_id, $object->ID, $object->FirstName, $object->LastName);
						} 
					} else {
						echo "ERROR GETTING TEAM MEMBER";
					}
				?>
			</td>
		</tr>
		<tr>
			<th colspan="2" class="center">Choose Team Members:</th>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<ul>
					<?php
						// have to select attendees that are active but not in the current teamlist
						$query = "SELECT ID, FirstName, LastName FROM attendee WHERE Active='yes' AND ID NOT IN (SELECT AttendeeID FROM teamlist WHERE TeamID=15) ORDER BY LastName;";
						if ( $results = $db->query($query) ) {
							while ( $object = $results->fetch_object() ) {
								printf('<li><input type="checkbox" name="members[]" value="%d" />%s %s</li>', $object->ID, $object->FirstName, $object->LastName);
							}
						} else {
							echo "ERROR GETTING TEAM MEMBER";
						}
					?>
				</ul>
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="submitted" value="yes" />
	
	<input type="submit" class="newTeamSubmit" value="Apply Changes" />
	</form>
</div>

<script>
	$('.date').datepicker({ dateFormat: "mm-dd-yy" });
</script>

<?php require_once('footer.php'); ?>