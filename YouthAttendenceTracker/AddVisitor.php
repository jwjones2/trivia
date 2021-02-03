<?php
	// start a session to get the session variables
	session_start();
	
	/********************************************************
	 * AddVisitor.php
	 *
	 * Description:  Adds a visitor by inserting Attendee and
	 *	Student into tables and returning the FirstName, LastName
	 *	and ID packed for an AJAX response.  Also, add the visitor to 
	 *	the team of the person who brought them.  An addition return value
	 *	is returned indicating whether the student needs to be added to
	 *	the team; if they are an eligible visitor but already on a team
	 *	do not add them to the team again.
	 *	**First check that the visitor is not already in the DB.  If so
	 *	simply return the id and student name.
	 ********************************************************/
	 
	 /********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and send if not
	if ( $db->connect_error ) {
		echo 'error|There was a problem connecting to the database.';
		exit();  // cannot continue since no connection established
	}
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	// first double check that the visitor was not added accidentally and they are already in the db.  If they are in
	// there already, just add them to the service and return
	// check the db for name and db
	$query = 'SELECT ID, PictureURL FROM attendee WHERE FirstName="' . $_POST['FirstName'] . '" AND LastName="' . $_POST['LastName'] . '" AND DOB="' . $_POST['DOB'] . '";';
	if ( $results = $db->query($query) ) {
		if ( $results->num_rows > 0 ) {
			if ( $object = $results->fetch_object() ) {
				// there was a match so just add the student and return
				// Query the temp_signin table and add the student
				$query = "INSERT INTO temp_signin (ServiceID, AttendeeID) VALUES ('" . $_SESSION['service_id'] . "', '" . $object->ID  . "');";
				if ( $db->query($query) ) {
					// FIRST -- CHECK temp_remove FOR THE STUDENT...IF THERE REMOVE SO THEY CAN BE ADDED TO servicelist
					// ** Just call the delete statement on temp_remove to save steps and processing
					$result = $db->query("DELETE FROM temp_remove WHERE AttendeeID=" . $object->ID . " AND ServiceID=" . $_SESSION['service_id'] . ";");
					$r0 = $result->num_rows;  // store if there were any rows returned for possibly rolling back
			
					// check the attendee id value sent in POST to check if this student is being added as a visitor.
					// If aId is 0, not a visitor, else add them to visitor table (aId is the attendee that added if not 0)
					if ( isset($_POST['aId']) && $_POST['aId'] != 0 ) {
						$query = "INSERT INTO visitor (ServiceID, AttendeeID, VisitorID) VALUES ('" . $_SESSION['service_id'] . "', '" . $_POST['aId'] . "', '" . $object->ID . "');";
						if ( !$db->query($query) )  {
							// failed to add the visitor so roll back the insert into temp_signin and add back to temp_remove
							$query = "DELETE FROM temp_signin WHERE ServiceID = '" . $_SESSION['service_id'] . "' AND AttendeeID = '" . $object->ID . "';";
							if ( !$db->query($query) )
								echo "error|Critical Error with Adding Student and Visitor...please check Database integrity.";
								
							if ( $r0 > 0 ) {  // they was in temp_remove so add back
								$query = "INSERT INTO temp_remove (ServiceID, AttendeeID) VALUES ('" . $_SESSION['service_id'] . "', '" . $object->ID  . "');";
								if ( !$db->query($query) )
									echo "error|Critical Error with Adding Student and Visitor...please check Database integrity.";
							}
								
							$db->close();
							exit();
						}
					}
			
					printf('student|%s|add|%s', $object->ID . '|' . $_POST['FirstName'] . '|' . $_POST['LastName'], $object->PictureURL);  // send back student data with ret[0] of student
					$db->close();
					exit();
				} else {
					echo "error|Could not add the visitor.";
				}
			}
		}
	}
	
	/***
	 * A NEW VISITOR *
	 *
	 * Get values from POST and build new Attendee and Student, add to service, and return results.
	 ***/

	// set value of SubDiscriminator for attendee type to be student
        $_POST['SubDiscriminator'] = "student";
        		
        // set the active field to true
        $_POST['Active'] = "yes";
        
        // set the start date to current date
        $_POST['StartDate'] = date('Y-m-d');
	
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
       			if ( $success ) {
				// Query the temp_signin table and add the student
				$query = "INSERT INTO temp_signin (ServiceID, AttendeeID) VALUES ('" . $_SESSION['service_id'] . "', '" . $id  . "');";
				if ( $db->query($query) ) {
			
					// check the attendee id value sent in POST to check if this student is being added as a visitor.
					// If aId is 0, not a visitor, else add them to visitor table (aId is the attendee that added if not 0)
					if ( isset($_POST['aId']) && $_POST['aId'] != 0 ) {
						$query = "INSERT INTO visitor (ServiceID, AttendeeID, VisitorID) VALUES ('" . $_SESSION['service_id'] . "', '" . $_POST['aId'] . "', '" . $id . "');";
						if ( !$db->query($query) )  {
							// failed to add the visitor so roll back the insert into temp_signin and add back to temp_remove
							$query = "DELETE FROM temp_signin WHERE ServiceID = '" . $_SESSION['service_id'] . "' AND AttendeeID = '" . $id. "';";
							if ( !$db->query($query) )
								echo "error|Critical Error with Adding Student and Visitor...please check Database integrity.";
							
						
							$db->close();
							exit();
						}
					}	
			
					printf('student|%s|add|%s', $id . '|' . $_POST['FirstName'] . '|' . $_POST['LastName'], "NULL");  // send back student data with ret[0] of student
					$db->close();
					exit();
				} else {
					$error = 'Could not add student';
				}
			} else {
				$error = 'Could not insert student';
			}
		} else {
			$error = 'Could not set student id' . $db->insert_id;
		}
	} else {
		$error = $a->get_error_message();   // if the query was not successful then get the error message generated by the Attendee class
	}
        
        // if the ID and name was not output and the script exit, then output an error message
        printf('error|%s', $error);

?>