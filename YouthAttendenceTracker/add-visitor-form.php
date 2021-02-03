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