<?php
date_default_timezone_set('America/Chicago');

	function get_average_attendence ( $db, $range = null, $year = null )  {
		// check if year is null, if so set to default of current year
		if ( $year == null )
			$year = date('Y');
		
		if ( !$range ) // no range set, make month selector blank
		{
			// build query to get all ServiceId from servicelist table
			$month_selector = "";
						
		} else {
			/***
			 * Range set, which is a month or months delineated by '-'.
			 *
			 * Therefore alter query to get only servelist records for the given
			 * months.
			 ***/
			// first check for range of one or multiple months, split by delineator
			$range_list = explode('-', $range);
			$month_selector = " AND MONTH(t2.Date) ";
			
			if ( count($range_list) == 1 )  { // one month
				$month_selector .= '= ' . $range_list[0];  // set = and month number for the query
			} else {
				// need to set the range as a month >= first and month <= last
				$month_selector .= '>= ' . $range_list[0] . ' AND MONTH(t2.`Date`) <= ' . $range_list[1];
			}
		}
		
		$query = 'SELECT COUNT(t1.ServiceID) FROM servicelist AS t1 INNER JOIN service AS t2 ON t1.ServiceID = t2.ID WHERE YEAR(t2.Date) = ' . $year . $month_selector . ' GROUP BY t1.ServiceID;';
	
		$total = 0;           // running total of services
		$service_count = "";  // number of services
		
		// query was set above.  Do query and count
		if ( $result = $db->query($query) ) {
			// set service_count for average
			$service_count = $result->num_rows;
			
			// loop to store service attendence totals
			while ( $arr = $result->fetch_array() ) {
				// just add totals onto total variable
				$total += $arr[0];
			}
		}
		
		// calculate average and return, if service_count is 0, no services this month yet, return 0
		if ( $service_count == 0 )
			return 0;
		else 
			return round($total / $service_count, 2);  // return average by dividing total by number of services represented in $count
	}
	
	function get_stat_bar_height ( $db, $month, $year, $multiplier = 20 ) {
		// use get average attendence to do main work then return multiplied by multiplier
		return round(get_average_attendence($db, $month, $year) * $multiplier, 2);
	}
	
	function check ( $val ) {
		// DESCRIPTION: Just uses isset to check for the value and checks to see if empty string
		//    Used to reduce redundant typing everytime need to check a GET or POST value
		if ( isset($val) && $val != "" )
			return true;
		else
			return false;
	}
	
	function check_and_assign ( $val, &$assign, $default=false ) {
		// DESCRIPTION: Just uses isset to check for the value and checks to see if empty string
		//    Used to reduce redundant typing everytime need to check a GET or POST value
		//    -Same as check except takes another parameter that gets assigned to val if val is valid
		//    -Also takes a default value to assign if val is invalid, else no value is assigned 
		if ( isset($val) && $val != "" ) {
			$assign = $val;
		} else {
			if ( $default )
				$assign = $default;
			else
				return false;
		}
		
		// return true in all cases except above where assignment failed and no default set
		return true;
	}
	
	function get_header_location ( $page ) {
		/***
		 * Used to return the header location to use with header function.
		 * -->This function's sole purpose is to abstract the header string
		 * across multiple pages for easier uploading or moving to another server.
		 ***/
		return "Location:  " . baseURL() . $page;
		//$header_location = "Location:  http://www.praiseassembly911.org/PraiseSearcher/$page";
		
		//return $header_location;
	}
        
        // simple function that returns the base URL for all page calls
        // *Use this over all pages to be able to change root directory or
        // migrate with only changing this return.
        function baseURL () {
            // change this line to change base URL
            //$url = "https://host210.hostmonster.com/~praiseas/PraiseSearcher/";
            $url = "http://localhost/YouthAttendenceTracker/";
            
            return $url;
        }
	
	// functions for changing date from mysql to form and vice versa
	function date_mysql_to_form ( $date ) {
		// first, return if date is blank
		if ( $date == '' )
			return '';
		
		$d_parts = explode('-', $date);
		return sprintf('%s-%s-%s', $d_parts[1], $d_parts[2], $d_parts[0]);
	}
	function date_form_to_mysql ( $date ) {
		// first, return if date is blank
		if ( $date == '' )
			return '';
		
		$d_parts = explode('-', $date);
		return sprintf('%s-%s-%s', $d_parts[2], $d_parts[0], $d_parts[1]);
	}
	$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	// functions for changing date from mysql to human readable
	function date_human_readable ( $date ) {
		$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		// first, return if date is blank
		if ( $date == '' )
			return '';
		
		$d_parts = explode('-', $date);
		$month_index = $d_parts[1] - 1;
		return sprintf('%s %s, %s', $months[$month_index], $d_parts[2], $d_parts[0]);
	}
	
	function parse_names_from_form ( $str ) {
		$names = explode(' ', trim($str));
		$first_name = "";
		$last_name = $names[count($names)-1];
    
		for ( $i = 0; $i < count($names); $i++ ) {
			if ( $i == ( count($names)-1 ) )
				break;
	
			$first_name .= $names[$i] . " ";
		}
    	
		return array(trim($first_name), trim($last_name));
	}
?>
