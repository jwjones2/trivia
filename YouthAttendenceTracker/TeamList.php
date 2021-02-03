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
	$title = "Team Page";
	$body_class = "formBody";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	include_once('Misc.php');          // for extra functions (like processing the date)
	
	
	// check for old teams flag
	if ( isset($_GET['show_old']) && $_GET['show_old'] == "T" )
		$show_old = true;
	else
		$show_old = false;
?>

	   <div class="teamList">
		<table>
			<tr class="teamListHeader">
				<th align="left">Compare</th>
				<th align="left">Name</th>
				<th align="left">Start Date</th>
				<th align="left">End Date</th>
				<td></td>
			</tr>
			<?php          
				/**
					Get all active teams.  These are teams that don't have an end date
					set or that have an end date that is in the future.
					
					**NEW:  show_old will allow for showing the old teams -- a flag from GET, set above.
				 **/
				$counter = 0;
    	  			$query = 'SELECT * FROM team ';
				// edit query based on show_old, to show or hide old teams (teams whose end date is in the past)
				if ( $show_old )
					$query .= ';';
				else
					$query .= 'WHERE End >= CURDATE() OR End="0000-00-00";';
    	  			if ( $results = $db->query($query) ) {
    	  				while ( $dat = $results->fetch_object() )  {
						printf('<tr id="team_%d"', $dat->ID); // start the team row
    	  					if ( $counter % 2 == 0 ) // even row
    	  						echo ' class="even">'; 			              
    	  					else
    	  						echo '>';
    	  							
						printf('<td><input type="checkbox" name="compare" value="%d" /></td>', $dat->ID);
    	  					printf('<td>%s</td>', $dat->Name);                 // Print the team name
    	  					printf('<td>%s</td>', date_mysql_to_form($dat->Start));	   // Print the StartDate
						printf('<td>%s</td>', date_mysql_to_form($dat->End));	   // Print the EndDate
    	  					printf('<td><a href="./Team.php?team_id=%d"><input type="button" value="View Team" /></a>', $dat->ID); // set up form to submit the ID to TeamProfile when button is clicked
						printf('<input type="button" value="Remove Team" onclick="removeTeam(%d);" /></td>', $dat->ID); // set up form to submit the ID to TeamProfile when button is clicked
						echo '</tr>';   // end the row 
    	
			  			$counter++;  // increment the counter
    	  				}
    	  			}
		    	  ?>
			  
			<tr>
				<th colspan="5">
					<a href="./CreateTeam.php"><input type="button" value="New Team" /></a>
				</th>
			</tr>
			<tr>
				<th colspan="5">
					<input type="button" value="Compare Teams" onclick="var link = './CompareTeams.php?teams='; var v = ''; $('input:checked').each(function(){if (v != '') v += '|'; v += this.checked ? $(this).val(): '';}); location.replace(link + v);" /><br /><span class="small-text italic">*Select the teams above to compare.</span>
				</th>
			</tr>
		</table>
	   </div>
	   
	   <!-- button to show or hide the old teams -- teams with expired end dates -->
	   <div id="oldTeamsButton">
	
	   <?php
	      // set show old or hide old team button according to show_old
	      if ( $show_old ) 
		printf('<a href="%s"><input type="button" value="Hide Old Teams" onclick="" /></a>', $_SERVER['PHP_SELF']);
	      else
	        printf('<a href="%s?show_old=T"><input type="button" value="Show Old Teams" onclick="" /></a>', $_SERVER['PHP_SELF']);
	   ?>
	   
	   </div>
	   
<?php include_once('Footer.php'); ?>
