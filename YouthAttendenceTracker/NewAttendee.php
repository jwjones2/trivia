<?php
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and set $error if not
	if ( $db->connect_error ) {
		$error = "There was a problem connecting to the database.";
	}
	
	/** Require the HTML_helper.php file for helper functions **/
	require_once('HTML_helper.php');
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
        <title>Add a New Student</title>
        
        <!--favicon icon-->
        <link rel="shortcut icon" href="./favicon.ico" />
   
        <!-- Metadata Section:  Description of Page and Keywords for Site. -->
        <meta name="description" content="" />
        <meta name="keywords" content="" />
   
        <!-- Link to Stylesheet -->
        <link rel="stylesheet" href="./main.css" type="text/css" />
        
        <!-- Link to Javascript code for page -->
        <script type="text/javascript" language="javascript" src="./main.js"> </script>
        
    </head>
    
    <body class="formBody">
    	  <?php
    	  		// print the error message if it exists
    	  		if ( $error != "" ) 
    	  			echo "<h1 class=\"red\">$error</h1>";
    	  ?>
        
        <!-- Form section -->
        <div id="formSection" class="formSection">
            <!-- top of form -->
            <span class="formTop center"><span class="moveDown">Add a New Member to the Database</span></span>
        			
            <!-- Bottom of form **Placed at top so that form contents will overlap it placing its content behind form body content -->
            <span class="formBottom"></span>
            
			<form enctype="multipart/form-data" id="attendeeForm" name="attendeeForm" class="attendeeForm" action="./GetFormElements.php" method="post" onsubmit="return validateAttendeeForm()">
				<table cellpadding="3px">
                    <!-- Row 1 -->
					<tr>
						<td class="col1, labelBig">First Name</td>
						<td class="col2, labelBig"><input type="text" id="FirstName" name="FirstName" maxlenth="30" size="15" /></td>		
						
						<td class="col3, labelBig">Last Name</td>
						<td class="col4, labelBig"><input type="text" id="LastName" name="LastName" maxlenth="30" size="15" /></td>
						
						<td class="col5">Middle Name</td>
						<td class="col6"><input type="text" id="MiddleName" name="MiddleName" size="1" maxlength="1" /></td>
					</tr>
                    <!-- Row 2 -->
					<tr>	
						<td class="col1">Nick Name</td>
						<td class="col2"><input type="text" id="NickName" name="NickName" size="15" maxlength="30" /></td>	
						
						<td class="col3">Date of Birth</td>
						<td class="col4">
							<?php
								selectBox("dobMonth", "dobMonth", "range", "1, 12");
								selectBox("dobDay", "dobDay", "range", "1, 31");
								$current_year = date("Y");
								selectBox("dobYear", "dobYear", "range", "$current_year, 1945");
							?>
						</td>
						
						<td class="col5">Sex</td>
						<td class="col6">
						M<input type="radio" name="Sex" value="male" /> &nbsp;&nbsp;&nbsp;
						F<input type="radio" name="Sex" value="female" />
					</tr>		
                    <!-- Row 3 -->
					<tr>	
						<td class="col1">Home Phone</td>
						<td class="col2"><input type="text" id="HomePhone" name="HomePhone" size="10" maxlength="10" /></td>
						
						<td class="col3">Cell Phone</td>
						<td class="col4"><input type="text" id="CellPhone" name="CellPhone" size="10" maxlength="10" /></td>	
						
						<td class="col5">Receive Texts?</td>
						<td class="col6"><input type="checkbox" id="CanReceiveTxt" name="CanReceiveTxt" /></td>	
					</tr>	
                    <!-- Row 4 -->	
                    			<tr>	
						<td class="col1">Email</td>
						<td class="col2"><input type="text" id="Email" name="Email" /></td>
						
						<td class="col3">Facebook Page?</td>
						<td class="col4"><input type="checkbox" id="Facebook" name="Facebook" /></td>
						
						<td class="col5">T-Shirt Size</td>
						<td class="col6">
							<select name="TShirtSize" id="TShirtSize">
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
						<td class="col1">Picture URL</td>
						<td class="col2"><input type="file" id="file" name="file" style="width: 175px;" /></td>
						
						<td class="col3">Street Address</td>
						<td class="col4"><input type="text" id="StreetAddress" name="StreetAddress" /></td>
						
						<td class="col5">Zip</td>
						<td class="col6">
							<select name="Zip" id="Zip">
								<?php                                
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
						<td class="col2"><input type="text" id="BroughtBy" name="BroughtBy" size="20" maxlenth="61" /></td>
						
						<td class="col3">Previous Church</td>
						<td class="col4"><input type="text" id="PreviousChurch" name="PreviousChurch" size="20" /></td>
						
						<td class="col5">Other Church</td>
						<td class="col6"><input type="text" id="OtherChurch" name="OtherChurch" size="20" /></td>										
					</tr>
                    <!-- Row 7 -->
                    			<tr>	
                    				<td class="col1">Picture Policy</td>
						<td class="col2">
							<select name="PicturePolicy" id="PicturePolicy">
								<option value="no">No</option>	
								<option value="partial">Partial</option>
								<option value="full">Full</option>							
							</select>
						</td>		
						
						<td class="col3">Occupation</td>
						<td class="col4"><input type="text" id="Occupation" name="Occupation" size="20" maxlength="50" /></td>
						
						<td class="col5">Work Phone</td>
						<td class="col6"><input type="text" id="WorkPhone" name="WorkPhone" size="10" maxlength="10" /></td>	
					</tr>
                    <!-- Row 8 -->	
                    			<tr>	
						<td class="col1">Release Form</td>
						<td class="col2">
							<select id="ReleaseForm" name="ReleaseForm">
								<option value="yes">yes</option>
								<option value="no">no</option>
							</select>
						</td>
						
						<td class="col3">Current Release Form Date</td>
						<td class="col4">
							<?php
								selectBox("releaseMonth", "releaseMonth", "range", "1, 12");
								selectBox("releaseDay", "releaseDay", "range", "1, 31");
								$current_year = date("Y");
								selectBox("releaseYear", "releaseYear", "range", "$current_year, 1945");
							?>
						</td>						
					</tr>
                    
                    <!-- Row 9 -->	
                    			<tr>	
                    				<td class="col1">Date Saved</td>
						<td class="col2">
							<?php
								selectBox("dateSavedMonth", "dateSavedMonth", "range", "1, 12");
								selectBox("dateSavedDay", "dateSavedDay", "range", "1, 31");
								$current_year = date("Y");
								selectBox("dateSavedYear", "dateSavedYear", "range", "$current_year, 1945");
							?>
						</td>
						
						<td class="col3">Date Baptized</td>
						<td class="col4">
							<?php
								selectBox("dateBaptizedMonth", "dateBaptizedMonth", "range", "1, 12");
								selectBox("dateBaptizedDay", "dateBaptizedDay", "range", "1, 31");
								$current_year = date("Y");
								selectBox("dateBaptizedYear", "dateBaptizedYear", "range", "$current_year, 1945");
							?>
						</td>
						
						<td class="col5">Date Holy Ghost</td>
						<td class="col6">
							<?php
								selectBox("dateHolyGhostMonth", "dateHolyGhostMonth", "range", "1, 12");
								selectBox("dateHolyGhostDay", "dateHolyGhostDay", "range", "1, 31");
								$current_year = date("Y");
								selectBox("dateHolyGhostYear", "dateHolyGhostYear", "range", "$current_year, 1945");
							?>
						</td>
					</tr>
                    
                    <!-- Row 10 -->	
                    			<tr>	
						<td class="col1">Start Date</td>
						<td class="col2">
							<?php
								selectBox("startDateMonth", "startDateMonth", "range", "1, 12");
								selectBox("startDateDay", "startDateDay", "range", "1, 31");
								$current_year = date("Y");
								selectBox("startDateYear", "startDateYear", "range", "$current_year, 1945");
							?>
						</td>	
						
						<td class="col3">School Name</td>
						<td class="col4"><input type="text" id="SchoolName" name="SchoolName" size="15" maxlength="50" /></td>	
						
						<td class="col5">Graduation Date</td>
						<td class="col6">
							<?php
								selectBox("gradDateMonth", "gradDateMonth", "range", "1, 12");
								selectBox("gradDateDay", "gradDateDay", "range", "1, 31");
								selectBox("gradDateYear", "gradDateYear", "range", "2020, 1960");
							?>
						</td>	
					</tr>
                    
                    <!-- Row 13 -->	
                    			<tr>	
						<td class="col1">Favorite Things</td>
						<td class="col2" colspan="5"><textarea rows="1" cols="80" id="FavoriteThings" name="FavoriteThings"></textarea></td>			
					</tr>
                    
                    <!-- Row 14 -->	
                    			<tr>	
						<td class="col1">Notes</td>
						<td class="col2" colspan="5"><textarea rows="1" cols="80" id="Notes" name="Notes"></textarea></td>		
					</tr>
                    
                    <!-- Row 15 -->	
                    			<tr>	
						<td class="center" colspan="3"><input type="button" value="Cancel" onclick="" id="cancel" name="cancel" /></td>
						<td class="center" colspan="3"><input type="submit" value="Next" id="submit" name="submit" /></td>			
					</tr>
				</table>
				
				<input type="hidden" name="_submit_check" value="1" /> 
			</form>
        
        </div> <!-- end form sectioin -->
        
    </body>
</html>