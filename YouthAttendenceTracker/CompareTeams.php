<?php
	/********************************************************
	 * CompareTeams.php
	 *
	 * Description:  Just like Team, except handles more than
	 * 	one team and attempts to show them side by side
	 * 	evenly spaced in the page.
	 *
	 ********************************************************/
 	 
	// output the header
	$title = "Team Comparison Page";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');          // the DBTable superclass
	require_once('Table_helper.php');     // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');      // functions for handling HTML output and manipulation
	include_once('Misc.php');             // for extra functions (like processing the date)
	include('TeamPointsCalculator.php');  // for getting team points
	
	// get the teams ids from GET
	if ( isset($_GET['teams']) && $_GET['teams'] != '' ) {
		$team_ids = $_GET['teams'];
	} else {
		echo '<h1 style="position: absolute; top: 50px; width: 100%; background-color: White; color: Red; font-size: 25px; z-index: 999;">ERROR:  The Team IDs could not be retrieved...</h1>';
		exit();
	}
	
	// split the team ids into an array
	$teams = explode('|', $team_ids);
	
	// query db for teams data, put in multi-dimentional array
	$teams_data = array();
	// loop over the team ids and query for the teams' data
	foreach ( $teams as $id ) {
		// first make sure id is not blank, if so continue
		if ( $id == '' )
			continue;
		
		$query = "SELECT * FROM team WHERE ID=$id;";
		if ( $result = $db->query($query) ) {
			if ( $object = $result->fetch_object() ) {
				$data = array();
				$data[] = $id;   // put id on front of array
				$data[] = $object->Name;
				$data[] = $object->Start;
				// check end, it could be '0000-00-00', if so set to blank
				if ( $object->End == '0000-00-00' )
					$object->End = '';
				$data[] = $object->End;	
				$data[] = $object->Notes;
			
				// push the data onto teams_data array
				$teams_data[] = $data;
				
			} else {
				echo '<h1 style="position: absolute; top: 50px; width: 100%; background-color: White; color: Red; font-size: 25px; z-index: 999;">ERROR:  The Team\'s information could not be retrieved...</h1>';
				exit();
			}
		} else {
			echo '<h1 style="position: absolute; top: 50px; width: 100%; background-color: White; color: Red; font-size: 25px; z-index: 999;">ERROR:  The Team\'s information could not be retrieved...</h1>';
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

<div class="container team-points-compare compare-teams">
	<!-- List of teams information -->
	<?php
		// Need to loop over teams_data and output each table below with equal width
		$width = 100 / count($teams_data) - 3;  // remove a percentage to account for padding
		$counter = -1;  // to keep track of which team being written
		foreach ( $teams_data as $team_data ) :
			$counter++;  // increment here since it is easier to read given the structure of the endforeach syntax
		
	?>
	<table style="width: <?= $width; ?>%; <?php /*if ( $counter != 0 ) echo 'border-left: 2px dashed #808080;';*/ ?>">
		<tr>
			<th align="center" colspan="2">Team:  <span class="italic bold">"<?= $team_data[1]; ?>"</span></th>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<a href="./EditTeam.php?team_id=<?= $team_data[0]; ?>"><input type="submit" value="Edit Team" /></a>
			</td>
		</tr>
		<tr>
			<td class="right">Current Points:</td>
			<td>
				<?php
					// get team points
					try {
						$team = new TeamPointsCalculator($db, $team_data[0]);
						printf('%s', number_format($team->get_points()) );
					} catch ( Exception $e ) {
						echo "FAILED to get the team points!!!";
					}
				?>
			</td>
		</tr>
		<tr>
			<td class="right">Start Date:</td>
			<td><?= date_mysql_to_form($team_data[2]); ?></td>
		</tr>
		<tr>
			<td class="right">End Date:</td>
			<td><?= date_mysql_to_form($team_data[3]); ?></td>
		</tr>
		<tr>
			<td class="right">Notes:</td>
			<td><?= $team_data[4]; ?></td>
		</tr>
		<tr><td colspan="2"><br /></td></tr>
		<tr>
			<th colspan="2" class="center">Current Team Members</th>
		</tr>
		<tr>
			<td colspan="2" class="center">
		<?php
			// get the team members from teamlist and attendee
			$query = "SELECT t2.FirstName, t2.LastName, t2.ID, t2.PictureURL, t2.DOB, t2.Sex FROM teamlist as t1 INNER JOIN attendee as t2 ON t1.AttendeeID = t2.ID WHERE t1.TeamID = " . $team_data[0] . " ORDER BY t2.LastName;";
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
							
					printf('%s %s &nbsp;&nbsp;&nbsp;', $object->FirstName, $object->LastName);                 // Print the full name:  FirstName LastName  ... ->FirstName ->Lastname
					printf('(%s)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', date_mysql_to_form($object->DOB));	   			                 // Print the DOB
					printf('<a href="./ViewProfile.php?ID=%d"><input type="button" value="View Profile" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $object->ID);  // print the view profile button
					printf('<input type="button" class="deleteButton" value="Remove Member" onclick="removeTeamMember(%d, %d);" /><br /></span>', $team_id, $object->ID);
				} 
			} else {
				echo "ERROR GETTING TEAM MEMBER" . $team_data[0];
			}
		?>
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="submitted" value="yes" />

		<?php endforeach; ?>
</div>


<script>
	$('.date').datepicker({ dateFormat: "mm-dd-yy" });
</script>

<?php require_once('footer.php'); ?>