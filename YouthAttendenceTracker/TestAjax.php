<?php
	/********************************************************
	 * AddVisitor.php
	 *
	 * Description:  Adds a visitor by inserting Attendee and
	 *	Student into tables and returning the FirstName, LastName
	 *	and ID packed for an AJAX response.
	 ********************************************************/

	 
	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and set $error if not
	if ( $db->connect_error ) {
		$error = "There was a problem connecting to the database.";
	}
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	// set value of SubDiscriminator for attendee type to be student
        $_POST['SubDiscriminator'] = "student";
        		
        // set the active field to true
        $_POST['Active'] = "yes";
	
	/**  
            NOW, Create a new Attendee and student object and then call
              functions to execute the bound query insertion into db.
         **/
        $attendee = new Attendee($db, $_POST);
        $student = new Student($db, $_POST);
        
        // check that start date was default and if so set to current date
        $default_date = date("Y") . "-1-1";  // XXXX-1-1
        		
        if ( $_POST['DOB'] == $default_date )  // DOB has not been changed in the form
        {
        	echo 'error|DOB of visitor must be entered';  // echo an error message
        	exit();                                       // and exit to prevent further output
        }
		        		
        // Call execute bound query to insert attendee into db with a bound query
        $success = $attendee->execute_bound_query();

	// if query was successful then proceed to get the ID and insert student
	if ( $success )
    	{
        	// set ID of student from Attendee and insert student
       		// using the ID
       		$id = $db->insert_id;
       		$success = $student->set_id_value($id);
        			
       		// check that student's ID was set with no errors
        	if ( $success ) 
        	{
        		$success = $student->execute_bound_query();  // execute the query
        				
        		// check that query was successful and student was entered
       			if ( $success ) 
       			{
       				// If successful, output: ID|FirstName LastName
       				printf('%d|%s %s', $id, $_POST['FirstName'], $_POST['LastName']);
       				exit();  // exit so no further output is written
       			}
       			else
       				$error = 'Could not insert student';
       		}
       		else
       			$error = 'Could not set student id' . $db->insert_id;
        }
        else
        	$error = 'Could not insert attendee.';
        
        // if the ID and name was not output and the script exit, then output an error message
        printf('error|%s', $error);
?>
