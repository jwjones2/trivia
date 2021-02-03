<?php
    // First connect to the database or display an error
    $db = mysqli_connect('127.0.0.1', 'root', '', 'sfasc');  
	if ( !$db ) {
		// if there is an error, set the $error variable to use later in the page
		// That way at least the page is shown rather than it failing at startup
		$error = "<h1 class='bold red pageContent center'>Error:  There was a problem reading the Database.  Please check back later for the Club Activities information.</h1>";
	}

	// Check for classes that have ended and need to have their placesAvailable reset
	
	// get all the fitnessclass table records
	$query = "SELECT * FROM fitnessclass";
	if ( $result = mysqli_query($db, $query) ) {
		// loop over results and get record as object with mysqli_fetch_object
		while ( $data = mysqli_fetch_object($result) ) {
			// compare day of year of fitnessclass record which is stored in InstanceRecord
			// with the current day of the year
			// get the current day of the year from getdate and then "yday" from associative array it returns
			// and hour using "hours" from assoicative array
			$date = getdate();
			$day = $date["yday"];
			$currentHour = $date["hours"] - 7;  // have to subtract 7 hours for the timezone difference
												// timezone is supposed to be GMT (which is a 5 hour offset) but localtime on this
												// computer is returning a "Europe/Paris" timezone which is, evidently, a 7 hour offset
			
			if ( $day > $data->InstanceRecord ) { // the current day of the year is after the class day of year so class is past
				resetPlacesAvailable($data->FitnessClassID);  // call function passing the id of fitnessclass record to reset placesAvailable and bookings	
			} elseif ( $day == $data->InstanceRecord ) {  // if day of the year is same as class day of year, then check hour
				// get hour from fitnessclass record
				$classDate = getdate(strtotime($data->ClassDate));
				$classHour = $classDate["hours"]; 
				
				if ( $currentHour >= $classHour ) { // if current hour is equal to or greater than, then the bookings for this class has ended so reset
					resetPlacesAvailable($data->FitnessClassID);  // call function passing the id of fitnessclass record to reset placesAvailable and bookings	
				}
			}

		}
	}

function resetPlacesAvailable($id) {
	// get a reference to global reference variable to database
	global $db;

	// first, get record data from fitnessclass table of $id
	$query = "SELECT fitnessclass.PlacesAvailableStart, fitnessclass.InstanceRecord FROM fitnessclass WHERE fitnessclass.FitnessClassID = $id";
	if ( $result = mysqli_query($db, $query) ) {
		$data = mysqli_fetch_object($result);
		$placesStart = $data->PlacesAvailableStart;  // get the places availiable default, at start, to use to reset PlacesAvailable
		$instanceRecord = $data->InstanceRecord;     // get the current InstanceRecord to increment by 7, for 7 days to next class date
	}

	$instanceRecord += 7;  // increment instance record
	if ( $instanceRecord > 365 ) // if instance record is set to later than 365 (php manual says day of year returns from 0 to 365) 
		$instanceRecord -= 365;  // then need to start over by substracting 365

	// now set PlacesAvailable using placesStar and set InstanceRecord with new value 7 days later
	$query = "UPDATE fitnessclass SET fitnessclass.PlacesAvailable = $placesStart, fitnessclass.InstanceRecord = $instanceRecord WHERE fitnessclass.FitnessClassID = $id";
	mysqli_query($db, $query);  // query the db

	// finally, delete all records from bookings with foreign key $id that corresponds to the fitness class being reset
	$query = "DELETE FROM bookings WHERE bookings.FitnessClassID = $id";
	mysqli_query($db, $query);  // query the db
}



?>

<?xml version="1.0" encoding="UTF-8">

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
        <title>SweatHut Fitness and Sporting Club Activities Page</title>
        
        <!--favicon icon-->
        <link rel="shortcut icon" href="./favicon.ico" />
   
        <!-- Metadata Section:  Description of Page and Keywords for Site. -->
        <meta name="description" content="SweatHut Fitness and Sporting Club Website Activities Page" />
        <meta name="keywords" content="SweatHut Fitness and Sporting Club Website Sports Center Highly Praised Restaurant Individual Family Membership Spacious Facilities Swimming Pool Squash Courts Gymnasium Badminton Netball Basketball Weight Training Treadmills Rowing Machines Static Bikes Spinning Sauna Jacuzzi Tanning" />
   
        <!-- Link to Stylesheet -->
        <link rel="stylesheet" href="./main.css" type="text/css" />
        
        <!-- Link to Javascript code for page -->
        <script type="text/javascript" language="javascript" src="./main.js"> </script>
        
    </head>
    
    <body onload="Init()">
        <div id="top" class="top"> </div>
        
        <div id="main" class="mainContainer"> <!-- The main container that has border and holds the pages content -->
            <div id="contentContainer" class="contentContainer">  <!-- Sets the width of the inner page content to 800px -->
    
                <!-- Logo and link to homepage -->
                <a href="./home.html" class="logo"><img src="sfasc_logo_off.gif" id="logo" alt="SFASC Logo" class="removeExtraSpace" onmouseover="rollover('logo', 'on')" onmouseout="rollover('logo', 'off')" /></a>
        
                <!-- Menu Section -->
                <div class="menu">
                    <table cellspacing="0">
                        <tr>
                            <td><a href="./home.html"><img src="./home_off.gif" name="home" alt="home: back to homepage" id="home" onmouseover="rollover('home', 'on')" onmouseout="rollover('home', 'off')"  /></a></td>
                            <td><a href="./committee.php"><img src="./committee_off.gif" name="committee" alt="Committee page link" id="committee" onmouseover="rollover('committee', 'on')" onmouseout="rollover('committee', 'off')"  /></a></td>
                            <td><a href="./activities.php"><img src="./activities_off.gif" name="activites" alt="Activities page link" id="activities" onmouseover="rollover('activities', 'on')" onmouseout="rollover('activities', 'off')"  /></a></td>
                            <td><a href="./membership.php"><img src="./membership_off.gif" name="membership" alt="Membership page link" id="membership" onmouseover="rollover('membership', 'on')" onmouseout="rollover('membership', 'off')"  /></a> </td>
                            <td><a href="./links.html"><img src="./links_off.gif" name="links" alt="Links page link" id="links" onmouseover="rollover('links', 'on')" onmouseout="rollover('links', 'off')"  /></a></td>
                            <td><a href="./contact.html"><img src="./contact_off.gif" name="contact" alt="Contact page link" id="contact" onmouseover="rollover('contact', 'on')" onmouseout="rollover('contact', 'off')"  /></a></td>
                        </tr>
                    </table>
                </div>
                
                <!-- The Main content -->
                <div id="pageContent" class="pageContent">
                    <h3 class="pageWelcome">SWEATHUT FITNESS AND SPORTING CLUB ACTIVITY PAGE</h3> <!-- A page welcome phrase to format differently at the top of the page -->
                    
                    <!-- Intro to Membership Page, a short description of how to become a member and note about pricing information -->
					<p class="textColor">
						Below is a list of our Club Activities.  The Fitness Class name, the Instructor for the class, and the price for non-members is listed
                        along with the days of the week and times for the class.  Be sure to book your class in advance using our <a href="#bookingsFormHeader">Activity Booking Form</a>
                        at the bottom of this page.  The classes fill up quickly!  The number in parenthesis in the table below is the number of places available for that class. 
					</p>
						<br />
                        
                    <hr class="addressHR" />
                    
                </div>  <!-- End page content for introduction section -->

				                        
                <!-- Section to display Club Activities information -->
                    <h3 class="pageWelcome center" id="clubActivities">Club Activities:</h3>
                    
                    <table class="clubActivitiesTable" id="clubActivities" cellpadding="0" cellspacing="0">
						<tr class="actRow">
							<th>Activity</th>
							<th>Instructor</th>
							<th class="center">Price <br /><span class="italic small">(non-member)</span></th>
                            <th colspan="3" class="center">Weekly Schedule and Availability</th>
						</tr>
                        
                    <?php

						if ( $error )     // if $error is set
							echo $error;  // write $error to page
                                               
                        // Build a query to extract all of the rows from activities and matching rows in fitnessclass which holds all the instances of activities in a week 
                        // according to day and time and also has the places available information for each individual class
						$query = "SELECT activities.ActivityID, activities.Activity, activities.Instructor, activities.Price, activities.PriceInfo, fitnessclass.ClassDate, fitnessclass.PlacesAvailable FROM activities INNER JOIN fitnessclass ON activities.ActivityID = fitnessclass.ActivityID";
                                        
						$testID = 1;     // this is a variable to hold the value of the current ID to make sure no duplicate data gets written to table
						$idCount = 0;    // this variable holds the number of times each ID has been received from DB; it is reset after a new ID is found
						$shadeCount = 0; // this variable keeps track of which rows are shaded to shade every other row
        
                        if ( $result = mysqli_query($db, $query) ) {
                            // loop over rows with mysqli_fetch_object
                            while ( $data = mysqli_fetch_object($result) ) {
								if ( $testID == $data->ActivityID && $idCount == 0 ) { // first check if current record's ID has already been written to html table
									echo "<tr class='actRow shade'><td>$data->Activity</td><td>$data->Instructor</td><td class='center'>$data->Price <span class='italic small'>$data->PriceInfo</span></td><td class='smallCell'>$data->ClassDate <span class='italic red'>($data->PlacesAvailable)</span></td>";
									$idCount++; // increment the count on how many records written
								}
								elseif ( $testID == $data->ActivityID && $idCount > 0 ) { // the current record's ID has already been written so only need to write the fitnessclass ClassDate and PlacesAvaialbed to html table
									echo "<td class='smallCell'>$data->ClassDate <span class='italic red'>($data->PlacesAvailable)</span></td>";
									$idCount++; // increment the count on how many records written
								}
								else { // $testID is not equal to current record's ID so this is a new record and entire record must be written to html table
									// check if there is an empty cell because only two class dates and places available was written
									if ( $idCount == 2 ) {
										// check if the row is shaded, i.e. $shadeCount is even
										if ( ($shadeCount % 2) == 0 )
											echo "<td class='fillerTextShade'>Filler 9:00 AM</td>";
										else
											echo "<td class='fillerText'>Filler 9:00 AM</td>";
									}

									// finish the previous row
									echo "</tr>";

									// increment $shadeCount since a row was just completely written to html
									$shadeCount++;
									
									// check if need to shade the new row--is an even row
									if ( ($shadeCount % 2) == 0 ) // the remainder after dividing by 2 is 0, so it is an even number
										echo "<tr class='actRow shade'>";
									else 
										echo "<tr class='actRow'>";

									// write the new row start
									echo "<td>$data->Activity</td><td>$data->Instructor</td><td class='center'>$data->Price <span class='italic small'>$data->PriceInfo</span></td><td class='smallCell'>$data->ClassDate <span class='italic red'>($data->PlacesAvailable)</span></td>";	
									$idCount = 1; // set idCount to 1 since this is the first row written
								}

								// set $testID to current ActivityID since it has now been written to page
								$testID = $data->ActivityID;
                            }
							
							// finish the last row written 
							// check if there is an empty cell because only two class dates and places available was written
							if ( $idCount == 2 ) {
								// check if the row is shaded, i.e. $shadeCount is even
								if ( ($shadeCount % 2) == 0 )
									echo "<td class='fillerTextShade'>Filler 9:00 AM</td>";
								else
									echo "<td class='fillerText'>Filler 9:00 AM</td>";
							}
							
							echo "</tr>";
                        }

                         
                    ?>  
                        
                </table>  

				<hr class="addressHR" />  
						<br />
						<br />

					<h3 class="pageWelcome center" id="bookingsFormHeader">Activities Bookings Form</h3>
                    
                    <p class="textColor pageContent">
                        Use the form below to book one of our activities.  Please fill our your first and last name and select the class and date from the drop
						down lists.  
                    </p>
                   
                    <form name="bookingsForm" id="bookingsForm" action="./ProcessBooking.php" method="post" onsubmit="return validateBookingsForm()">
                        <table class="bookingsTable" cellpadding="5px">
                            <tr>
                                <td class="alignRight">First Name</td>
                                <td class="alignLeft"><input type="text" id="firstName" name="firstName" /></td>
                                <td class="alignRight">Last Name</td>
                                <td class="alignLeft"><input type="text" id="lastName" name="lastName" /></td>
                            </tr>
                            <tr>
                                <td class="center" colspan="4">Activity/Instructor Class Date</td>
							</tr>
							<tr>
                                <td class="center" colspan="4">
                                    <select name="booking" id="booking">
                                        
						<?php

							// Query the Activity and Instructor from activities table joined with Class Date and Places Available
							// from fitnessclass table
							$query = "SELECT activities.ActivityID, activities.Activity, activities.Instructor, fitnessclass.FitnessClassID, fitnessclass.ClassDate, fitnessclass.PlacesAvailable FROM activities INNER JOIN fitnessclass ON activities.ActivityID = fitnessclass.ActivityID";
							if ( $result = mysqli_query($db, $query) ) {
								$count = 0; // variable to shade every other row for easier reading

								// loop over $result getting records as object
								while ( $data = mysqli_fetch_object($result) ) {	
									// check if there are still PlacesAvailable
									if ( $data->PlacesAvailable > 0 ) {  // if there is at least 1 PlaceAvailable
										// Write the DB data to drop-down list option values
										// shade every other row for easier reading
										if ( ($count % 2) == 0 ) // $count is even
											echo "<option value='" . "$data->FitnessClassID|$data->Activity|$data->Instructor|$data->ClassDate" . "' class='shade'>$data->Activity by $data->Instructor at $data->ClassDate</option>";
										else
											echo "<option value='" . "$data->FitnessClassID|$data->Activity|$data->Instructor|$data->ClassDate" . "'>$data->Activity by $data->Instructor at $data->ClassDate</option>";
									}

									$count++; // increment the counter
								}
							}	

						?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"><input type="submit" value="Book Activity" id="submit" name="submit" /></td>
                            </tr>
                        </table>
                    </form>

					<br />
					<br />

                              
            </div> <!-- End contentContainer div -->           
            <div class="clearFooter"> </div>   <!-- Needed to make the mainContainer expand with the content so that the footer (address) stays on bottom -->
        </div> <!-- End mainContainer Div -->
        
        <!-- Address Section -->
        <div id="address" class="address">
            <hr class="addressHR" />
            <p class="addressInfo">
                <span class="bold textColor">&nbsp;&nbsp;&#169; 2010 SweatHut Fitness and Sporting Club</span>
                <span class="italic">&nbsp;&nbsp;All Rights Reserved.</span> 
                    <br />
                345 Greengage Lane, Small Town, Florida 32165 | (555) 123-1234  |  secretary@sfasc.com <br />
            </p>
        </div>

		<?php

			// close the db connection
			mysqli_close($db);

		?>
        
    </body>
</html>
        
        
        
