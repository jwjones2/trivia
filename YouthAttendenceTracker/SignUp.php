<?php
	/********************************************************
	 * SignUp.php
	 *
	 * Description:  Requires a GET variable TeamID to be set
	 *	to the ID of a team in the team table.  This page displays
	 *	a sign-up sheet for a Service and is self-posting,
	 *	self-validating, redirecting the user to Home.html
	 *	on success.  Success occurs when the user submits the form
	 *	and all of the students counted "here" are inserted into 
	 *	the ServiceList table.
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
	
	// set the default timezone for date functions
	date_default_timezone_set("US/Central");	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
        <title>Service Team Sign Up</title>
        
        <!--favicon icon-->
        <link rel="shortcut icon" href="./favicon.ico" />
   
        <!-- Metadata Section:  Description of Page and Keywords for Site. -->
        <meta name="description" content="" />
        <meta name="keywords" content="" />
   
        <!-- Link to Stylesheet -->
        <link rel="stylesheet" href="./main.css" type="text/css" />
        
        <!-- Link to Javascript code for page -->
        <script type="text/javascript" language="javascript" src="./main.js"> </script>
        <script type="text/javascript" language="javascript" src="./AJAX.js"> </script>
        <script type="text/javascript" language="javascript" src="./AjaxFunctions.js"> </script>
        <script type="text/javascript" language="javascript">
        
        </script>
        
    </head>
    
    <body class="signup_body">
    	  <!-- MENU SECTION -->
    	  <?php
    	  	write_menu();
    	  ?>
		
    	  <?php   	  		
    	  	/***************************************************************
    	  	 * PHP Functionality
    	  	 *
    	  	 * (1) Loop over all teams in TeamID[] (the form element submitted
    	  	 *     from the multi-select box with each TeamID selected to signup).
    	  	 * (2) Get the name of the team by querying the table team with TeamID.
    	  	 * (3) Get a list of the team members' IDs from the teamlist table.
    	  	 *     2a.  Use IDs to retrieve student info from Attendee table.
    	  	 * (4) Results of db queries will be used to build the table that will
    	  	 *     be the sign-up form that makes up the page.
    	  	 * (5) Upon submission:  
    	  	 *     5a. Use the IDs of each student to match their submitted signup
    	  	 *         data by using the formula ID_fieldname.  
    	  	 *     5b. First, check that student was here, then match their signup
    	  	 *	   data fields and retrieve the values to use in building a 
    	  	 *	   query to insert them into the ServiceList table.
    	  	 *     5c. Perform the query on each student iteratively catching any
    	  	 *	   errors.
    	  	 *     5d. Success: Redirect to Home page.  Failure:  Display errors.
    	  	 * (6) Also, lastly any extra points data for a team to the servicepoints table.
    	  	 *  **Future updates: Use transactions to submit the individual students
    	  	 *    committing if all succeeded, roll back otherwise.  May not be an
    	  	 *    issue, however since there are many individual queries that are 
    	  	 *    being submitted as part of the whole signup submission, it is important
    	  	 *    to catch any failed submissions as it may be hard to notice if they 
    	  	 *    failed.
    	  	 **************************************************************/
    	  	// Loop over TeamID array -- all the team ids submitted for signup --
    	  	// and build the multi-dementional array teams.
    	  	
    	  	// Make teams multi-dementional array
    	  	$teams = array();  // to hold student's data, Multidimentional array, first demention is TeamID index
    	  	
    	  	foreach ( $_POST['TeamID'] as $teamId )  // loop over TeamIDs passed from calling page in TeamID $_POST variable as array
    	  	{   	  	
    	  		// Get the name of the team with TeamID
    	  		$query = 'SELECT Name FROM team WHERE ID=' . $teamId . ';';  // build the query string
    	  		$team_name = "";  // to hold the name of the team
    	  		if ( $result = $db->query($query) )
    	  		{
    	  			while ( $obj = $result->fetch_object() ) 
    	  			{
    	  				$team_name = $obj->Name;  // get the name from the result object and assign to team_name variable
    	  			}
    	  		}
    	  		
    	  		$teams[$team_name] = array();  // multi-dementional array indexed by team name to hold team's members' data
    	  		
    	  		// (2) Retrieve students info from Attendee table with ID retrieved
    	  		$query = 'SELECT attendee.ID, FirstName, LastName, NickName FROM attendee, teamlist WHERE TeamID=' . $teamId . ' AND AttendeeID = attendee.ID ORDER BY LastName;';  // build the query
    	  		
    	  		
    	  		// query table and get results to build the array of teams
    	  		if ( $result = $db->query($query) ) 
    	  		{
    	  			while ( $row = $result->fetch_array() ) 
    	  			{
    	  				$teams[$team_name][] = array($row[0], $row[1], $row[2], $row[3], $teamId); 
    	  			}
    	  		}
    	  		
    	  	}  // end loop over TeamIDs
    	  	
    	  	
    	  	/*** IF FORM IS SUBMITTED, DO SUBMIT ***/
    	  	/***************************************************************
    	  	 * Submit Signup Sheets
    	  	 *
    	  	 * Description of functionality:  
    	  	 * 	(1) Insert a Service entry into the Service table and get ID returned from query.
    	  	 *	(2) Loop over all students on Signup form and build queries and insert into Service_List table.
    	  	 *		2a. Build Query by getting fields submitted by formula AttendeeId_fieldname.
    	  	 *		2b. Insert Query
    	  	 *		2c. Log results.
    	  	 **************************************************************/
    	  	if ( isset($_POST['_submit_check']) )
    	  	{
    	  		// variables for logging results
    	  		$students_signed_in = 0;     // keep track of number of students successfully signed in by entering into db
    	  		$students_failed_submit = 0; // keep track of how many students db queries failed
    	  		$results = "";               // result string to send logged results to next page
    	  		
        		// (1) Insert Service into Service table and get ID for next queries on Service_List table
        		$query = 'INSERT INTO service (Date, Title, EventType) VALUES ("' . $_POST['Date'] . '", "' . $_POST['Title'] . '", "' . $_POST['EventType'] . '");';
        		
        		// If Query is successful do step (2) Building and Inserting Student queries on servicelist table, else set error
        		if ( $db->query($query) )
        		{
        			// first, get insert ID for use in following queries
        			$serviceID = $db->insert_id;
        			
        			// (2a) Build query and (2b) Insert query by looping over the students stored in multi-dementional array in teams
        			foreach ( array_keys($teams) as $t ) 
        			{
        				// need to loop over array in $t for each student on each team
        				foreach ( $teams[$t] as $student )
        				{
        					/* Build each students query */
        					// start query
        					$query = 'INSERT INTO servicelist (ServiceID, AttendeeID, SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors) VALUES ("'; 
        					
        					// Get the attributes of each student from $_POST variables:  in format StudentID_attribute
        					$here = $student[0] . '_Here';
        					$sunsch = $student[0] . '_SunSch';
        					$sunam = $student[0] . '_SunAM';
        					$sunpm = $student[0] . '_SunPM';
        					$bible = $student[0] . '_Bible';
        					$visitors = $student[0] . '_Visitors';
        				       					
        					// now check for here attribute:  if exists then check the other values to set defaults if not set
        					if ( isset($_POST[$here]) )  // student was present in service
        					{        						
        						// set defaults of attributes if not set
        						if ( !isset($_POST[$sunsch]) )
        							$_POST[$sunsch] = 'no';
        						else  // if isset then change value to yes since it is 'on' instead of 'yes'
        							$_POST[$sunsch] = 'yes';
        						
        						if ( !isset($_POST[$sunam]) )
        							$_POST[$sunam] = 'no';
        						else  // if isset then change value to yes since it is 'on' instead of 'yes'
        							$_POST[$sunam] = 'yes';
        						
        						if ( !isset($_POST[$sunpm]) )
        							$_POST[$sunpm] = 'no';
        						else  // if isset then change value to yes since it is 'on' instead of 'yes'
        							$_POST[$sunpm] = 'yes';
        						
        						if ( !isset($_POST[$bible]) )
        							$_POST[$bible] = 'no';
        						else  // if isset then change value to yes since it is 'on' instead of 'yes'
        							$_POST[$bible] = 'yes';
        						
        						if ( !isset($_POST[$visitors]) )
        							$_POST[$visitors] = 0;
        						
        					}
        					else   // student was not present so continue to next iteration of the loop for next student
        						continue;
        						
        					// build remainder of query
        					$query .= $serviceID . '", "' . $student[0] . '", "' . $_POST[$sunsch] . '", "' . $_POST[$sunam] . '", "' . $_POST[$sunpm] . '", "' . $_POST[$bible] . '", "' . $_POST[$visitors] . '");';
        				
        					// query the db and log the results
        					if ( $db->query($query) )
        						$students_signed_in++;     // increment counter of successful db entries
        					else 
        						$students_failed_submit++; // increment counter of failed queries
        					
        				}  // end student loop
        				
        				/* NOW, insert any extra points for the team into servicepoints */
        				$tID = $teams[$t][0][4];  // get the teamId to use in query
        				if ( $_POST['extraPointsValue_' . $tID] )
        				{
        					if ( $_POST['extraPointsValue_' . $tID] != "" )
        					{
        						// create query and insert
        						$query = 'INSERT INTO servicepoints (ServiceID, TeamID, Points, Description) VALUES (' . $serviceID . ', ' . $tID . ', ' . $_POST['extraPointsValue_' . $tID] . ', "' . $_POST['extraPointsDescription_' . $tID] . '");';
        						$db->query($query);
        					}
        				}
        				
        			}  // end team loop
        			
        			/*** Now, redirect to summary page if no errors in service creation and since all students have been processed ***/
        			// build url to redirect with GET variables for logged results
        			// first build results
        			$results .= 'Total number of students signed in:  ' . $students_signed_in . '__Total number of failed database queries:  ' . $students_failed_submit;
        			$url = 'Location: ./SigninSummary.php?results=' . $results;
        			
        			/* Redirect browser */
        			header($url);
        			/* Make sure that code below does not get executed after redirect. */
        			//exit;
        			
        		}
        		else
        			$error .= "Error:  Could not create Service listing in service table; had to quit.  Please try again.  No Students signed in!";
        	}
		
         
        	// print the error message if it exists
    	  	if ( $error != "" ) 
    	  		echo "<h1 class='red'>$error</h1>";
        		
        ?>
        <!-- Sign Up Form section -->
        <div class="signup">
        
        	<!-- Signup Sheet table -->
        	
       	
        	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="signup_form" name="signup_form" onsubmit="return confirm('Submit Signup?  All points have been recorded?');">
        	
        	<?php
        		/***********************************************
        		 * PHP Functionality
        		 *
        		 * (1) First, Loop over each team and create team name header.
        		 * (2) Loop over members array for each team
        		 *     using each student's data to build the sign up row.
        		 **********************************************/
        		$field_counter = 0;  // to use to give unique ID for javascript function
        		$team_counter = 0;   // to use to keep track of which team is being written to page
        		$loop_counter = 0;   // to know when to start a new table
        		
        		// loop over teams using keys to get the names
        		foreach ( array_keys($teams) as $t_name )    // $t_name holds the name of the team for each iteration
        		{      	
        			if ( $loop_counter > 0 )
        				echo '</table>&nbsp;&nbsp;'; // end the previous table if not the first team written
        			$loop_counter++;         // increment the loop counter
        			
        			printf('<table border="1" cellspacing="0" cellpadding="0" class="signup_table" id="%s">', 'Team_' . $teams[$t_name][0][4]);  // write the start of the table
        			
        			echo '<!-- Column Heading Row -->';
        			echo '<!-- Header showing the team name -->';  
        			printf('<tr><td colspan="7" align="center"><div class="signup_header">"%s" SIGN-UP</div></td></tr>', $t_name);    // div - header of individual team with team name
        			echo '<tr class="signup_columnheader">';
        			echo '<td>Name</td>';
        			echo '<td>Here?<br />10,000</td>';
        			echo '<td>Sun. Sch<br />10,000</td>';
        			echo '<td>Sun. AM<br />10,000</td>';
        			echo '<td>Sun. PM<br />10,000</td>';
        			echo '<td>Bible<br />50,000</td>';
        			echo '<td>Visitors<br />100,000</td>';
        			echo '</tr>';
	        				
        			echo '<!-- Student Signup Rows -->';
	        				
        			foreach ( $teams[$t_name] as $data )  // $data is an array that holds the student's data 
        			{
        				echo '<tr>'; // start row
		        		        				
        				/* NAME */
        				// check first is NickName is NULL or not
        				if ( $data[3] == 'NULL' )  // No NickName so print FirstName LastName
        					printf('<td>%s %s</td>', $data[1], $data[2]);
        				else                       // NickName, so print FirstName "NickName" LastName
        					printf('<td>%s "%s" %s</td>', $data[1], $data[3], $data[2]);
        					
        				/* HERE */
        				printf('<td><input type="checkbox" name="%s" class="signup_checkbox" onclick="enableFields(%s);" /></td>', $data[0] . "_Here", $field_counter);
        					
        				/* SUN. SCH */
        				printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" /></td>', $data[0] . "_SunSch", ++$field_counter);  // pass field_counter as the id of the field to use with javascript enableFields function
        					
        				/* SUN. AM */
        				printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" /></td>', $data[0] . "_SunAM", ++$field_counter);
        					
        				/* SUN. PM */
        				printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" /></td>', $data[0] . "_SunPM", ++$field_counter);
        					
        				/* BIBLE */
        				printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" /></td>', $data[0] . "_Bible", ++$field_counter);
        						
        				/* VISITORS */
        				printf('<td><div id="%s" class="visitorsAdded"></div><input type="hidden" name="visitors" value="0" /><input type="button" id="%s" value="Add Visitor" disabled="disabled" onclick="showAddVisitor(%d, %d)" /></td>', "Visitor_" . ++$field_counter, $field_counter, $field_counter, $data[4]);
        				
        				/*
        				printf('<td><select name="%s" class="center" id="%s" disabled="disabled">', $data[0] . "_Visitors", ++$field_counter);
        				for ( $i = 0; $i < 11; $i++ )  // use loop to print 0-10 options for visitor select list
        					printf('<option value="%s">%s</option>', $i, $i);
        					}
        				echo '</td>';  // end the data element
        				*/
        				echo '</tr>'; // end row
        				
        				//printf('<tr id="VisitorAdd_%s"></tr>', $field_counter);
        				
        			}
        			
        			/* Write the extra points section in a separate table to help with visitor adding aesthetics, so that visitors
        			   are added at the end of the student rows instead of after the extra points section for the team.
        			 */
        			 printf('<tr><td class="red italic" align="right">Click to add a member to the team:  </td><td><input type="button" value="Add Visitor" onclick="showAddVisitor(%d, %d)" /></td></tr></table><table class="signup_table">', 0, $teams[$t_name][0][4]);
        		
        			/* Write the extra points row for each team to be able to add points for games or other in-service activities */
        			echo '<tr class="signup_columnheader"> <td>Extra Points Row</td> <td>Points</td> <td colspan="4">Description</td></tr>';
        			printf('<tr> <td class="bold italic">Extra Points</td> <td><input type="text" id="extraPointsValue_%1$s" name="extraPointsValue_%1$s" /></td> <td colspan="3"><input type="text" id="extraPointsDescription_%1$s" name="extraPointsDescription_%1$s" /></td></tr>', $data[4]);
        			
        			/* Store the last value of field counter for each team in a javascript variable for use in Adding visitor. */
        			printf('<script type="text/javascript" language="Javascript">fieldCounter = %s;</script>', $field_counter);
        		}
        	?>
        		
        	</table>  <!-- end the team tables  -->
        		&nbsp;&nbsp;
        	<table class="signup_table">   <!-- for extra footers -->
        		<!-- Footer/Date Row -->
        		<tr class="signup_footer">
        			<td colspan="7" align="center">
        				<span id="displayDate">
        					<?php 
        						// write the date
        						echo date('F j, Y'); 
        					?>
        				</span>
        				
        				<br />
        				
        				<input type="button" value="Enter a Custom Date" onclick="customDate();" />
        			</td>
        		</tr>
        		
        		<!-- Submit Button row -->
        		<tr>
        			<td colspan="7" align="center">
        				<input type="hidden" name="_submit_check" value="yes" /> <!-- To Verify the form was submitted -->
        				<input type="submit" value="Submit" id="submit" name="submit" /> <!-- Submit button -->
				</td>
        		</tr>
        		
        	</table>
        	
        	<!-- END FORM + Add extra info fields -->
        	<?php
        		// Recreate POST variable TeamID for resubmission by creating a series of input fields with
        		// the name TeamID[] and each value of the TeamID POST array.
        		foreach ( $_POST['TeamID'] as $ti ) 
        			printf('<input type="hidden" name="TeamID[]" value="%s" />', $ti);
        	?>
        	<input type="hidden" name="Date" id="Date" value="<?php echo date('Y-m-d'); ?>" />
        	<input type="hidden" name="Title" value="NULL" />
        	<input type="hidden" name="EventType" value="Youth Service" />
        	</form>
        </div>
        
        
        <!-- HOME LOGO SECTION -->
        <div class="homeLogoButton"><a href="./home.php"><img src="./site_images/Homelogo.gif" /></a></div>
        
        
        <!--  ADD VISITOR SECTION.  2 Divs for showing AddVisitor form and getting input.  -->
        <div id="popup" class="popupLocation">
        
        <div id="visitorDiv1" class="transparentBackground"> </div><!-- To put a transparent layer over page to prevent other input other than currently display form -->
           <div id="visitorDiv2" class="addVisitorFormSection">
        	<form id="addVisitorForm" name="addVisitorForm" class="addVisitorForm" action="./AddVisitor.php" method="post" onsubmit="return validateAttendeeForm()">
		<table cellpadding="3px">
                    	<!-- Header Row (2 rows) -->
                    			<tr>
                    				<th colspan="6" class="center bigText">Add A Visitor</th>
                    			</tr>
                    			<tr>
                    				<th colspan="6" class="center red italic">Enter a new visitor or select from the list of eligible visitors.</th>
                    			</tr>
			
			<!-- Row 1 -->
					<tr>
						<!-- Drop Down List to select a student that is already in DB or select "New Visitor" -->
						<td colspan="6" class="center">
							<select name="pickVisitor" id="pickVisitor">
								<option value="newVisitor">New Visitor</option>
								<?php
									/*******************************************
									 * Get Eligible visitors for drop-down list
									 *
									 * To do this:  Get all students from the attendee table by queries
									 * 	attendee table matching SubDiscriminator to "student"; Then
									 *    	query the servicelist and service tables to get service dates
									 *	the student was present and counting to see if student present
									 *	less than 3 times in last 6 months.  Eligible students are
									 *	written to the drop-down list with their IDs as values.
									 ******************************************/
									// build query to get all students
									$query = 'SELECT ID, FirstName, LastName, NickName FROM attendee WHERE SubDiscriminator = "student" ORDER BY LastName;';
									
									// counter to keep track of students' eligible attendence
									$counter;
									
									// query attendee DB and use each result (a student) to process that student by querying service and servicelist
									if ( $result = $db->query($query) ) 
									{
										while ( $object = $result->fetch_object() ) 
										{
											/*  Now process each student  */
											$counter = 0;  // clear counter for each student
											
											// build a new query to get the Dates where the students have been present
											$q = 'SELECT service.Date FROM service, servicelist WHERE servicelist.AttendeeID = ' . $object->ID . ';';
											
											if ( $r = $db->query($q) )
											{
												while ( $o = $r->fetch_object() ) 
												{
													/*  Start counting eligible dates  */
													$sd = explode('-', $o->Date); // split the date to process
													$years = $sd[0] - date('y'); // check if the date is not the current year
													if ( $years == 0 )
													{
														// same year as current so check months
														$months = $sd[1] - date('m');
														if ( $months < 6 )  // if service date more than 6 months old
															$counter++; // increment the counter
													}
												}
												
												// Check counter, if < 3, write the student
												if ( $counter < 3 )
												{
													// Check for NickName to write Nick name if not NULL
													if ( $object->NickName == 'NULL' ) 
														printf('<option value="%s|%s|%s">%s %s</option>', $object->ID, $object->FirstName, $object->LastName, $object->FirstName, $object->LastName);
													else
														printf('<option value="%s|%s|%s|%s">%s "%s" %s</option>', $object->ID, $object->FirstName, $object->LastName, $object->NickName, $object->FirstName, $object->NickName, $object->LastName);
												}
											}
											else
												echo '<option>Problem with quering Services</option>';
										}
									}
									else
										echo '<option>Problem with quering Attendee</option>';
								?>
							</select>
						</td>
					</tr>
			<!-- Row 2 -->
                    			<tr>
						<td class="col1">First Name</td>
						<td class="col2"><input type="text" id="FirstName" name="FirstName" maxlenth="30" size="15" value="<?php populate_form('FirstName'); ?>" /></td>		
						
						<td class="col3">Last Name</td>
						<td class="col4"><input type="text" id="LastName" name="LastName" maxlenth="30" size="15" value="<?php populate_form('LastName'); ?>" /></td>
						
						<td class="col5">Middle Initial</td>
						<td class="col6"><input type="text" id="MiddleName" name="MiddleName" size="1" maxlength="1" value="<?php populate_form('MiddleName'); ?>" /></td>
					</tr>
                    <!-- Row 3 -->
                    
					<tr>	
						<td class="col1">Nick Name</td>
						<td class="col2"><input type="text" id="NickName" name="NickName" size="15" maxlength="30" value="<?php populate_form('NickName'); ?>" /></td>	
						
						<td class="col3">Date of Birth</td>
						<td class="col4">
							<?php
								$a = get_month_init_values('dobMonth', 'dobDay', 'dobYear');
								
								selectBox("dobMonth", "dobMonth", "range", "1, 12", "not_set", $a[0]);
								selectBox("dobDay", "dobDay", "range", "1, 31", "not_set", $a[1]);
								$current_year = date("Y");
								selectBox("dobYear", "dobYear", "range", "$current_year, 1945", "not_set", $a[2]);
							?>
						</td>
						
						<td class="col5">Sex</td>
						<td class="col6">
						M<input type="radio" name="Sex" id="male" value="male" <?php is_selected('Sex', 'male'); ?> /> &nbsp;&nbsp;&nbsp;
						F<input type="radio" name="Sex" id="female" value="female" <?php is_selected('Sex', 'female'); ?> />
					</tr>		
                    <!-- Row 4 -->
					<tr>	
						<td class="col1">Email</td>
						<td class="col2"><input type="text" id="Email" name="Email" value="<?php populate_form('CellPhone'); ?>" /></td>
						
						<td class="col3">Cell Phone</td>
						<td class="col4"><input type="text" id="CellPhone" name="CellPhone" size="10" maxlength="10" value="<?php populate_form('CellPhone'); ?>" /></td>	
						
						<td class="col5">Receive Texts?</td>
						<td class="col6"><input type="checkbox" id="CanReceiveTxt" name="CanReceiveTxt" <?php is_selected_checkbox('CanReceiveTxt'); ?> /></td>	
					</tr>	
                    <!-- Row 5-->	
                    			<tr>	
						<td class="col1">Facebook Page?</td>
						<td class="col2"><input type="checkbox" id="Facebook" name="Facebook" <?php is_selected_checkbox('Facebook'); ?> /></td>
						
						<td class="col3">Street Address</td>
						<td class="col4"><input type="text" id="StreetAddress" name="StreetAddress" value="<?php populate_form('StreetAddress'); ?>" /></td>
						
						<td class="col5">Zip</td>
						<td class="col6">
							<select name="Zip" id="Zip">
								<?php                
									select_box_check('Zip');
									
									// query the zip codes from the Zipcode table 
									$query = "SELECT * FROM Zipcode";    // construct the query
    	  								if ( $result = $db->query($query) )  // query the db and get the result object (mysqli_result)
    	  								{  
    	  									while ( $data = $result->fetch_object() ) 
    	  									{  // get row as object
    	  										echo "<option value='$data->Value'>$data->Value</option>";  // write the value of row as option tag
    	  									}
    	  								}
		    	  			
    	  							?>
    	  						</select>
    	  					</td>
					</tr>
                  
                    <!-- Row 6 -->	
                    			<tr>	
						<td class="col1">Notes</td>
						<td class="col2" colspan="5"><textarea rows="1" cols="80" id="Notes" name="Notes"><?php populate_form('Notes'); ?></textarea></td>		
					</tr>
                    
                    <!-- Row 7 -->	
                    			<tr>	
                    				<td class="center" colspan="3"><input type="button" value="Cancel" onclick="hideAddVisitor()" /></td>
						<td class="center" colspan="3"><input type="button" value="Add" id="submit" name="submit" onclick="addVisitorAjax();" /></td>			
					</tr>
			</table>
				
			<input type="hidden" name="_submit_check" value="1" />
		</form>
        </div>  
        
        </div>  <!--  END ADD VISITOR FORM SECTION  -->
        
        <?php
        	// Handle any errors:  Create a javascript function to alert user
        	//  	of errors and then highlight fields that need attention.
        	if ( isset($failed) && $failed == true)  // failed is true so need to handle errors
        	{
        		$errors = parse_error_message($attendee->get_error_message());
        		
        		echo '<script type="text/javascript" language="Javascript">';
        		echo 'alert("';
        		
        		foreach ( $errors as $el ) 
        		{
        			if ( isset($el[0]) && isset($el[1]) ) 
        				printf('(Field Name:  %s) %s.', $el[0], $el[1]);
        		}
        		
        		if ( isset($error_message) )
        			printf('  Error:  %s  ', $error_message);
        		
        		echo '");';
        		
        		foreach ( $errors as $el ) 
        		{
        			if ( isset($el[0]) )
        				printf('document.getElementById("%s").style.backgroundColor = "Yellow";', $el[0]);
        		}
        		echo '</script>';
        	}
        ?>
       
    </body>
</html>
