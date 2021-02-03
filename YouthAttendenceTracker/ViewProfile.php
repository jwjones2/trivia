<?php
	/********************************************************
	 * ViewProfile.php
	 *
	 * Description:  Gets an ID from $_POST and uses Attendee
	 *	and Student (and Sponsor) classes to get the values
	 *	of a row in the correspondind tables, displaying
	 *	the values and allowing for editing of the values.
	 ********************************************************/

	// output the header
	$title = "Student Profile";
	require_once('Header.php');

	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation

        // check for get for ID if post ID not set
        if ( !isset($_POST['ID']) )
            $_POST['ID'] = $_GET['ID'];

?>
	  <?php
    	  		// print the error message if it exists
    	  		if ( $error != "" )
    	  			echo "<h1 class='red'>$error</h1>";


        	/*******************************************
        	 * Get row from table section.
        	 *******************************************/
              	$id = $db->real_escape_string($_POST['ID']); // use real_escape_string to escape the input value

        	$a = new Attendee($db, $_POST, $id);         // create Attendee object using optional parameter in constructor to pass id
        	$s = new Student($db, $_POST, $id);          // create Student object using optional parameter in constructor to pass id

        	/**  Attendee now holds the values of the row in attendee table corresponding to $id.  Now populate the form below with these values **/


        	// variable to hold confirmation message on UPDATE
        	$confirmation_message = "";

        	/***************************************************************
        	 * SUBMIT CHANGES SECTION
        	 *
        	 * Description:  This code runs when the (1) Edit button was pressed
        	 *	and then (2) Changes were made to the form and (3) The submit
        	 *	changes button was pressed.  This code creates a temp Attendee
        	 *	and Student object to hold the values of the submitted form
        	 *	and then the function of the DB Attendee and Student object
        	 *	is called that compares the two objects values, updates the
        	 *	DB, and finally updates the DB Object.  Then Edit is set to 'n'
        	 *	and the rest of the page runs, returning the default ViewProfile
        	 *	state.
        	 **************************************************************/
        	 if ( isset($_POST['SubmitChanges']))
        	 {
        	 	 /*****
        	 	  * Steps to updating Attendee Profile.
        	 	  *
        	 	  * 1. Create an Attendee object from the submitted form data.
        	 	  * 2. Use the built in function of Attendee to compare the Attendee
        	 	  *    object created from form data with Attendee object already
        	 	  *    created from db record and updating the db object if necessary.
        	 	  *    A)  This function compares all the fields and values of
        	 	  *        the objects and creates an UPDATE query to update the db
        	 	  *        record.
        	 	  *    B)  Upon successful querying the db, the db object is updated.
        	 	  * 3. The Edit variable of $_POST is set to "n" so that the form
        	 	  *    is not in Edit mode since it was already updated.
        	 	  * 4. An confirmation message is shown and then the form below will
        	 	  *    display the updated profile (it draws data from the db object
        	 	  *    and so all the updated data will be properly shown).
        	 	  *****/
        	 	  // further prepare the form elements for creating the Form Attendee object
        		  // All dates in form need to be condensed to YYYY-MM-DD format
        		  // *Use build_date_from_form to do this.
        		  $_POST['DOB'] = build_date_from_form($_POST, 'dobYear', 'dobMonth', 'dobDay');
        		  $_POST['ReleaseFormUpdate'] = build_date_from_form($_POST, 'releaseYear', 'releaseMonth', 'releaseDay');
        		  $_POST['DateSaved'] = build_date_from_form($_POST, 'dateSavedYear', 'dateSavedMonth', 'dateSavedDay');
        		  $_POST['DateBaptized'] = build_date_from_form($_POST, 'dateBaptizedYear', 'dateBaptizedMonth', 'dateBaptizedDay');
        		  $_POST['DateHolyGhost'] = build_date_from_form($_POST, 'dateHolyGhostYear', 'dateHolyGhostMonth', 'dateHolyGhostDay');
        		  $_POST['StartDate'] = build_date_from_form($_POST, 'startDateYear', 'startDateMonth', 'startDateDay');
        		  // check that start date was default and if so set to current date
        		  $default_date = date("Y") . "-1-1";  // XXXX-1-1
        		  if ( $_POST['StartDate'] == $default_date )
        		  	  $_POST['StartDate'] = date("Y-m-d");
        		  $_POST['GraduationDate'] = build_date_from_form($_POST, 'gradDateYear', 'gradDateMonth', 'gradDateDay');
        		  $_POST['LeaveDate'] = "0000-00-00";   // set LeaveDate to default since not being used

        		  // set value of SubDiscriminator for attendee type to be student
        		  $_POST['SubDiscriminator'] = "student";

        		  // set Active to yes
        		  $_POST['Active'] = 'yes';

        		  /**
        		  	  GET the Picture from the file upload from the form for the Profile Picture.
        		  **/
        		  /******************************************************************************
			   *UPDATE -- Using a filename instead of a file for now.
			   *
        		  $uploaded = false;  // flag to check that the file uploaded sucessfully

        		  // first test that file has been changed
        		  // if db PictureURL equals what was submitted from form, don't upload
        		  // if no Picture uploaded then set the form $_POST PictureURL to db value for comparison purposes
        		  if ( count($_FILES) == 0 )
        		  {
        		  	  $_POST['PictureURL'] = $a->retrieve_value('PictureURL');

        		  	  // set uploaded to true, since the upload did not fail it just didn't need to happen since no changes
        	 	  	  // were made in the picture URL
        	 	  	  $uploaded = true;
        	 	  }
        		  else
        		  {
        		  	// test that file is right type and not too large
        		  	if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg")) && ($_FILES["file"]["size"] < 50000))
        		  	{
        		  		// check for file errors
        		  		if ($_FILES["file"]["error"] > 0)
        		  		{
        		  			$confirmation_message .= "Return Code: " . $_FILES["file"]["error"] . "<br />";  // print error
        		  		}
        		  		else
        		  		{
        		  			// Check to make sure file doesn't already exist
        		  			if (file_exists("upload/" . $_FILES["file"]["name"]))
        		  			{
        		  				$confirmation_message .= $_FILES["file"]["name"] . " already exists. ";  // print error
        		  			}
        		  			else
        		  			{
        		  				// Now, move file from tmp directory to the right location on the server
        		  				move_uploaded_file($_FILES["file"]["tmp_name"], "profile_pictures/" . $_FILES["file"]["name"]);

        		  				// Set the $_POST value for the PictureURL to insert into the database
        		  				$_POST['PictureURL'] = "profile_pictures/" . $db->real_escape_string($_FILES['file']['name']);

        		  				$uploaded = true; // set uploaded flag
        		  			}
        		  		}
        		  	}
        		  	else
        		  	{
        		  		$confirmation_message = "Invalid file for upload:  Update failed.";  // Add picture error to confirmation_message
        		  	}
        	 	  }
        	 	  **
        	 	  **PictureURL is set in the POST values already
        	 	  *********************************************************************************/

                         $form_att = new Attendee($db, $_POST);            // 1 - create Attendee from form
        		 $form_stu = new Student($db, $_POST);             //   - create Student from form

                         $success = $a->compare_to_update($db, $form_att); // 2 - compare and update db Attendee object
        		 if ( $success )                                   //   if successful, then update Student
        		  	$success = $s->compare_to_update($db, $form_stu);

        		 $_POST['Edit'] = "n";                             // 3 - set Edit to "n"

        		 if ( $success )                                   // 4 - Display confirmation
        		     $confirmation_message = "Profile was successfully updated!";
        		 else
        		     $confirmation_message = "Update failed.  Please try again.";
        	}

        	/******  Disable Check  *******/
        	// use disable_check to set an int to use in disabling select boxes if necessary
        	$disabled = disable_check();

        ?>

 <!-- Form section -->
        <div id="formSection" class="formSection">

            <!-- top of form -->
            <span class="formTop center"><span class="moveDown"><?php printf('%s %s\'s Profile', $a->retrieve_value('FirstName'), $a->retrieve_value('LastName')); ?></span></span>

			<form enctype="multipart/form-data" id="attendeeForm" name="attendeeForm" class="attendeeForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return validateAttendeeForm()">
				<table cellpadding="3px">
                    <!-- Row 1 -->
					<tr>
						<td class="column1">
							<?php
								echo '<img src="';  // start img tag

								// check if there is a profile image, if not check if boy or girl and add holder image
								if ( $a->retrieve_value('PictureURL') == NULL ) // no picture
								{
									if ( $a->retrieve_value('Sex') == "male" )  // a boy
										echo './site_images/boy-icon.png" height="60px" width="60px"';
									else					    // a girl
										echo './site_images/girl-icon.png" height="60px" width="60px"';
								}
								else   // print the image URL
								{
									printf('./%s" width="150px" height="150px"', $a->retrieve_value('PictureURL') );
								}

								// finish the tag
								echo ' id="pictureUrl" />';
							?>
						</td>
						<td class="column2, label">
							First Name  <input type="text" id="FirstName" name="FirstName" maxlenth="30" size="15" value="<?php echo $a->retrieve_value('FirstName'); ?>"  <?php disable(); ?> />
							  <br />
							  <br />
							  <br />
							Nick Name  <input type="text" id="NickName" name="NickName" size="15" maxlength="30" value="<?php echo $a->retrieve_value('NickName'); ?>" <?php disable(); ?> /></td>

						<td class="column3, label">
							Last Name  <input type="text" id="LastName" name="LastName" maxlenth="30" size="15" value="<?php echo $a->retrieve_value('LastName'); ?>" <?php disable(); ?> />
							  <br />
							  <br />
							  <br />
							DOB
							<?php
								$vals = get_month_init_values_from_db($a, 'DOB');

								selectBox("dobMonth", "dobMonth", "range", "1, 12", "not_set", $vals[0], $disabled);
								selectBox("dobDay", "dobDay", "range", "1, 31", "not_set", $vals[1], $disabled);
								$current_year = date("Y");
								selectBox("dobYear", "dobYear", "range", "$current_year, 1945", "not_set", $vals[2], $disabled);
							?>
						</td>

						<td class="column4, label">
							Middle Initial  <input type="text" id="MiddleName" name="MiddleName" size="1" maxlength="1" value="<?php echo $a->retrieve_value('MiddleName'); ?>" <?php disable(); ?> />
							  <br />
							  <br />
							  <br />
							Sex&nbsp;&nbsp;
								M<input type="radio" name="Sex" value="male" <?php if ( $a->retrieve_value('Sex') == 'male' ) printf('checked="checked"'); ?> <?php disable(); ?> /> &nbsp;&nbsp;&nbsp;
								F<input type="radio" name="Sex" value="female" <?php if ( $a->retrieve_value('Sex') == 'female' ) printf('checked="checked"'); ?> <?php disable(); ?> />
						</td>
					</tr>

					<?php
						/*** Edit section for PictureURL
							-Upon the user hitting Edit, add the picture URL upload form element.
						 ***/
						if ( isset($_POST['Edit']) && $_POST['Edit'] == 'y' )
						{
							echo '<tr><td class="column1, label" colspan="4"><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />Picture URL';

							// output an input box that is disabled and has the current PictureURL and has a change button that is Javascript
							// enabled, upon pressing button the form will change to a file upload input element
							printf('<span id="changeImageURL"><input type="hidden" name="PictureURL" id="PictureURL" value="%s" /><span id="pictureUrlValue" style="color: Black; font-weight: bold">%s</span><input type="button" value="Change Picture URL" onclick="changeURL();" /></span></td></tr>', $a->retrieve_value('PictureURL'), $a->retrieve_value('PictureURL'));
						}
					?>

                    <!-- Row 3  ** Row 2 is combined with Row 1 beside the Picture -->
					<tr>
						<td class="column1, label">Home Phone  <input type="text" id="HomePhone" name="HomePhone" size="12" maxlength="10" value="<?php echo $a->retrieve_value('HomePhone'); ?>" <?php disable(); ?> /></td>

						<td class="column2, label">Cell Phone  <input type="text" id="CellPhone" name="CellPhone" size="12" maxlength="10" value="<?php echo $a->retrieve_value('CellPhone'); ?>" <?php disable(); ?> /></td>

						<td class="column3, label">Receive Texts?  <input type="checkbox" id="CanReceiveTxt" name="CanReceiveTxt" <?php if ( $a->retrieve_value('CanReceiveTxt') == 'yes' ) printf('checked="checked"'); ?> <?php disable(); ?> /></td>

						<td class="column4, label">Email<br /><input type="text" id="Email" name="Email" size="30" value="<?php echo $a->retrieve_value('Email'); ?>" <?php disable(); ?> /></td>
					</tr>
                    <!-- Row 4 -->
                    			<tr>
						<td class="column1, label">Facebook Page?  <input type="checkbox" id="Facebook" name="Facebook" <?php if ( $a->retrieve_value('Facebook') == 'yes' ) printf('checked="checked"'); ?> <?php disable(); ?> /></td>

						<td class="column2, label">Street Address <br /> <input type="text" id="StreetAddress" name="StreetAddress" size="30" value="<?php echo $a->retrieve_value('StreetAddress'); ?>" <?php disable(); ?> /></td>

						<td class="column3, label">Zip
							<select name="Zip" id="Zip" <?php disable(); ?> >
								<?php
									printf('<option value="%s">%s</option>', $a->retrieve_value('Zip'), $a->retrieve_value('Zip') );

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

									<td class="column4, label">T-Shirt Size
							<select name="TShirtSize" id="TShirtSize" <?php disable(); ?>>
								<?php if ( $a->retrieve_value('TShirtSize') ) printf('<option value="%s">%s</option>', $a->retrieve_value('TShirtSize'), strtoupper($a->retrieve_value('TShirtSize')) ); ?>
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
                    				<td class="column1, label">Picture Policy
							<select name="PicturePolicy" id="PicturePolicy" <?php disable(); ?>>
								<?php printf('<option value="%s">%s</option>', $a->retrieve_value('PicturePolicy'), strtoupper($a->retrieve_value('PicturePolicy')) ); ?>
								<option value="no">No</option>
								<option value="partial">Partial</option>
								<option value="full">Full</option>
							</select>
						</td>

						<td class="column2, label" colspan="2">Release Form Date
							<?php
								$vals = get_month_init_values_from_db($a, 'ReleaseFormUpdate');

								selectBox("releaseMonth", "releaseMonth", "range", "1, 12", "not_set", $vals[0], $disabled);
								selectBox("releaseDay", "releaseDay", "range", "1, 31", "not_set", $vals[1], $disabled);
								$current_year = date("Y");
								selectBox("releaseYear", "releaseYear", "range", "$current_year, 1945", "not_set", $vals[2], $disabled);
							?>
						</td>

						<td class="column4, label">Brought By  <input type="text" id="BroughtBy" name="BroughtBy" size="20" maxlenth="61" value="<?php echo $a->retrieve_value('BroughtBy'); ?>" <?php disable(); ?> /></td>
					</td>
					</tr>
                    <!-- Row 6 -->
                    			<tr>
						<td class="column1, label">Occupation  <input type="text" id="Occupation" name="Occupation" size="20" maxlength="50" value="<?php echo $a->retrieve_value('Occupation'); ?>" <?php disable(); ?> /></td>

						<td class="column2, label">Work Phone  <input type="text" id="WorkPhone" name="WorkPhone" size="12" maxlength="10" value="<?php echo $a->retrieve_value('WorkPhone'); ?>" <?php disable(); ?> /></td>

						<td class="column3, label">Previous Church  <input type="text" id="PreviousChurch" name="PreviousChurch" size="20" value="<?php echo $s->retrieve_value('PreviousChurch'); ?>" <?php disable(); ?> /></td>

						<td class="column4, label">Other Church  <input type="text" id="OtherChurch" name="OtherChurch" size="20" value="<?php echo $s->retrieve_value('OtherChurch'); ?>" <?php disable(); ?> /></td>
					</tr>
                    <!-- Row 7 -->
                    			<tr>
                    				<td class="column1, label">Start Date<br />
							<?php
								$vals = get_month_init_values_from_db($s, 'StartDate');

								selectBox("startDateMonth", "startDateMonth", "range", "1, 12", "not_set", $vals[0], $disabled);
								selectBox("startDateDay", "startDateDay", "range", "1, 31", "not_set", $vals[1], $disabled);
								$current_year = date("Y");
								selectBox("startDateYear", "startDateYear", "range", "$current_year, 1945", "not_set", $vals[2], $disabled);
							?>
						</td>

                    				<td class="column2, label">Date Saved<br />
							<?php
								$vals = get_month_init_values_from_db($a, 'DateSaved');

								selectBox("dateSavedMonth", "dateSavedMonth", "range", "1, 12", "not_set", $vals[0], $disabled);
								selectBox("dateSavedDay", "dateSavedDay", "range", "1, 31", "not_set", $vals[1], $disabled);
								$current_year = date("Y");
								selectBox("dateSavedYear", "dateSavedYear", "range", "$current_year, 1945", "not_set", $vals[2], $disabled);
							?>
						</td>

						<td class="column3, label">Date Baptized<br />
							<?php
								$vals = get_month_init_values_from_db($a, 'DateBaptized');

								selectBox("dateBaptizedMonth", "dateBaptizedMonth", "range", "1, 12", "not_set", $vals[0], $disabled);
								selectBox("dateBaptizedDay", "dateBaptizedDay", "range", "1, 31", "not_set", $vals[1], $disabled);
								$current_year = date("Y");
								selectBox("dateBaptizedYear", "dateBaptizedYear", "range", "$current_year, 1945", "not_set", $vals[2], $disabled);
							?>
						</td>

						<td class="column4, label">Holy Ghost Filled<br />
							<?php
								$vals = get_month_init_values_from_db($a, 'DateHolyGhost');

								selectBox("dateHolyGhostMonth", "dateHolyGhostMonth", "range", "1, 12", "not_set", $vals[0], $disabled);
								selectBox("dateHolyGhostDay", "dateHolyGhostDay", "range", "1, 31", "not_set", $vals[1], $disabled);
								$current_year = date("Y");
								selectBox("dateHolyGhostYear", "dateHolyGhostYear", "range", "$current_year, 1945", "not_set", $vals[2], $disabled);
							?>
						</td>
					</tr>

                    <!-- Row 8 -->
                    			<tr>
                    				<td class="column1, label" style="text-align: right;">School Name</td>
                    				<td class="column2, label"><input type="text" id="SchoolName" name="SchoolName" size="30" maxlength="50" value="<?php echo $s->retrieve_value('SchoolName'); ?>" <?php disable(); ?> /></td>

                    				<td class="column3, label" style="text-align: right;">Graduation Date</td>
                    				<td class="column4, label">
							<?php
								$vals = get_month_init_values_from_db($s, 'GraduationDate');

								selectBox("gradDateMonth", "gradDateMonth", "range", "1, 12", "not_set", $vals[0], $disabled);
								selectBox("gradDateDay", "gradDateDay", "range", "1, 31", "not_set", $vals[1], $disabled);
								selectBox("gradDateYear", "gradDateYear", "range", "2030, 1960", "not_set", $vals[2], $disabled);
							?>
						</td>
					</tr>

                    <!-- Row 9 -->
                    			<tr>
						<td class="column1, label" style="text-align: right;">Favorite Things</td>
						<td class="label" colspan="3"><textarea rows="1" cols="60" id="FavoriteThings" name="FavoriteThings" <?php disable(); ?>><?php echo $a->retrieve_value('FavoriteThings'); ?></textarea></td>
					</tr>

                    <!-- Row 10 -->
                    			<tr>
						<td class="column1, label" style="text-align: right;">Notes</td>
						<td class="label" colspan="3"><textarea rows="1" cols="60" id="Notes" name="Notes" <?php disable(); ?>><?php echo $a->retrieve_value('Notes'); ?></textarea></td>
					</tr>
					<tr><td colspan="6"></td></tr>

                    <!-- Row 11 -->
                    			<tr>
                    				<?php
                    					/*****************************************
                    					 * Section that determines which buttons to display.
                    					 *
                    					 * If Edit isset then show Cancel and Submit Changes buttons,
                    					 * Else Show Edit button.
                    					 *****************************************/
                    					 // first insert the ID for submitting to page
                    					 printf('<input type="hidden" name="ID" value="%s" />', $id);

                    					 if ( isset($_POST['Edit']) && $_POST['Edit'] == "y" )
                    					 {
                    					 	 printf('<td class="center" colspan="2"><input type="hidden" value="n" name="Edit" /><input type="submit" value="Cancel" id="cancel" name="cancel" /></td>');
                    					 	 echo '<td class="center" colspan="2"><input type="submit" value="Submit Changes" id="SubmitChanges" name="SubmitChanges" /></td>';

                    					 }
                    					 else
                    					 {
                    					 	 printf('<td class="center" colspan="2"><a href="./AttendeeGridView.php"><input type="button" value="Back to Student List" /></a></td>');
                    					 	 printf('<form id="editSubmitForm" name="editSubmitForm" action="%s">', $_SERVER['PHP_SELF']);
                    					 	 printf('<td class="center" colspan="2"><input type="hidden" name="Edit" value="y" /><input type="hidden" name="_submit_check" value="1" /> <input type="submit" value="Edit" id="Edit" name="submitEdit" /></td>');

                    					 }
                    				?>

					</tr>
				</table>

				<input type="hidden" name="_submit_check" value="1" />
			</form>

        </div> <!-- end form sectioin -->

         <!-- ERROR section -->
 	<div id="showErrors" class="showErrors">
 		<?php if ( $confirmation_message != "" ) printf('<br /><span style="color: red;">%s</span>', $confirmation_message); ?>
 	</div>

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
