<?php
        /***********************************************************************
	 * Class StudentPointsCalculator
	 *
	 * Description:  Gets the points for a student from a database.
	 *
	 ***********************************************************************/
	class StudentPointsCalculator
	{
		/***************************************************************
		 * Private member variables
		 ***************************************************************/
		private $db;                        // db object to use in queries
		private $id;                        // the student's id
		private $points;                    // hold the total points
		private $error;                     // holds errors that occurs

		/***************************************************************
		 * Constants for Signin values
		 ***************************************************************/
		const HERE = 10000;
		const SUNSCH = 30000;
		const SUNMORN = 10000;
		const SUNEVEN = 10000;
		const BIBLE = 50000;
		const VISITOR = 100000;


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
		 * Input variables:  date begin (optional, in format YYYY-MM-DD), end date (optional)
		 *
		 * Return type:  integer (points)
		 ***************************************************************/
		public function get_points ( $start_date = null, $end_date = null )
		{
			// set points to 0
			$this->points = 0;

      // first if there is a begin and/or an end date, collect all the services between that range
      $service_ids = array();
      if ( $start_date != null && $end_date == null ) {
        $query = "SELECT ID FROM service WHERE DATE(Date) > '$start_date';";
        if ( $result = $this->db->query($query) ) {
          while ( $object = $result->fetch_object() ) {
            array_push($service_ids, $object->ID);
          }
        } else {
          $this->error = 'Error:  could not get points from database.';
          return false;
        }

      } else if ( $start_date == null && $end_date != null ) {
        $query = "SELECT ID FROM service WHERE DATE(Date) < '$end_date';";
        if ( $result = $this->db->query($query) ) {
          while ( $object = $result->fetch_object() ) {
            array_push($service_ids, $object->ID);
          }
        } else {
          $this->error = 'Error:  could not get points from database.';
          return false;
        }
      } else if ( $start_date != null && $end_date != null ) {
        $query = "SELECT ID FROM service WHERE DATE(Date) BETWEEN '$start_date' AND '$end_date';";
        if ( $result = $this->db->query($query) ) {
          while ( $object = $result->fetch_object() ) {
            array_push($service_ids, $object->ID);
          }
        } else {
          $this->error = 'Error:  could not get points from database.';
          return false;
        }
      } else {  
				// no need for specific service dates, just grab all and total
        /* Build Query and query the servicelist table for all rows that match the attendee's id */
				$query = 'SELECT SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors, extrapoints FROM servicelist WHERE AttendeeID = ' . $this->id . ';';

        if ( $result = $this->db->query($query) ) {
          while ( $arr = $result->fetch_array(MYSQLI_NUM) ) {
            $this->points += $this->add_service_points($arr);  // add to running total of points
          }
        } else {
          $this->error = 'Error:  could not get points from database.';
          return false;
        }

				return $this->points;
      }

      // Loop over servicelist with service_ids
      foreach ( $service_ids as $id ) {
        // query servicelist
        $query = 'SELECT SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors, extrapoints FROM servicelist WHERE AttendeeID = ' . $this->id . ' AND ServiceID = ' . $id . ';';

        if ( $result = $this->db->query($query) ) {
          while ( $arr = $result->fetch_array(MYSQLI_NUM) ) {
            $this->points += $this->add_service_points($arr);
          }
        } else {
          $this->error = 'Error:  could not get points from database.';
          return false;
        }
      }

      return $this->points;
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
			$points = self::HERE;  // holds running total of points, start at HERE value because Here is not recorded table

			/* check each array value and add points */
			// Sunday School
			if ( $array[0] == 'yes' )
			{
				$points += self::SUNSCH;
			}

			// Sunday Morning
			if ( $array[1] == 'yes' )
			{
				$points += self::SUNMORN;
			}

			// Sunday Evening
			if ( $array[2] == 'yes' )
			{
				$points += self::SUNEVEN;
			}

			// Bible
			if ( $array[3] == 'yes' )
			{
				$points += self::BIBLE;
			}

			// Visitor
      for ( $i = 0; $i < $array[4]; $i++ )
        $points += self::VISITOR;  // must multiply number of visitors by value to get total


      // Extra points
      $points += $array[5];

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
