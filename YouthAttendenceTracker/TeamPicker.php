<?php
	session_start();
	error_reporting(0);
	/********************************************************
	 * TeamPicker.php
	 *
	 * Description:  Various ways to get the students by
	 * 	their points and then randomly pick a team
	 * 	weighting the selection to try to reduce putting
	 * 	too many of the highest point getters on a team.
	 * 	
	 ********************************************************/
 	 
	// output the header
	$title = "Team Picker";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('StudentPointsCalculator.php');  // for calculating the student's points
	
	/*** Get the team data ***/
	// team id
	$team_id = $_GET['teamID'];
	// get number of teams or set default
	if ( !isset($_GET['num_teams']) )
		$team_number = 2;
	else
		$team_number = $_GET['num_teams'];
	
	// variables for page
	// number of past months to look at for getting members points
	if ( isset($_GET['past_months']) )
		$past_months = $_GET['past_months'];
	else
		$past_months = 3;  // the default
	
	// build the past date...
	// subtract months from current, but if current is less than subtract, have to deincrement the year
	$current_month = date('m');
	$current_year = date('Y');
	if ( $current_month < $past_months ) {
		$current_year--;
		$current_month = 12 - ($past_months - $current_month);
	} else if ( $current_month == $past_months ) {
		$current_month = "01";  // january
	}
	if ( strlen($current_month) == 1 )
		$current_month = "0" . $current_month;
		
	$past_date = $current_year . "-" . $current_month . "-01";
?>
	<div class="team_picker">
	<?php
		$list = array();
		
		// get all the active student id's
		$query = "SELECT ID, FirstName, LastName FROM attendee WHERE Active='yes';";
		if ( $results = $db->query($query) ) {
			while ( $object = $results->fetch_object() ) {
				array_push($list, $object);
			}
		}
		
		class Container {
			public $name;
			public $points;
			
			function __construct ( $n, $p ) {
				$this->name = $n;
				$this->points = $p;
			}
		}
		
		// to hold the container objects for sorting
		$students = array();
		
		foreach ( $list as $student ) {
			$points = new StudentPointsCalculator($db, $student->ID);
			array_push($students, new Container($student->FirstName . ' ' . $student->LastName, $points->get_points($past_date)));
		}
		
		// sort the object -- using example adapted from: http://stackoverflow.com/questions/124266/sort-object-in-php
		function compare($a, $b) { 
			if($a->points == $b->points) {
			    return 0 ;
			} 
			return ($a->points < $b->points) ? 1 : -1;
		} 

		usort($students, 'compare');
		
		$pool = array();
		$top_points = 0;
		$count = 0;
		$teams_html = array();
		/***UPDATE -- FOR MULTIPLE TEAMS - MORE THAN 2 - Store teams html in array and use a function to iterate over the current team to write
			ALSO - need to change css of the spans holding the teams ***/
		foreach ( $students as $s ) {
			if ( $count == 0 ) {
				$teams_html[0] = "<span class='team_column'><h1>Captain = " . $s->name . "</h1>";
				$top_points = $s->points;
			} else if ( $count > 0 && $count < $team_number ) {
				$teams_html[$count] = "<span class='team_column'><h1>Captain = " . $s->name . "</h1>";
			} else {
				/** Add other students to the pool number of times based on their points. **/
				
				// set points at 10000 if 0
				$s_points = $s->points;
				if ( $s_points == 0 )
					$s_points = 10000;
				for ( $i = 0; $i < ceil($top_points / $s_points); $i++ ) {
					array_push($pool, $s->name);
				}
			}
			
			$count++;
		}
		
		/* Select Teams
		 * -Use pool to randomly select a team member for each team until no more members.
		 * -Select member then remove from pool.
		 ***/
		$count = 0;  // reset the counter to get odd and even
		while ( count($pool) > 0 ) { // loop until pool empty
			/**
			 * Get a random number from 0 - size of pool (-1 since is zero based)
			 * Print member on correct team by adding to variable.
			 * Remove all other occurences.
			 **/
			$r = rand(0, (count($pool) - 1) );
			
			// get the team member's name
			$member = $pool[$r];
			
			// add member to team, use count to determine which team
			$teams_html[$count] .= '<h3>' . $member . '</h3>';
				
			// remove the item selected and all other occurences
			// do this by building a new array and setting assigning pool to it
			$temp = array();
			foreach ( $pool as $p ) {
				if ( $p != $member )
					array_push($temp, $p);
			}
			$pool = $temp;
			
			// set count based on team number.  Count will be 0 for 1, 1 for 2, etc. until
			// greater than team number, then back to 0
			if ( ++$count >= $team_number )
				$count = 0;
		}
		
		// finish the teams html and print
		for ( $i = 0; $i < $team_number; $i++ )
			echo $teams_html[$i] . "</span>";
	?>
	</div>

<?php require_once('footer.php'); ?>