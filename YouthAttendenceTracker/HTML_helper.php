<?php

/******************************************************
 * Function selectBox
 *
 * Description:  Takes four arguments and constructs an
 * 	HTML select list.  First argument is name, then id,
 *	them type of values input in fourth parameter values.
 *	If the first value is greater than the second then
 *	list will be printed in descending order.
 *
 * Input variables:  name, id, type (range or list of values),
 *	values (described by type input variable), init_value (an optional initial value),
 *	disabled (whether or not the select box should start disabled)
 *
 * Return type:  N/A (outputs html)
 *****************************************************/
function selectBox ( $name, $id, $type, $values, $optional_parameters='not_set', $init_value='not_set', $disabled=0 )
{
	// First:  separate values into an array
	$values_list = explode( ',', $values );

	// Second:  begin outputing the select list
	echo '<select name="' . $name . '" id="' . $id . '" ';  // output name and id in opening select tag

	if ( $optional_parameters != 'not_set' )
	{
		$params = explode('|', $optional_parameters);
		$pair = array();
		for ( $i = 0; $i < count($params); $i++ )
		{
			$pair = explode('=', $params[$i]);
			echo "$pair[0]=\"$pair[1]\"";
		}
	}

	// check for disabled optional parameter and write disabled if 1, or true
	if ( $disabled == 1 )
		echo 'disabled="disabled"';

	echo '>';

	// Check that init_value is set and add the first <option...> tag with
	// its value as the value if so
	if ( $init_value != 'not_set' )
		printf('<option value="%s">%s</option>', $init_value, $init_value);

	// Third:  Check for type of values (range or list) and
	//    process accordingly.  If any value other than "range" is entered
	//    for $type then the default is list.
	if ( $type == "range" )
	{
		// If $values are a range of values to output then start at
		// first value in $values_list and then end at second value
		// in $values_list.  Ascending or descending is determined by
		// whether the first value is smaller or larger than the second.
		// Must add one to $values_list[1] so that loop stops at $values_list[1]
		// or substract in case of descending.
		if ( isset($values_list[0]) && isset($values_list[1]) )  // **Check that $values_list[0] and $values_list[1] exists
		{
			// check for range type ascending or descending based on whether first value is smaller than second.
			if ( $values_list[0] < $values_list[1] )
			{
				for ( $i = $values_list[0]; $i < $values_list[1] + 1; $i++ )
					printf('<option value="%s">%s</option>', $i, $i);
			}
			else
			{
				for ( $i = $values_list[0]; $i > $values_list[1] - 1; $i-- )
					printf('<option value="%s">%s</option>', $i, $i);
			}
		}
	}
	else   // any value other than "range" input
	{
		// loop over $values_list outputing the values
		for ( $i = 0; $i < count($values_list); $i++ )
			printf('<option value="%s">%s</option>', $values_list[$i], $values_list[$i]);
	}

	// Finally:  finisht the select list's HTML syntax
	echo '</select>';
}

	/********************************************
	 * function disable
	 *
	 * Description:  Checks for the 'Edit' global value in $_POST
	 *	and if it is not set and set to 'y' then disable
	 * 	the input box by writing 'disabled="disabled"'
	 *
	 * Input variables:  n/a
	 *
	 * Return type: n/a
	 ********************************************/
	function disable()
        {
        	if ( isset($_POST['Edit']) && $_POST['Edit'] == 'y' )
        		echo '';
        	else  // print disabled and also remove borders from input boxes
        	{
        		echo ' disabled="disabled" ';
        		echo ' style="border: none; font-size: 14px; font-family: Georgia;" ';
        	}
        }

        /********************************************
	 * function disable_check
	 *
	 * Description:  Checks for the 'Edit' global value in $_POST
	 *	and if it is not set and set to 'y' then return 1
	 *	else return 0 for false, meaning 'not disabled'
	 *
	 * Input variables:  n/a
	 *
	 * Return type: int
	 ********************************************/
	function disable_check()
        {
        	// if Edit is set and set to y, then not disabled so
        	// return 0
        	if ( isset($_POST['Edit']) && $_POST['Edit'] == 'y' )
        		return 0;
        	else  // else return 1, is disabled
        		return 1;
        }

        /********************************************
	 * function write_menu
	 *
	 * Description:  Ouputs the menu for AttendenceTracker
	 *	so that any changes can be made to the menu
	 *	in one location, this function.
	 *
	 * Input variables:  n/a
	 *
	 * Return type: n/a
	 ********************************************/
	function write_menu()
	{
		echo '<div class="menu">';  // menu start, opening div

		// store links in array
		$links = array();
		$links[0] = array("home.php", "Calendar");
		$links[1] = array("AttendeeGridView.php", "Students List");
		$links[2] = array("AddAttendee.php", "New Student");
		$links[3] = array("AddSponsor.php", "New Sponsor");
		//$links[4] = array("NewTeam.php", "New Team");
		$links[4] = array("TeamList.php", "Teams List");
		$links[5] = array("AttendenceStats.php", "Stats");
		//$links[4] = array("SignupSetup.php", "Service Signup");
		$links[6] = array("IndividualPointsList.php", "Monthly Points Listing");

		$first = true;  // flag to not write divider on first
		$divider = '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';  // the divider code

		// write the links
		foreach ($links as $link) {
			if ( $first )
				$first = false;
			else
				echo $divider;

			printf('<a href="./%s">%s</a>', $link[0], $link[1]);
		}

		echo '</div>';  // write closing div
	}

?>
