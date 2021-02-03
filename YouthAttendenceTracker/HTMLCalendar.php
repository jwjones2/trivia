<?php

include('Calendar.php');

class HTMLCalendar extends Calendar
{
	// Constructor to call parent's constructor
	function __construct ( $month, $year ) {
		parent::__construct ( $month, $year );
	}
	
	// override the write_day function
	public function write_day ( ) {
		// Connect to the db; use MYSQL_helper.php for mysql login
		require_once('MYSQL_helper.php');
		$db = db_connect( );
		
		$results = "";  // to store the results to return
	
		// need to query service for each day checking to see if there is a service
		// if so, return link to Signup with date
		// if not, return html for javascript to handle creating a new service
		$date = parent::get_current_date();
		
		$query = "SELECT ID, Date, EventType, submitted FROM service WHERE Date='$date';";
		/***UPDATE -- FUTURE TODO -- Consider adding a hover/popup that displays the Description of service from service table ***/
		if ( $result = $db->query($query) ) {
			if ( $object = $result->fetch_object() ) {
				// check if submitted or not and set class to reflect this
				if ( $object->submitted == "yes" ) 
					$results .= '<a class="service-listing submitted-yes" href="Signin.php?date=' . $object->Date . '">' . $object->EventType . '</a>';
				else
					$results .= '<a class="service-listing submitted-no" href="Signin.php?date=' . $object->Date . '">' . $object->EventType . '</a>';
			} else {
				// no service so add html for adding service
				$results .= '<a class="add-service" href="Signin.php?date=' . $date . '">+</a>';
			}
		} else {
			return 'Error getting service.';
		}
		
		// now add any birthdays that might be present for the month to the Calendar
		// use webcontrols to get birthdays and print in tabular format
		// use month to build the where clause to get birthdays from current month
		require_once('WebControls.php');
		$where = "Active='yes' AND MONTH(DOB) = ". parent::get_month() . " AND DAY(DOB) = " . parent::get_current_day() . " ORDER BY DOB;";  
		$list = new ListView($db, 'attendee', 'FirstName|LastName', '<tr><td>%s %s</td></tr>', $where);
		$results .= '<div id="birthday-listing">' . $list->return_results() . '</div>';
		
		return $results;
	}
}


?>