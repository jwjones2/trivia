<?php
	/********************************************************
	 * AddAttendee.php
	 *
	 * Description:  A form to add a new student.  Self-posting.
	 * 	Inserts data for attendee and student tables.
	 ********************************************************/

	// output the header
	$title = "Add a New Student";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
?>

		
    	  <?php
    	  		// print the error message if it exists
    	  		if ( $error != "" ) 
    	  			echo "<h1 class='red'>$error</h1>";

    	  	/*** IF FORM IS SUBMITTED, DO SUBMIT ***/
    	  	if ( isset($_POST['_submit_check']) )
    	  	{
        		/**
        		 * Insert the new Attendee that was posted to page or
        		 * print an error that will redirect them back to previous page 
        		 * or the main menu
        		 * FIRST HOWEVER, must validate the data and escape any malicious
        		 *   or erroneous characters in data.
        		 * If successful, then print the NewStudent form
        		 **/
        		 
        		// further prepare the form elements for creating the SQL query
        		// All dates in form need to be condensed to YYYY-MM-DD format,
        		// added to the clean array and then the month, day, and year elements unset
        		// *Use build_date_from_form to do this.
        		//** HANDLE DEFAULT DATES:  If date is 01 01 YYYY (Where YYYY is current year) then it
        		//   has not been altered and needs to be set to 0000-00-00 as the default date so that
        		//   it is not mistaken in the future for valid data (it is esentially blank).
        		$_POST['DOB'] = build_date_from_form($_POST, 'dobYear', 'dobMonth', 'dobDay');
        		$_POST['ReleaseFormUpdate'] = build_date_from_form($_POST, 'releaseYear', 'releaseMonth', 'releaseDay');
        		$_POST['DateSaved'] = build_date_from_form($_POST, 'dateSavedYear', 'dateSavedMonth', 'dateSavedDay');
        		$_POST['DateBaptized'] = build_date_from_form($_POST, 'dateBaptizedYear', 'dateBaptizedMonth', 'dateBaptizedDay');
        		$_POST['DateHolyGhost'] = build_date_from_form($_POST, 'dateHolyGhostYear', 'dateHolyGhostMonth', 'dateHolyGhostDay');
        		$_POST['StartDate'] = build_date_from_form($_POST, 'startDateYear', 'startDateMonth', 'startDateDay');
        		$_POST['GraduationDate'] = build_date_from_form($_POST, 'gradDateYear', 'gradDateMonth', 'gradDateDay');
        		
        		// check that start date was default and if so set to current date
        		$default_date = date("Y") . "-1-1";  // XXXX-1-1
        	
        		// **HANDLE UNCHANGED DATES.
        		if ( $_POST['StartDate'] == $default_date )  // Start date is set to current date if unchanged
        			$_POST['StartDate'] = date('Y-m-d');
        		
        		if ( $_POST['ReleaseFormUpdate'] == $default_date ) 
        			$_POST['ReleaseFormUpdate'] = '0000-00-00';
        		
        		if ( $_POST['DateSaved'] == $default_date ) 
        			$_POST['DateSaved'] = '0000-00-00';
        		
        		if ( $_POST['DateBaptized'] == $default_date ) 
        			$_POST['DateBaptized'] = '0000-00-00';
        		
        		if ( $_POST['DateHolyGhost'] == $default_date ) 
        			$_POST['DateHolyGhost'] = '0000-00-00';
        		
        		if ( $_POST['GraduationDate'] == '2020-1-1' )  // use different default date for GraduationDate 
        			$_POST['GraduationDate'] = '0000-00-00';
        		
        		// check that DOB was entered, do not allow to submit a default date as DOB
        		$dob_entered = false;  // set to true if a DOB was entered, i.e. not equal to default date
        		
        		if ( $_POST['DOB'] != $default_date )  // DOB has been changed in the form 
        			$dob_entered = true;           // set to true so the form will submit
        		        		
        		// set value of SubDiscriminator for attendee type to be student
        		$_POST['SubDiscriminator'] = "student";
        		
        		// set the active field to true
        		$_POST['Active'] = "yes";
        		
        		/**
        		    GET the Picture from the file upload from the form for the Profile Picture.
        		 **/	
        		 // first test that file is right type and not too large
        		 if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg")) && ($_FILES["file"]["size"] < 50000))
        		 {
        		 	 // check for file errors
        		 	 if ($_FILES["file"]["error"] > 0)
        		 	 {
        		 	 	 echo "Return Code: " . $_FILES["file"]["error"] . "<br />";  // print error
        		 	 }
        		 	 else
        		 	 {
        		 	 	// Check to make sure file doesn't already exist 
        		 	 	 if (file_exists("upload/" . $_FILES["file"]["name"]))
        		 	 	 {
        		 	 	 	 echo $_FILES["file"]["name"] . " already exists. ";  // print error
        		 	 	 }
        		 	 	 else
        		 	 	 {
        		 	 	 	 // Now, move file from tmp directory to the right location on the server
        		 	 	 	 move_uploaded_file($_FILES["file"]["tmp_name"], "profile_pictures/" . $_FILES["file"]["name"]);
        		 	 	         		 	 	 	 
        		 	 	 	 // Set the $_POST value for the PictureURL to insert into the database
        		 	 	 	 $_POST['PictureURL'] = "profile_pictures/" . $db->real_escape_string($_FILES['file']['name']);
        		 	 	 }
        		 	 }
        		 }
        		 else
        		 {
        		 	 // echo "Invalid file";  // print error
        		 }
        		 
        		        		
        		/**  
        		    NOW, Create a new Attendee and student object and then call
        		      functions to execute the bound query insertion into db.
        		 **/
        		$attendee = new Attendee($db, $_POST);
        		$student = new Student($db, $_POST);
		        		
        		// Call execute bound query to insert attendee into db with a bound query
        		// *FIRST CHECK THAT DOB WAS ENTERED
        		if ( $dob_entered ) 
        			$success = $attendee->execute_bound_query();
        		else
        		{
        			// set success to false
        			$success = false;
        			
        			// set error message to be used to alert user
        			$error_message = 'Date of Birth must be entered.';
        		}

        		// if query was successful then proceed to get the ID and insert student
        		if ( $success )
        		{
        			// variable to check if any of the queries failed to handle the errors
        			$failed = false;  
        			
        			// set ID of student from Attendee and insert student
        			// using the ID
        			$success = $student->set_id_value($db->insert_id);
        			
        			// check that student's ID was set with no errors
        			if ( $success ) 
        			{
        				$success = $student->execute_bound_query();  // execute the query
        				
        				// check that query was successful and student was entered
        				if ( $success ) 
        				{
        					/*** ATTENDEE AND STUDENT ADDED SUCCESSFULLY!  REDIRECT TO SUCCESS PAGE ***/
        					/* Redirect browser */
        					header("Location: ./AttendeeGridView.php");
        					/* Make sure that code below does not get executed when we redirect. */
        					exit;
        				}	
        				else 
        				{
        					$failed = true;
        				}
        			}
        			else
        			{
        				$failed = true;
        			}
        		}
        		else 
        		{
        			$failed = true;
        		}
        		
        	}
		
         
        		
        ?>
 <!-- Form section -->
        <div id="formSection" class="formSection">
            <!-- top of form -->
            <span class="formTop center"><span class="moveDown">Add a New Member to the Database</span></span>
        			
            <!-- Bottom of form **Placed at top so that form contents will overlap it placing its content behind form body content -->
            <!--UPDATE -- NO LONGER NEEDED <span class="formBottom"></span>-->
	    
			<form enctype="multipart/form-data" id="attendeeForm" name="attendeeForm" class="attendeeForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return validateAttendeeForm()">
				<table cellpadding="3px">
                    <!-- Row 1 -->
					<tr>
						<td class="col1">First Name</td>
						<td class="col2"><input type="text" id="FirstName" name="FirstName" maxlenth="30" size="15" value="<?php populate_form('FirstName'); ?>" /></td>		
						
						<td class="col3">Last Name</td>
						<td class="col4"><input type="text" id="LastName" name="LastName" maxlenth="30" size="15" value="<?php populate_form('LastName'); ?>" /></td>
						
						<td class="col5">Middle Name</td>
						<td class="col6"><input type="text" id="MiddleName" name="MiddleName" size="1" maxlength="1" value="<?php populate_form('MiddleName'); ?>" /></td>
					</tr>
                    <!-- Row 2 -->
                    
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
						M<input type="radio" name="Sex" value="male" <?php is_selected('Sex', 'male'); ?> /> &nbsp;&nbsp;&nbsp;
						F<input type="radio" name="Sex" value="female" <?php is_selected('Sex', 'female'); ?> />
					</tr>		
                    <!-- Row 3 -->
					<tr>	
						<td class="col1">Home Phone</td>
						<td class="col2"><input type="text" id="HomePhone" name="HomePhone" size="10" maxlength="10" value="<?php populate_form('HomePhone'); ?>" /></td>
						
						<td class="col3">Cell Phone</td>
						<td class="col4"><input type="text" id="CellPhone" name="CellPhone" size="10" maxlength="10" value="<?php populate_form('CellPhone'); ?>" /></td>	
						
						<td class="col5">Receive Texts?</td>
						<td class="col6"><input type="checkbox" id="CanReceiveTxt" name="CanReceiveTxt" <?php is_selected_checkbox('CanReceiveTxt'); ?> /></td>	
					</tr>	
                    <!-- Row 4 -->	
                    			<tr>	
						<td class="col1">Email</td>
						<td class="col2"><input type="text" id="Email" name="Email" value="<?php populate_form('CellPhone'); ?>" /></td>
						
						<td class="col3">Facebook Page?</td>
						<td class="col4"><input type="checkbox" id="Facebook" name="Facebook" <?php is_selected_checkbox('Facebook'); ?> /></td>
						
						<td class="col5">T-Shirt Size</td>
						<td class="col6">
							<select name="TShirtSize" id="TShirtSize">
								<?php select_box_check('TShirtSize'); ?>
								<option value="xs">XS</option>
								<option value="s">S</option>
								<option value="m">M</option>
								<option value="l">L</option>
								<option value="xl">XL</option>
								<option value="xxl">XXL</option>
							</select>
						</td>
					</tr>
                    <!-- Row 5 -->
                    			<tr>	
                    				<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
						<td class="col1">Picture URL</td>
						<td class="col2"><input type="file" id="file" name="file" style="width: 175px;" /></td>
						
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
					</td>				
					</tr>
                    <!-- Row 6 -->
                    			<tr>	
						<td class="col1">Brought By</td>
						<td class="col2"><input type="text" id="BroughtBy" name="BroughtBy" size="20" maxlenth="61" value="<?php populate_form('BroughtBy'); ?>" /></td>
						
						<td class="col3">Previous Church</td>
						<td class="col4"><input type="text" id="PreviousChurch" name="PreviousChurch" size="20" value="<?php populate_form('PreviousChurch'); ?>" /></td>
						
						<td class="col5">Other Church</td>
						<td class="col6"><input type="text" id="OtherChurch" name="OtherChurch" size="15" value="<?php populate_form('OtherChurch'); ?>" /></td>										
					</tr>
                    <!-- Row 7 -->
                    			<tr>	
                    				<td class="col1">Picture Policy</td>
						<td class="col2">
							<select name="PicturePolicy" id="PicturePolicy">
								<?php select_box_check('PicturePolicy'); ?>
								<option value="no">No</option>	
								<option value="partial">Partial</option>
								<option value="full">Full</option>							
							</select>
						</td>		
						
						<td class="col3">Occupation</td>
						<td class="col4"><input type="text" id="Occupation" name="Occupation" size="20" maxlength="50" value="<?php populate_form('Occupation'); ?>" /></td>
						
						<td class="col5">Work Phone</td>
						<td class="col6"><input type="text" id="WorkPhone" name="WorkPhone" size="10" maxlength="10" value="<?php populate_form('WorkPhone'); ?>" /></td>	
					</tr>
                    <!-- Row 8 -->	
                    			<tr>							
                    				<td colspan="3" align="right">Current Release Form Date</td>
						<td colspan="3">
							<?php
								$a = get_month_init_values('releaseMonth', 'releaseDay', 'releaseYear');
								
								selectBox("releaseMonth", "releaseMonth", "range", "1, 12", "not_set", $a[0]);
								selectBox("releaseDay", "releaseDay", "range", "1, 31", "not_set", $a[1]);
								$current_year = date("Y");
								selectBox("releaseYear", "releaseYear", "range", "$current_year, 1945", "not_set", $a[2]);
							?>
						</td>						
					</tr>
                    
                    <!-- Row 9 -->	
                    			<tr>	
                    				<td class="col1">Date Saved</td>
						<td class="col2">
							<?php
								$a = get_month_init_values('dateSavedMonth', 'dateSavedDay', 'dateSavedYear');
								
								selectBox("dateSavedMonth", "dateSavedMonth", "range", "1, 12", "not_set", $a[0]);
								selectBox("dateSavedDay", "dateSavedDay", "range", "1, 31", "not_set", $a[1]);
								$current_year = date("Y");
								selectBox("dateSavedYear", "dateSavedYear", "range", "$current_year, 1945", "not_set", $a[2]);
							?>
						</td>
						
						<td class="col3">Date Baptized</td>
						<td class="col4">
							<?php
								$a = get_month_init_values('dateBaptizedMonth', 'dateBaptizedDay', 'dateBaptizedYear');
								
								selectBox("dateBaptizedMonth", "dateBaptizedMonth", "range", "1, 12", "not_set", $a[0]);
								selectBox("dateBaptizedDay", "dateBaptizedDay", "range", "1, 31", "not_set", $a[1]);
								$current_year = date("Y");
								selectBox("dateBaptizedYear", "dateBaptizedYear", "range", "$current_year, 1945", "not_set", $a[2]);
							?>
						</td>
						
						<td class="col5">Date Holy Ghost</td>
						<td class="col6">
							<?php
								$a = get_month_init_values('dateHolyGhostMonth', 'dateHolyGhostDay', 'dateHolyGhostYear');
								
								selectBox("dateHolyGhostMonth", "dateHolyGhostMonth", "range", "1, 12", "not_set", $a[0]);
								selectBox("dateHolyGhostDay", "dateHolyGhostDay", "range", "1, 31", "not_set", $a[1]);
								$current_year = date("Y");
								selectBox("dateHolyGhostYear", "dateHolyGhostYear", "range", "$current_year, 1945", "not_set", $a[2]);
							?>
						</td>
					</tr>
                    
                    <!-- Row 10 -->	
                    			<tr>	
						<td class="col1">Start Date</td>
						<td class="col2">
							<?php
								$a = get_month_init_values('startDateMonth', 'startDateDay', 'startDateYear');
								
								selectBox("startDateMonth", "startDateMonth", "range", "1, 12", "not_set", $a[0]);
								selectBox("startDateDay", "startDateDay", "range", "1, 31", "not_set", $a[1]);
								$current_year = date("Y");
								selectBox("startDateYear", "startDateYear", "range", "$current_year, 1945", "not_set", $a[2]);
							?>
						</td>	
						
						<td class="col3">School Name</td>
						<td class="col4"><input type="text" id="SchoolName" name="SchoolName" size="15" maxlength="50" value="<?php populate_form('SchoolName'); ?>" /></td>	
						
						<td class="col5">Graduation Date</td>
						<td class="col6">
							<?php
								$a = get_month_init_values('gradDateMonth', 'gradDateDay', 'gradDateYear');
								
								selectBox("gradDateMonth", "gradDateMonth", "range", "1, 12", "not_set", $a[0]);
								selectBox("gradDateDay", "gradDateDay", "range", "1, 31", "not_set", $a[1]);
								selectBox("gradDateYear", "gradDateYear", "range", "2020, 1960", "not_set", $a[2]);
							?>
						</td>	
					</tr>
                    
                    <!-- Row 13 -->	
                    			<tr>	
						<td class="col1">Favorite Things</td>
						<td class="col2" colspan="5"><textarea rows="1" cols="80" id="FavoriteThings" name="FavoriteThings"><?php populate_form('FavoriteThings'); ?></textarea></td>			
					</tr>
                    
                    <!-- Row 14 -->	
                    			<tr>	
						<td class="col1">Notes</td>
						<td class="col2" colspan="5"><textarea rows="1" cols="80" id="Notes" name="Notes"><?php populate_form('Notes'); ?></textarea></td>		
					</tr>
                    
                    <!-- Row 15 -->	
                    			<tr>	
						<td class="center" colspan="3"><a id="homelink" href="./Home.html"><input type="button" value="Cancel" id="cancel" name="cancel" /></a></td>
						<td class="center" colspan="3"><input type="submit" value="Submit" id="submit" name="submit" /></td>			
					</tr>
				</table>
				
				<input type="hidden" name="_submit_check" value="1" /> 
			</form>
        
        </div> <!-- end form sectioin -->
        
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
