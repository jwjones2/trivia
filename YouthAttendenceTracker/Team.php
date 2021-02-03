<?php
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
	$title = "Team Page";
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
	<!-- List of team points -->
	<table>
		<tr>
			<th align="center" colspan="2">Team:  <span class="italic bold">"<?= $team_name; ?>"</span></th>
		</tr>
		<tr>
			<td class="center" colspan="2">
				<?php
					// get team points using DBMiscClasses.php
					include_once('TeamPointsCalculator.php');
					try {
						$team = new TeamPointsCalculator($db, $team_id);
						printf('Current Team Points = %s', number_format($team->get_points()) );
					} catch ( Exception $e ) {
						echo "FAILED to get the team points!!!";
					}
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="center">Start Date: &nbsp;&nbsp;&nbsp;
			<?= date_mysql_to_form($team_start); ?></td>
		</tr>
		<tr>
			<td colspan="2" class="center">End Date (optional): &nbsp;&nbsp;&nbsp;
			<?= date_mysql_to_form($team_end); ?></td>
		</tr>
		<tr>
			<td colspan="2" class="center">Notes (optional): &nbsp;&nbsp;&nbsp;
			<?= $team_notes; ?></td>
		</tr>
	</table>

	<table>
		<tr>
			<th colspan="5" class="center">Current Team Members</th>
		</tr>

		<?php
			// get the team members from teamlist and attendee
			$query = "SELECT t2.FirstName, t2.LastName, t2.ID, t2.PictureURL, t2.DOB, t2.Sex FROM teamlist as t1 INNER JOIN attendee as t2 ON t1.AttendeeID = t2.ID WHERE t1.TeamID = $team_id ORDER BY t2.LastName;";
			if ( $results = $db->query($query) ) {
				while ( $object = $results->fetch_object() ) {
					printf('<tr><td><span id="attendee_%d" colspan="2"><img src="', $object->ID); //start the td for this member and the picture
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

					printf('%s %s </span></td>', $object->FirstName, $object->LastName);                 // Print the full name:  FirstName LastName  ... ->FirstName ->Lastname
					printf('<td>%s</td>', date_mysql_to_form($object->DOB));	   			                 // Print the DOB
					printf('<td><a href="./ViewProfile.php?ID=%d"><input type="button" value="View Profile" /></a></td>', $object->ID);  // print the view profile button
					printf('<td><input type="button" class="deleteButton" value="Remove Member" onclick="removeTeamMember(%d, %d);" /></td>', $team_id, $object->ID);

					// now print the team members points from the beginning of teams still now or the end databases
					include_once('StudentPointsCalculator.php');
					$student_points = new StudentPointsCalculator($db, $object->ID);
					// set team_start and team_end to null if blank
					if ( $team_start == '' )
						$team_start = null;
					if ( $team_end == '' )
						$team_end = null;
					printf('<td>Points = %s</td></tr>', number_format($student_points->get_points($team_start, $team_end)));

				}
			} else {
				echo "ERROR GETTING TEAM MEMBER";
			}
		?>

		</tr>
	</table>

	<input type="hidden" name="submitted" value="yes" />

	<a href="./EditTeam.php?team_id=<?= $team_id; ?>"><input type="submit" class="newTeamSubmit" value="Edit Team" /></a>

</div>


<script>
	$('.date').datepicker({ dateFormat: "mm-dd-yy" });
</script>

<?php require_once('footer.php'); ?>
