<?php
	session_start();
	error_reporting(0);
	/********************************************************
	 * Signin.php
	 *
	 * Description:  
	 * 	
	 ********************************************************/
 	 
	// output the header
	$title = "Team Points";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	/*** Get the team data ***/
	// team id
	$team_id = $_GET['teamID'];
	
	// team's start and end date
	
?>

<div class="container team-points">
	<!-- List of team points -->
	<table>
		<tr>
			<th class="center">Students' Points List</th>
		</tr>
		<?php           			
    			require_once('WebControls.php');
			$year = date('Y');  // get the full year for use in list query
    			$list = new ListView($db, 'attendee', 'FirstName|LastName', '<tr class="center"><td>%s %s</td></tr>', 'Active="yes" AND DATE(ReleaseFormUpdate) BETWEEN "' . $year . '-01-01" AND "' . $year . '-12-31" ORDER BY LastName');
    			$list->print_results();
	    	  ?>
	</table>
</div>

<?php require_once('footer.php'); ?>