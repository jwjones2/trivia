	  <!--  ADD VISITOR SECTION.  2 Divs for showing AddVisitor form and getting input.  -->
        <div id="student_popup" class="popupLocation">

        <div id="visitorDiv1" class="transparentBackground" onclick="hideAddStudent()">
	       <!-- add a close button so user will not just have to guess to click the background,
		      also, because the form does not auto-close for quicker student adding when
		      adding more than one student.  -->
	       <input type="button" value="X" class="transparent-close-button" onclick="hideAddStudent()" />

	</div><!-- To put a transparent layer over page to prevent other input other than currently display form -->
           <div id="visitorDiv2" class="addVisitorFormSection container">
        	<form id="addVisitorForm" name="addVisitorForm" class="addVisitorForm" action="./AddVisitor.php" method="post" onsubmit="return validateAttendeeForm()">
		<table cellpadding="3px" id="pick-visitor-form">
                    	<!-- Header Row (2 rows) -->
                    			<tr>
                    				<th class="center bigText">Add A Student</th>
                    			</tr>
                    			<tr>
                    				<th class="center theme-color italic">Pick a student from the list below.</th>
                    			</tr>

			<!-- Row 1 -->
					<tr>
						<!-- Drop Down List to select a student that is already in DB or select "New Visitor" -->
						<td class="center">
						       <div id="pickVisitorStudentSection">
						       <script type="text/javascript">
							    // BUILD A STRING FOR THE PICK VISITOR AND PICK STUDENT SECTION OF CONTENT TO BE LOADED DYNAMICALLY
							    pickVisitorContent = '<select name="pickStudent" id="pickStudent">';
							    pickVisitorContent += '<option value="default">Pick a student</option>';
								 <?php
									/*********************************************************
									 * Get Eligible visitors for drop-down list
									 *
									 * To do this:  Pick the last 100 entries in servicelist
									 * 	then count/group by AttendeeID, then display in
									 * 	order of most attended to least.  CHANGED -- Get the active
                   *  students and order in alphabetical order.
									 *********************************************************/
								      // CHANGED -- $query = 'SELECT AttendeeID, COUNT(AttendeeID), FirstName, NickName, LastName FROM (SELECT s.ID, s.AttendeeID, a.FirstName, a.LastName, a.NickName, a.DOB FROM servicelist AS s INNER JOIN attendee AS a ON s.AttendeeID = a.ID ORDER BY ID DESC LIMIT 100) AS t GROUP BY t.AttendeeID ORDER BY COUNT(t.AttendeeID) DESC;';
                      $query = 'SELECT ID, FirstName, NickName, LastName FROM attendee WHERE active="yes" ORDER BY LastName;';

								      if ( $result = $db->query($query) ) {
									   while ( $object = $result->fetch_object() ) {
										// Check for NickName to write Nick name if not NULL
										if ( $object->NickName == 'NULL' || $object->NickName == null )
										   printf('pickVisitorContent += \'<option value="%s|%s|%s">%s %s</option>\';', $object->ID, $object->FirstName, $object->LastName, $object->FirstName, $object->LastName);
										else
										   printf('pickVisitorContent += \'<option value="%s|%s|%s|%s">%s %s</option>\';', $object->ID, $object->FirstName, $object->LastName, $object->NickName, $object->NickName, $object->LastName);
									   }
								      } else { echo 'pickVisitorContent += \'<option>Problem with quering Services</option>\';'; }
								 ?>
							    // end pickVisitorContent
							    pickVisitorContent += '</select>';
						  </script>
						  </div> <!-- end pickVisitorStudentSection div -->
						</td>
					</tr>
		        <!-- Add Javascript to use for toggling add a visitor. -->

						  <script type="text/javascript">
						       var vfToggle = true;  // flag to show or hide visitor form

						       function toggleAddVisitorForm() {
							    if ( vfToggle ) {
								 vfToggle = false;
								 document.getElementById('add-visitor-form').style.display="block";
							    } else {
								 vfToggle = true;
								 document.getElementById('add-visitor-form').style.display="none";
							    }
						       }
						  </script>

					<tr>
                    			     <td class="center">
						  <input type="button" value="Add" class="large-text theme-color bold" id="submit" name="submit" onclick="addStudent();" />
					     </td>
					</tr>
		</table> <!-- End the pick visitor section and start the add visitor section table -->
		    <br />
		    <br />
		<table>
		    <tr>
			 <th colspan="2" class="bold large-text">
			      Add a New Visitor
			 </th>
		    </tr>
		    <tr>
			 <td class="bold right">Name</td>
			 <td><input type="text" id="visitor-name" /></td>
		    </tr>
		    <tr>
			 <td class="bold right">DOB </td>
			 <td><input type="text" id="visitor-dob" /></td>
		    </tr>
		    <tr>
			 <td class="bold right">Sex</td>
			 <td>
			      Male<input type="radio" name="Sex" id="visitor-sex" value="male" checked="checked" /> &nbsp;&nbsp;&nbsp;
			      Female<input type="radio" name="Sex" id="visitor-sex" value="female" />
			 </td>
		    </tr>
		    <tr>
			 <td colspan="2" class="center bold italic large-text theme-color">
			      Type the name of the visitor here.  If they are already in the system select their name from the dropdown list.  If not, enter their name and date-of-birth and press "Add" below.
			 </td>
		    </tr>
		    <tr>
			 <td colspan="2" class="center">
			      <input type="button" class="large-text theme-color bold" value="Add" onclick="addNewVisitor()" />
			 </td>
		    </tr>
		</table>
	  <script>
	    $( "#visitor-name" ).autocomplete({
		minLength: 1,
		delay: 500,
		source: function( request, response ) {
			lastXhr = $.ajax({
				url: "./SearchVisitors.php",
				dataType: "json",
				data: {
					term: $("#visitor-name").val().trim(),
				},
				success: function( data, status, xhr ) {
				    if (xhr === lastXhr) {
				         response( $.map( data, function( item ) {
				            return {
				                label: item.label,
				                value: item.value,
				                id: item.id
				            };
				        }));
				    }
				}
			});
		},
		focus: function ( event, ui ) {
			event.preventDefault();
			$('#visitor-name').val(ui.item.label);
		},
		close: function ( event, ui ) {
		    $("#visitor-name").css("background-image", "url('')");
		},
		search: function ( event, ui ) {
			$("#visitor-name").css("background-image", "url('./site_images/ajax-loader.gif')");
			$("#visitor-name").css("background-position", "right");
			$("#visitor-name").css("background-repeat", "no-repeat");
		},
		select: function ( event, ui ) {
			$("#visitor-name").css("background-image", "url('')");
			$('#visitor-name').val(ui.item.label);
			// get the DOB from value and add to DOB field
			var parts = ui.item.value.split('|');
			$('#visitor-dob').val(parts[1]);
			return false;  // prevent default behavior
		}
	});

	$("#visitor-name").keypress( function ( e ) {
		if (!e) e = window.event;
		if (e.keyCode == '13'){
			$('#visitor-name').autocomplete('close');
			$("#visitor-name").css("background-image", "url('')");
			$('#visitor-name').val("");
			return false;
		}
	});

	$('#visitor-dob').datepicker({dateFormat: "mm-dd-yy", defaultDate: "-12y"});
     </script>
     	</div>  <!-- end the visitor form section -->
	        <table cellpadding="3px" id="add-visitor-form" class="container-semi-solid-bg" style="display: none;">
		    <tr>
			 <th colspan="6" class="center bigText">Add A Visitor</th>
                    </tr>
			<!-- Row 2 -->
                    			<tr id="vf1">
						<td class="col1">First Name</td>
						<td class="col2"><input type="text" id="FirstName" name="FirstName" maxlenth="30" size="15" value="<?php populate_form('FirstName'); ?>" /></td>

						<td class="col3">Last Name</td>
						<td class="col4"><input type="text" id="LastName" name="LastName" maxlenth="30" size="15" value="<?php populate_form('LastName'); ?>" /></td>

						<td class="col5">Middle Initial</td>
						<td class="col6"><input type="text" id="MiddleName" name="MiddleName" size="1" maxlength="1" value="<?php populate_form('MiddleName'); ?>" /></td>
					</tr>
                    <!-- Row 3 -->

					<tr id="vf2">
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
					<tr id="vf3">
						<td class="col1">Email</td>
						<td class="col2"><input type="text" id="Email" name="Email" value="<?php populate_form('CellPhone'); ?>" /></td>

						<td class="col3">Cell Phone</td>
						<td class="col4"><input type="text" id="CellPhone" name="CellPhone" size="10" maxlength="10" value="<?php populate_form('CellPhone'); ?>" /></td>

						<td class="col5">Receive Texts?</td>
						<td class="col6"><input type="checkbox" id="CanReceiveTxt" name="CanReceiveTxt" <?php is_selected_checkbox('CanReceiveTxt'); ?> /></td>
					</tr>
                    <!-- Row 5-->
                    			<tr id="vf4">
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
									$query = "SELECT * FROM zipcode";    // construct the query
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
                    			<tr id="vf5">
						<td class="col1">Notes</td>
						<td class="col2" colspan="5"><textarea rows="1" cols="80" id="Notes" name="Notes"><?php populate_form('Notes'); ?></textarea></td>
					</tr>

                    <!-- Row 7 -->
                    			<tr>
                    				<td class="center" colspan="3"><input type="button" value="Cancel" onclick="hideAddVisitor()" /></td>
						<td class="center" colspan="3"><input type="button" value="Add" id="submit" name="submit" onclick="addVisitor(true);" /></td>
					</tr>
			</table>

			<input type="hidden" name="_submit_check" value="1" />
		</form>

		<!-- ADD RECENT SECTION:  TO ADD STUDENTS THAT ARE TYPICALLY IN ATTENDANCE TO SPEED UP ADDING PROCESS -->
		<div id="add-recent-student" class="container">
		    <h3>Recent Students</h3>
			<h4><i>Click on a student to add.</i></h4>
		    <!-- List students who have attended most recently, taking the last 100 entries of service list and aggregating by attendence times -->
		    <?php
			 $query = 'SELECT AttendeeID, COUNT(AttendeeID), FirstName, NickName, LastName, PictureURL FROM (SELECT s.ID, s.AttendeeID, a.FirstName, a.LastName, a.NickName, a.DOB, a.PictureURL FROM servicelist AS s INNER JOIN attendee AS a ON s.AttendeeID = a.ID ORDER BY ID DESC LIMIT 100) AS t GROUP BY t.AttendeeID ORDER BY COUNT(t.AttendeeID) DESC;';

			 if ( $result = $db->query($query) ) {
			      while ( $obj = $result->fetch_object() ) {
				   // first check to see if the student has a picture in the system.  If so use the picture instead of a button
				   if ( $obj->PictureURL != "NULL" ) {
					printf('<span><img src="%s" width="75px" height="75px" style="cursor: hand; cursor: pointer;"', $obj->PictureURL);
					// check for nickname and use if not null
					if ( $obj->NickName != 'NULL' && $obj->NickName != null )
					     printf(' onclick=\'setStudentSelect("%d|%s|%s|%s")\' alt=\'Add %s %s\' /></span>', $obj->AttendeeID, $obj->FirstName, $obj->LastName, $obj->NickName, $obj->NickName, $obj->LastName);
					else
					     printf(' onclick=\'setStudentSelect("%d|%s|%s")\' alt="Add %s %s" /></span>', $obj->AttendeeID, $obj->FirstName, $obj->LastName, $obj->FirstName, $obj->LastName);
				   } else {
					// check for nickname and add if not null
					if ( $obj->NickName != 'NULL' && $obj->NickName != null )
					     printf('<span><input type="button" onclick=\'setStudentSelect("%d|%s|%s|%s")\' value=\'Add %s %s\' /></span>', $obj->AttendeeID, $obj->FirstName, $obj->LastName, $obj->NickName, $obj->NickName, $obj->LastName);
					else
					     printf('<span><input type="button" onclick=\'setStudentSelect("%d|%s|%s")\' value="Add %s %s" /></span>', $obj->AttendeeID, $obj->FirstName, $obj->LastName, $obj->FirstName, $obj->LastName);
				   }
			      }
			 }
		    ?>
		</div>
        </div>

        </div>  <!--  END ADD VISITOR FORM SECTION  -->
