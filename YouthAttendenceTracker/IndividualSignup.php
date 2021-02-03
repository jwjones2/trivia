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
	  
		/******************
		 * MOD -- FOR CALLING WITH SERVICE ID TO REPOPULATE A SERVICE
		 *
		 * If Get 'ID' is set then get all signup info from DB and build array.
		 *
		 * Functionality
		 * 1. Query servicelist and Attendee to get signup data
		 * 2. store in Multi-dementional array
		 ******************/
		if ( isset($_GET['ID']) && $_GET['ID'] != "" ) {
			// array for populating data
			$students_signin = array();
			
			$query = "SELECT t2.FirstName, t2.LastName, t2.ID, t1.SunSchAttend, t1.SunMornAttend, t1.SunEvenAttend, t1.Bible, t1.Visitors, t1.extrapoints FROM servicelist as t1 INNER JOIN attendee as t2 ON t1.AttendeeID = t2.ID WHERE t1.ServiceID = $id ORDER BY t2.LastName;";
			
			if ( $result = $db->query($query) ) {
				while ( $obj = $result->fetch_object() ) {
					// build array
					$students_signin[$obj->ID] = array($obj->FirstName, $obj->LastName, $obj->SunSchAttend, $obj->SunMornAttend, $obj->SunEvenAttend, $obj->Bible, $obj->Visitors, $obj->extrapoints);
				}
			}
		}
		
		
    	  	
    	  	// Make teams multi-dementional array
    	  	$students = array();  // to hold student's data, Multidimentional array, first demention is TeamID index
	
    	  	// (2) Retrieve students info from Attendee table with ID retrieved
    	  	$query = 'SELECT ID, FirstName, LastName, NickName FROM attendee WHERE Active="yes" ORDER BY LastName;';  // build the query
    	  		
    	  	
    	    // query table and get results to build the array of teams
    	  	if ( $result = $db->query($query) ) {
				while ( $row = $result->fetch_array() ) 
    	  				$students[] = array($row[0], $row[1], $row[2], $row[3]);   // array: ID, FirstName, LastName, NickName
    	  	} else
				printf('<div class="errormessage">PROBLEM WITH QUERY:  %s</div><hr />', $query);
    	  	
    	  	
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
        		$query = 'INSERT IGNORE INTO service (Date, Title, EventType) VALUES ("' . $_POST['Date'] . '", "' . $_POST['Title'] . '", "' . $_POST['EventType'] . '");';
        		
        		// If Query is successful do step (2) Building and Inserting Student queries on servicelist table, else set error
        		if ( $db->query($query) )
        		{
        			// first, get insert ID for use in following queries
        			$serviceID = $db->insert_id;
        			
        			// (2a) Build query and (2b) Insert query by looping over the students stored in multi-dementional array in teams
        			foreach ( $students as $student ) 
        			{
        				/* Build each students query */
        				// start query
        				$query = 'INSERT INTO servicelist (ServiceID, AttendeeID, SunSchAttend, SunMornAttend, SunEvenAttend, Bible, Visitors, extrapoints) VALUES ("'; 
        					
        				// Get the attributes of each student from $_POST variables:  in format StudentID_attribute
        				$here = $student[0] . '_Here';
       					$sunsch = $student[0] . '_SunSch';
       					$sunam = $student[0] . '_SunAM';
       					$sunpm = $student[0] . '_SunPM';
        				$bible = $student[0] . '_Bible';
        				$visitors = $student[0] . '_Visitors';
        				$ex_points = $student[0] . '_ExtraPoints';
        				       					
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
        						
        					// UPDATE -- ADD EXTRA POINTS ON EACH STUDENT
        					if ( !isset($_POST[$ex_points]) )
        						$_POST[$ex_points] = 0;
        				}
        				else   // student was not present so continue to next iteration of the loop for next student
        					continue;
        						
        				// build remainder of query  
        				$query .= $serviceID . '", "' . $student[0] . '", "' . $_POST[$sunsch] . '", "' . $_POST[$sunam] . '", "' . $_POST[$sunpm] . '", "' . $_POST[$bible] . '", "' . $_POST[$visitors] . '", "' . $_POST[$ex_points] . '");';
        				
					printf('EXTRA POINTS:  %s<hr />', $_POST[$ex_points]);
					
        				// query the db and log the results
       					if ( $db->query($query) )
        					$students_signed_in++;     // increment counter of successful db entries
        				else 
        					$students_failed_submit++; // increment counter of failed queries
        					
        			}  // end student loop
        			
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
        	}// end POST check
		
         
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
		?>     
        			
		
		<table border="1" cellspacing="0" cellpadding="0" id="signup_table" id="Team_0">
				
			<tr>
				<td colspan="8" align="center"><div class="signup_header">INDIVIDUAL SIGN-UP</div></td>
			</tr>
			<tr class="signup_columnheader">
				<td>Name</td>
				<td>Here?<br />10,000</td>
				<td>Sun. Sch<br />10,000</td>
				<td>Sun. AM<br />10,000</td>
				<td>Sun. PM<br />10,000</td>
				<td>Bible<br />50,000</td>
				<td>Visitors<br />100,000</td>
				<td>Extra Points</td>
			</tr>
	        				
       		<!-- Student Signup Rows -->
	        	
		<?php
	        	/***
	        	 * THE STUDENT ROWS
	        	 ***/
        		foreach ( $students as $data )  // $data is an array that holds the student's data 
        		{
        			echo '<tr>'; // start row
		        		        				
        			/* NAME */
        			// check first is NickName is NULL or not
        			if ( $data[3] == 'NULL' )  // No NickName so print FirstName LastName
        				printf('<td>%s %s</td>', $data[1], $data[2]);
        			else                       // NickName, so print FirstName "NickName" LastName
       					printf('<td>%s "%s" %s</td>', $data[1], $data[3], $data[2]);
        					
        			/* HERE */
        			printf('<td><input type="checkbox" name="%s" id="%s" class="signup_checkbox" onclick="enableFields(%s); here(\'%s\');" /></td>', $data[0] . "_Here", $data[0] . "_Here", $data[0], $data[0]);
        					
        			/* SUN. SCH */
        			printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" onclick="clickSignIn(\'%s\', \'_SunSch\')" /></td>', $data[0] . "_SunSch", $data[0] . "_SunSch", $data[0]);  // pass field_counter as the id of the field to use with javascript enableFields function
        					
        			/* SUN. AM */
        			printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" onclick="clickSignIn(\'%s\', \'_SunAM\')" /></td>', $data[0] . "_SunAM", $data[0] . "_SunAM", $data[0]);
        					
        			/* SUN. PM */
        			printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" onclick="clickSignIn(\'%s\', \'_SunPM\')" /></td>', $data[0] . "_SunPM", $data[0] . "_SunPM", $data[0]);
        					
        			/* BIBLE */
        			printf('<td><input type="checkbox" name="%s" class="signup_checkbox" id="%s" disabled="disabled" onclick="clickSignIn(\'%s\', \'_Bible\')" /></td>', $data[0] . "_Bible", $data[0] . "_Bible", $data[0]);
        						
        			/* VISITORS */
        			printf('<td><div id="%s" class="visitorsAdded"></div><input type="hidden" id="%s" name="%s" value="0" /><input type="button" id="%s" value="Add Visitor" disabled="disabled" onclick="showAddVisitor(%d, %d)" /></td>', "Visitor_" . $data[0], $data[0] . "_Visitors", $data[0] . "_Visitors", $data[0] . "_Visitor", $data[0], $data[4]);
        				
        			/*** UPDATE ADD EXTRA POINTS
        			 ***/
        			/* EXTRA POINTS */
        			printf('<td><input type="text" name="%s" class="signup_textbox" value="0" id="%s" disabled="disabled" onchange="clickSignIn(\'%s\', \'_ExtraPoints\')" /></td>', $data[0] . "_ExtraPoints", $data[0] . "_ExtraPoints", $data[0]);


        			/***** REMOVED
        			printf('<td><select name="%s" class="center" id="%s" disabled="disabled">', $data[0] . "_Visitors", ++$field_counter);
       				for ( $i = 0; $i < 11; $i++ )  // use loop to print 0-10 options for visitor select list
       					printf('<option value="%s">%s</option>', $i, $i);
        				}
        			echo '</td>';  // end the data element
        			******/
        			
        			
        			echo '</tr>'; // end row	
        		}
        			
        			/* Write the extra points section in a separate table to help with visitor adding aesthetics, so that visitors
        			   are added at the end of the student rows instead of after the extra points section for the team.
        			 */
					printf('<tr><td class="red italic" align="right">Click to add a vistor:  </td><td><input type="button" value="Add Visitor" onclick="showAddVisitor(%d, %d)" /></td></tr></table><table class="signup_table">', 0, $teams[$t_name][0][4]);
        		
        			/* Write the extra points row for each team to be able to add points for games or other in-service activities */
        			/*echo '<tr class="signup_columnheader"> <td>Extra Points Row</td> <td>Points</td> <td colspan="4">Description</td></tr>';
        			printf('<tr> <td class="bold italic">Extra Points</td> <td><input type="text" id="extraPointsValue_%1$s" name="extraPointsValue_%1$s" /></td> <td colspan="3"><input type="text" id="extraPointsDescription_%1$s" name="extraPointsDescription_%1$s" /></td></tr>', $data[4]);
        			*/
        			/* Store the last value of field counter for each team in a javascript variable for use in Adding visitor. */
        			printf('<script type="text/javascript" language="Javascript">fieldCounter = %s;</script>', $field_counter);
        		
        	?>
        		
        	</table>  <!-- end the team tables  -->
        		&nbsp;&nbsp;
        	<table class="footer_table">   <!-- for extra footers -->
        		<!-- Footer/Date Row -->
        		<tr class="signup_footer">
        			<td colspan="8" align="center">
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
 
        	
        	<input type="hidden" name="Date" id="Date" value="<?php echo date('Y-m-d'); ?>" />
        	<input type="hidden" name="Title" value="NULL" />
        	<input type="hidden" name="EventType" value="Youth Service" />
        	</form>
        </div>
        
        
        <!-- HOME LOGO SECTION -->
        <div class="homeLogoButton"><a href="./home.php"><img src="./site_images/Homelogo.gif" /></a></div>
        
	
        <!--  ADD VISITOR SECTION.  -->
        <?php include('add-visitor-form.php'); ?>
        
	
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
