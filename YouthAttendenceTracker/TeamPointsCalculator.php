<?php
	/***********************************************************************
	 * CONSTANTS to define the points value for fields in service.
	 ***********************************************************************/
	define('HERE', 10000);
	define('SUNSCH', 10000);
	define('SUNMORN', 10000);
	define('SUNEVEN', 10000);
	define('BIBLE', 50000);
	define('VISITOR', 100000);
	
	// include the studentpointscalculator class for use in TeamPointsCalculator
	include_once('StudentPointsCalculator.php');
	
	/***********************************************************************
	 * Class TeamPointsCalculator
	 *
	 * Description:  Gets the points for a team by getting each students points
	 *	and totaling.
	 *
	 ***********************************************************************/
	class TeamPointsCalculator
	{
		/***************************************************************
		 * Private member variables
		 ***************************************************************/
		private $db;                        // db object to use in queries
		private $id;                        // the team's id
		private $points;                    // hold the total points
		private $error;                     // holds the errors 
		
		
		/***************************************************************
		 * Constructor
		 *
		 * Description:  Sets the team's id in private variable.  Also sets
		 *	the private variable that holds reference to a db object to
		 *	use in queries.
		 *
		 * Input variables:  id (integer)
		 *
		 * Return type:  n/a
		 ***************************************************************/
		function __construct ( $db, $id ) 
		{
			$this->db = $db;
			$this->id = $id;
			$this->error = null;    // set error to defaul of null
		}
		
		/***************************************************************
		 * member function get_points
		 *
		 * Description:  Gets the team points by getting the team students
		 *	and looping over students using StudentPoints to get each
		 *	student's points and adding to points.
		 *
		 * Input variables:  n/a
		 *
		 * Return type:  integer (points)
		 ***************************************************************/
		public function get_points ( ) 
		{
			// Get team start and end to pass to StudentPoints for range
			$query = 'SELECT Start, End FROM team WHERE ID=' . $this->id . ';';
			
			$team_start = '0000-00-00'; 
			$team_end = null;
			if ( $result = $this->db->query($query) )
			{
				if ( $row = $result->fetch_row() ) 
					$team_start = $row[0];   // store start date (in row[0]) 
					$team_end = $row[1];
			} else {
				$this->error = 'Error:  Could not get team points.';
			}
			
			// get the team members, loop over members getting points using team start date
			$query = 'SELECT AttendeeID FROM teamlist WHERE TeamID=' . $this->id . ';';
			if ( $result = $this->db->query($query) )
			{
				while ( $row = $result->fetch_array() )
				{
					$student = new StudentPointsCalculator($this->db, $row[0]);    // create new instance of StudentPoints with current attendee id
					$this->points += $student->get_points($team_start, $team_end);  // use StudentPoints' get_points to get the student points for team date range
											     // add to running total team points
				}
			}
			else
				$this->error = 'Error:  Could not get team points.';
				
			/* NOW, add the extra points for the team. Use function get_extra_points */
			$this->points += $this->get_extra_points();
			
			// return the value
			return $this->points;  // return the team points
		}
		
		/***************************************************************
		 * Member function get_extra_points
		 *
		 * Description:  Queries the servicepoints table for all the exta
		 *	points for team.  
		 *
		 * Input variables:  n/a
		 *
		 * Return variables:  integer (points)
		 ***************************************************************/
		public function get_extra_points ( )
		{
			// build query
			$query = 'SELECT Points FROM servicepoints WHERE TeamID=' . $this->id . ';';
			
			// variable to hold running total to return
			$points = 0;
			
			// execute query and add points together
			if ( $results = $this->db->query($query) )
			{
				while ( $row = $results->fetch_array() ) 
					$points += $row[0];  // add the returned value to $points
			}
			
			// return the value
			return $points;
		}
		
	}
	
?>
