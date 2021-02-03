<?php
	// Globals to use to simplify changing the table values SunSchAttend...Visitor, etc.
	$ss = "SunSchAttend";
	$sm = "SunMornAttend";
	$se = "SunEvenAttend";
	$bi = "Bible";
	$vi = "Visitor";
  $ex = "extrapoints";

	// Associative Array Points value -- holds the value of points for
	// service activities or attendence, etc
	$points_value = array();
	$points_value[$ss] = 30000;
	$points_value[$sm] = 10000;
	$points_value[$se] = 10000;
	$points_value[$bi] = 50000;
	$points_value[$vi] = 100000;

	/*******************************************************************
	 * Function getPoints
	 *
	 * Description:  Just builds the points based on Bible, Here...
	 * 		and returns int.
	 *
	 * Input variables:  Result Object of table record
	 *
	 * Return type: int (the points)
	 *******************************************************************/
	function getPoints ( $obj ) {
		// Just check SunSchoolAttend, SunMornAttend, SunEvenAttend, Bible, Visitor
		//  if yes add points value--Get points value from assoc. array points_value
		$points = 10000;  // to return, default to 10000 since that is the value if the student was present

		// get global values
		global $ss, $sm, $se, $bi, $vi, $ex, $points_value;

		// Sunday School
		if ( $obj->$ss == "yes" )
			$points += $points_value[$ss];

		// Sunday Morning
		if ( $obj->$sm == "yes" )
			$points += $points_value[$sm];

		// Sunday Evening
		if ( $obj->$se == "yes" )
			$points += $points_value[$se];

		// Bible
		if ( $obj->$bi == "yes" )
			$points += $points_value[$bi];

		// Visitor
		if ( $obj->$vi == "yes" )
			$points += $points_value[$vi];

                // Extra points, just add to points value
                $points += $obj->$ex;

		return $points;
	}
