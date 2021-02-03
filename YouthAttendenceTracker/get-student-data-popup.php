<?php
    
	/********************************************************
	 * test-output.php
	 *
	 * Description:  Gets an ID from $_POST and uses Attendee
	 *	and Student (and Sponsor) classes to get the values
	 *	of a row in the correspondind tables, outputting
	 *	the values.
	 ********************************************************/
	// Turn off all error reporting
	//error_reporting(0);

	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );
        
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
        
        // check for get for ID if post ID not set
        if ( !isset($_POST['ID']) )
            $_POST['ID'] = $_GET['ID'];
            
	  	
        	/*******************************************
        	 * Get row from table section.
        	 *******************************************/
              	$id = $db->real_escape_string($_POST['ID']); // use real_escape_string to escape the input value	
        	
        	$a = new Attendee($db, $_POST, $id);         // create Attendee object using optional parameter in constructor to pass id
        	$s = new Student($db, $_POST, $id);          // create Student object using optional parameter in constructor to pass id
        	
        	/**  Attendee now holds the values of the row in attendee table corresponding to $id.  Now populate the form below with these values **/
        	
        	
        	// variable to hold confirmation message on UPDATE
        	$confirmation_message = "";
        	
        	
        	/******  Disable Check  *******/
        	// use disable_check to set an int to use in disabling select boxes if necessary
        	$disabled = disable_check();
		include_once('Misc.php');
                printf('%s %s|', $a->retrieve_value("FirstName"), $a->retrieve_value("LastName"));
                printf('<br /><img src="%s" style="width: 200px; height: 200px; float: left" />', $a->retrieve_value("PictureURL"));
                printf('<span class="medium-text">DOB:  %s<br /><br />Graduation Date:  %s<br /><br />Favorite Things: %s</span>', date_mysql_to_form($a->retrieve_value("DOB")), date_mysql_to_form($s->retrieve_value("GraduationDate")), $a->retrieve_value("FavoriteThings"));
                $db->close();
                exit();

?>
