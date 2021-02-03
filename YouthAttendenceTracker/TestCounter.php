<?php
require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and set $error if not
	if ( $db->connect_error ) {
		echo '<h1 style="color:Red">There was a problem connecting to the database.</h1>';
		$db->close();
		exit();
	}
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	// set the default timezone for date functions
	date_default_timezone_set("US/Central");
// build query to get all students
									   $query = 'SELECT ID, FirstName, LastName, NickName FROM attendee WHERE SubDiscriminator = "student" ORDER BY LastName;';
                                                                           
									
									   // counter to keep track of students' eligible attendence
									   $counter;
									
									   // query attendee DB and use each result (a student) to process that student by querying service and servicelist
									   if ( $result = $db->query($query) ) {
										while ( $object = $result->fetch_object() )  {
											/*  Now process each student  */
											$counter = 0;  // clear counter for each student
											
											// build a new query to get the Dates where the students have been present
                                                                                        $current_month = date('m');
                                                                    
                                                                                        if ( $current_month <= 6 ) {
                                                                                            $current_month = 12 - ($current_month - 6);
                                                                                            // decrement the year
                                                                                            $current_year = date('Y') - 1;
                                                                                        } else {
                                                                                            $current_month -= 6;
                                                                                            $current_year = date('Y');
                                                                                        }
                                                                                        
											$q = 'SELECT t2.AttendeeID FROM service as t1 INNER JOIN servicelist as t2 ON t1.ID = t2.ServiceID WHERE t1.Date >= "' . $current_year . '-' . $current_month . '-' . date('d') . '";';
                                                                                        
											
											if ( $r = $db->query($q) )
											{
												while ( $o = $r->fetch_object() ) 
												{
													// count how many times student was present in last 6 months
                                                                                                        if ( $object->ID == $o->AttendeeID )
                                                                                                            $counter++;
												}
												
												// Check counter, if < 3, write the student
												if ( $counter < 3 )
												{
														printf('<h1>**%s** %s %s</h1>', $counter, $object->FirstName, $object->LastName);
												}
											}
											else
												echo 'pickVisitorContent += \'<option>Problem with quering Services</option>\';';
										}
									   } else {
										echo 'pickVisitorContent += \'<option>Problem with quering Attendee</option>\';';
									   }
                                                                           
    ?>