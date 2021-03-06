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
	
	
	/***********************************************************************
	 * Class TeamPoints
	 *
	 * Description:  Gets the points for a team by getting each students points
	 *	and totaling.
	 *
	 ***********************************************************************/
	class TeamPoints
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
			/* Get the team members and start date (for use in date range to pass to get_points of StudentPoints) */
			// first get team start date
			$query = 'SELECT Start FROM team WHERE ID=' . $this->id . ';';
			
			if ( $result = $this->db->query($query) )
			{
				if ( $row = $result->fetch_row() ) 
					$team_start = $row[0];   // store start date (in row[0]) 
			}
			else
				$error = 'Error:  Could not get team points.';
				
			// get the team members, loop over members getting points using team start date
			$query = 'SELECT AttendeeID FROM teamlist WHERE TeamID=' . $this->id . ';';
			if ( $result = $this->db->query($query) )
			{
				while ( $row = $result->fetch_array() )
				{
					$student = new StudentPoints($this->db, $row[0]);    // create new instance of StudentPoints with current attendee id
					echo "<h1>$row[0] " . number_format($student->get_points($team_start)) . "</h1>";
					$this->points += $student->get_points($team_start);  // use StudentPoints' get_points to get the student points for team date range
											     // add to running total team points
				}
			}
			else
				$error = 'Error:  Could not get team points.';
				
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
	
	
	/***********************************************************************
	 * Class StudentPoints
	 *
	 * Description:  Gets the points for a student from a database.
	 *
	 ***********************************************************************/
	class StudentPoints
	{
		/***************************************************************
		 * Private member variables
		 ***************************************************************/
		private $db;                        // db object to use in queries
		private $id;                        // the team's id
		private $points;                    // hold the total points
		private $error;                     // holds errors that occurs
		
		
		/***************************************************************
		 * Constructor
		 *
		 * Description:  Sets the student's id and a db connection reference
		 *	object.
		 *
		 * Input variables:  id (integer)
		 *
		 * Return type:  n/a
		 ***************************************************************/
		function __construct ( $db, $id ) 
		{
			$this->db = $db;
			$this->id = $id;
			$this->error = null;   // set error to default of null
		}
		
		/***************************************************************
		 * member function get_points
		 *
		 * Description:  Does the work for getting the student's points.
		 *	Queries the service list table based on the student's id
		 *	and optional date range.  The returned rows are 
		 *	processed by adding the 'yes' values (based on their points
		 *	values defined by constants) and storing in points variable.
		 *	Returns the points value.
		 *
		 * Input variables:  date begin (optional, in format YYYY-MM-DD)
		 *
		 * Return type:  integer (points)
		 ***************************************************************/
		public function get_points ( $date_begin = null )
		{
			// set points to 0
			$this->points = 0;
			
			/* Build Query and query the servicelist table for all rows that match the attendee's id */
			// begin query 
			$query = 'SELECT ServiceID, SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors FROM servicelist WHERE AttendeeID = ' . $this->id;
						
			$services = array();  // array to hold service results
						
			// check to make sure query was successful
			if ( $result = $this->db->query($query) )
			{
				while ( $object = $result->fetch_array() )
				{
					// get the results and build associative array
					$services[$object[0]] = array($object[1], $object[2], $object[3], $object[4], $object[5]);
				}
			}
			else
				$this->error = 'Error:  could not get points from database.';
			
			/* 
			   Check if date_begin is null, if not need to get 
			   list of services to use to get results.
			 */
			if ( $date_begin ) // not null
			{
				// query service for services between input date and current date (retrieved using date())
				$query = 'SELECT ID FROM service WHERE DATE(Date) BETWEEN "' . $date_begin . '" AND "' . date('Y-m-d') . '";';
				if ( $result = $this->db->query($query) )
				{
					while ( $object = $result->fetch_array() )
					{
						// check each service's id to see if it is in services assoc. array, if so add points
						if ( array_key_exists($object[0], $services) )  // if the service id is in assoc. array
						{
							$this->points += $this->add_service_points($services[$object[0]]);  // use add_service_points to get points from row
						}
					}
					
					return $this->points;  // return points
				}
				else
					$this->error = 'Error:  could not get points from database.';
			}
			else  // no date range so just loop over services adding up points
			{
				foreach ( $services as $s )
					$this->points += $this->add_service_points($s);
				
				return $this->points;  // return points
			}
		}
		
		/***************************************************************
		 * member function add_service_points
		 *
		 * Description:  Adds the points from a row returned from service table
		 * 	by adding all yes values according to their points value
		 *	defined by constants.
		 *
		 * Input variable:  array (reference)
		 *
		 * Return type:  integer
		 ***************************************************************/
		private function add_service_points ( $array ) 
		{
			$points = HERE;  // holds running total of points, start at HERE value because Here is not recorded table
			
			/* check each array value and add points */
			// Sunday School
			if ( $array[0] == 'yes' )
			{
				$points += SUNSCH;
			}
			
			// Sunday Morning
			if ( $array[1] == 'yes' )
			{
				$points += SUNMORN;
			}
			
			// Sunday Evening
			if ( $array[2] == 'yes' )
			{
				$points += SUNEVEN;
			}
			
			// Bible
			if ( $array[3] == 'yes' )
			{
				$points += BIBLE;
			}
			
			// Visitor
			$points += VISITOR * $array[4];  // must multiply number of visitors by value to get total
			
			// return the collected points
			return $points;
		}
		
		/***************************************************************
		 * member function print_points
		 *
		 * Description:  Prints the value of points.
		 *
		 * Input variable:  n/a
		 *
		 * Return type:  n/a
		 ***************************************************************/
		public function print_points ( )
		{
			echo $this->points;
		}
	}
		 
?>
