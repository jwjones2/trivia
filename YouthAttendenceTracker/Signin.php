<?php
	session_start();
	error_reporting(0);
	/********************************************************
	 * Signin.php
	 *
	 * Description:  This is a Master page for signins.  Handles
	 * 	individual signins.  As each student is entered, the
	 * 	database is adjusted (temp table) and then if submitted
	 * 	the data is entered in servicelist.
	 *
	 * 	??Can be called with an id to get summary mode??
	 *
	 ********************************************************/

	// output the header
	$title = "Signin Page";
	require_once('Header.php');

	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation


	// get the date from GET or set date of signin page to current date
	$service_date = date('Y-m-d');  // default to current date
	if ( isset($_GET['date']) && $_GET['date'] != "" )
		$service_date = $_GET['date'];
?>

    	  <?php

		// FIRST:  Create the service entry in service table and get ID for use in servicelist
		$query = 'INSERT INTO service (Date, Title, EventType, submitted) VALUES ("' . $service_date . '", "NULL", "Youth Service", "no");';

		if ( $db->query($query) ) {
			// first, get insert ID for use in following queries
			$serviceID = $db->insert_id;
			// add service id to session for use over multiple pages
			$_SESSION['service_id'] = $serviceID;
		} else {
			// then a service already exists with that date and eventtype
			// so, need to query for the service and set variables
			$query = "SELECT ID FROM service WHERE Date='$service_date' AND EventType='Youth Service';";
			if ( $result = $db->query($query) ) {
				if ( $object = $result->fetch_object() ) {
					$serviceID = $object->ID;
					$_SESSION['service_id'] = $serviceID;
				} else {
					echo '<h1 style="color:Red;position: absolute; left: 50%;margin-left:-300px;top: 50px;background-color:Yellow;z-index:99;">Could not set up the Service.  Please try again.</h1>';
					}
			} else {
				echo '<h1 style="color:Red;position: absolute; left: 50%;margin-left:-300px;top: 50px;background-color:Yellow;z-index:99;">Could not set up the Service.  Please try again.</h1>';
			}
		}

        	// print the error message if it exists
    	  	if ( $error != "" )
    	  		echo "<h1 class='red'>$error</h1>";

        ?>
        <!-- Sign Up Form section -->
        <div class="signup container">

        	<!-- Signup Sheet table -->
		<table border="1" cellspacing="0" cellpadding="0" id="signup_table">

			<tr>
				<td colspan="7" align="center">
					<div class="signup_header">INDIVIDUAL SIGN-UP</div>
					<span class="giant-text theme-color bold">
						<?php
							// write the date--stored in service_date
							// first clean up for appearance and add month name
							$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
							$dparts = explode('-', $service_date);
							printf('%s %s, %s', $months[$dparts[1] - 1], $dparts[2], $dparts[0]);
						?>
					</span>
				</td>
			</tr>
			<tr class="signup_columnheader">
				<td>Name</td>
				<!-- NO LONG NEED HERE <td>Here?<br />10,000</td>-->
				<td>Sun. Sch<br />30,000</td>
				<td>Sun. AM<br />10,000</td>
				<td>Sun. PM<br />10,000</td>
				<td>Bible<br />50,000</td>
				<td>Visitors<br />100,000</td>
				<td>Extra Points</td>
			</tr>

       		<!-- Student Signup Rows -->

	<!-- NO STUDENTS UNTIL ADDED BY ADDSTUDENTFORM -->

			<!-- Add visitor button row -->
			<tr>
				<td colspan="7" class="add-student-button" align="center">
					<input type="button" value="Click to Add a Student" onclick="showAddStudent(0)" />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="italic">Quick Add:</span>
					<input type="text" id="quick-add" /><input type="button" value=">>" onclick="quickAddStudent($('#quick-add').val())" />
				</td>
			</tr>

	<script>
		$( "#quick-add" ).autocomplete({
		    minLength: 1,
			delay: 500,
			source: function( request, response ) {
				lastXhr = $.ajax({
					url: "./SearchStudents.php",
					dataType: "json",
					data: {
						term: $("#quick-add").val().trim(),
						all: "false"
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
				$('#quick-add').val(ui.item.label);
			},
			search: function ( event, ui ) {
				$("#quick-add").css("background-image", "url('./site_images/ajax-loader.gif')");
				$("#quick-add").css("background-position", "right");
				$("#quick-add").css("background-repeat", "no-repeat");
			},
		    select: function ( event, ui ) {
				$("#quick-add").css("background-image", "url('')");
				quickAddStudent(ui.item.value);
				$('#quick-add').val(ui.item.label);
		    }
		});

		$("#quick-add").keypress( function ( e ) {
			if (!e) e = window.event;
			if (e.keyCode == '13'){
			  $('#quick-add').autocomplete('close');
			  $("#quick-add").css("background-image", "url('')");
			  $('#quick-add').val("");
			  return false;
			}
		});
	</script>

			<?php
				/** 1-6-15 REMOVED SERVICE SUBMITTED.
				 *  From now on the services will be entered directly into the servicelist table
				 *  instead of kept in the temp_signin until submitted.  I was looking at transactional
				 *  qualities when I set this up and it seems completely unnecessary now.  When an attendee
				 *  is entered or updated or removed, etc., that can happen immediately.  There can be
				 *  problems down the road with querying signin data if a service is accidentally
				 *  not submitted and thereby the data still kept in temp_signin.  Plus there is too much
				 *  complexity introduced by the old way.
				 **/
				$list_table = "servicelist";

				// (1.) get visitor data and add to array -- need to get names from attendee
				$visitor_data = array();
				$query = "SELECT t1.FirstName, t1.LastName, t1.NickName, t1.PictureURL, t2.AttendeeID, t2.VisitorID FROM attendee as t1 INNER JOIN visitor as t2 ON t1.ID=t2.VisitorID WHERE t2.ServiceID = $serviceID;";
				if ( $r1 = $db->query($query) ) {
					while ( $o1 = $r1->fetch_array() ) {
						// add to array -- store in multidimentional array
						$visitor_data[ $o1[5] ] = $o1;
					}
				} else {
					echo '<tr><td colspan="7"><h1 class="red">There was a problem retrieving the student\'s data.</h1></td></tr>';
				}

				// (2.) get signin and attendee data and (3.) build rows
				$query = "SELECT t1.ID, t1.FirstName, t1.LastName, t1.NickName, t1.PictureURL, t2.SunSchAttend, t2.SunMornAttend, t2.SunEvenAttend, t2.Bible, t2.Visitors, t2.extrapoints FROM attendee as t1 INNER JOIN $list_table as t2 ON t1.ID = t2.AttendeeID WHERE t2.ServiceID = $serviceID ORDER BY t1.LastName;";
				if ( $result = $db->query($query) ) {
					while ( $object = $result->fetch_object() ) {
						// (3.) Build the rows populating student's data
						// First, search to see if the current student is a visitor, if so do not write the delete button
						$fl0 = false;  // flag for visitor or not, true = visitor
						if ( isset($visitor_data[$object->ID]) )
							$fl0 = true;


						// start the row
						printf('<tr id="StudentRow_%d">', $object->ID);

						if ( !$fl0 ) {
							printf('<td><input type="button" value="x" id="student-row-delete-button" class="delete-button" onclick="deleteStudent(%d)" />', $object->ID);
							// check if PictureURL not null
							if ( $object->PictureURL != "NULL" && $object->PictureURL != null )
								printf('<img src="%s" width="50px" height="50px" />', $object->PictureURL);
						} else {
							echo '<td style="color: Green;">';
						}

						// make name clickable to show student's data in popup
						printf('<span class="underline clickable" id="student-name" onclick="showStudentPopup(%d)">', $object->ID);

						// check for nickname** THIS IS GOING TO BE "CALLED BY" NAME.  If set, replaces the first name
						if ( $object->NickName != "NULL" && $object->NickName != null )
							echo $object->NickName, ' ', $object->LastName;
						else
							echo $object->FirstName, ' ', $object->LastName;
						echo "</span></td>";

						// Sunday School
						printf('<td><input type="checkbox" id="%d_SunSchAttend" class="signup_checkbox" onchange="toggleSigninValue(\'%d_SunSchAttend\');" ', $object->ID, $object->ID);
						if ( $object->SunSchAttend == "yes" )
							echo 'checked="checked" /></td>';  // add checked if set in db and finish field
						else
							echo "/></td>";

						// Sunday Morning
						printf('<td><input type="checkbox" id="%d_SunMornAttend" class="signup_checkbox" onchange="toggleSigninValue(\'%d_SunMornAttend\');" ', $object->ID, $object->ID);
						if ( $object->SunMornAttend == "yes" )
							echo 'checked="checked" /></td>';  // add checked if set in db and finish field
						else
							echo "/></td>";

						// Sunday Evening
						printf('<td><input type="checkbox" id="%d_SunEvenAttend" class="signup_checkbox" onchange="toggleSigninValue(\'%d_SunEvenAttend\');" ', $object->ID, $object->ID);
						if ( $object->SunEvenAttend == "yes" )
							echo 'checked="checked" /></td>';  // add checked if set in db and finish field
						else
							echo "/></td>";

						// Bible
						printf('<td><input type="checkbox" id="%d_Bible" class="signup_checkbox" onchange="toggleSigninValue(\'%d_Bible\');" ', $object->ID, $object->ID);
						if ( $object->Bible == "yes" )
							echo 'checked="checked" /></td>';  // add checked if set in db and finish field
						else
							echo "/></td>";

						// Visitors
						printf('<td><input type="hidden" id="%d_Visitors" value="0" /><input type="button" value="Add Visitor" id="%d_Visitor" onclick="showAddStudent(%d);" /><div id="Visitor_%d" class="visitorsAdded">', $object->ID, $object->ID, $object->ID, $object->ID);
						// now check for visitors and add then finish the row
						foreach ( $visitor_data as $v ) {
							if ( $v['AttendeeID'] == $object->ID ) {
								// a visitor so write to row
								// check for NickName, or "Called by" name
								if ( $v['NickName'] == "NULL" || $v['NickName'] == null )
									printf('<div id="%d%s%s">%s %s', $v['VisitorID'], $v['FirstName'], $v['LastName'], $v['FirstName'], $v['LastName']);
								else
									printf('<div id="%d%s%s">%s %s', $v['VisitorID'], $v['FirstName'], $v['LastName'], $v['MiddleName'], $v['LastName']);
								printf('<input type="button" value="x" class="visitor-delete-button" onclick="deleteStudentVisitor(%d, \'%d%s%s\', %d);" /></div>', $object->ID, $v['VisitorID'], $v['FirstName'], $v['LastName'], $v['VisitorID']);
							}
						}
						echo "</div></td>";

						// Extra points
						printf('<td><input type="text" id="%d_ExtraPoints" class="signup_textbox" onchange="toggleSigninValue(\'%d_ExtraPoints\');" ', $object->ID, $object->ID);
						// now check for value of extrapoints in table
						printf('value="%d" /></td>', $object->extrapoints);

						// end the row
						echo "</tr>";
					}
				} else {
					echo '<tr><td colspan="7"><h1 class="red">There was a problem retrieving the student\'s data.</h1></td></tr>';
				}
			?>

		</table>  <!-- end the team tables  -->
        		&nbsp;&nbsp;
        	<table id="footer_table" border="1" cellspacing="0" cellpadding="0">   <!-- for extra footers -->
        		<!-- Footer/Date Row -->
        		<tr class="signup_footer">
        			<td colspan="7" align="center">
        				<input type="button" value="Select Random Teams" onclick="selectRandomTeams($('#randomTeamSize').val())" />
					<input type="text" value="2" class="center" size="2" maxlength="2" id="randomTeamSize" />
        			</td>
        		</tr>
        	</table>

		<script>
			function selectRandomTeams ( num ) {
				var students = $('[id=student-name]');
				// inialize teams
				var teams = new Array(num);
				for ( var i = 0; i < num; i++ )
					teams[i] = new Array();

				var counter = 0;  // to assign to each team
				var c = students.length;
				// create "num" number of teams and add students randomly selecting from students until all used
				while ( c > 0  ) {
					var rand = Math.ceil(Math.random() * students.length);
					// double check that rand is not greater than student.length because of using ceil
					if ( rand == students.length )
						--rand; // just deincrement

					var student = students.splice(rand, 1);
					teams[counter].push( $(student).html() );
					if ( ++counter == num ) // increment and reset counter if necessary
						counter = 0;
					c--;
				}
				var header = "Random Teams";
				var content = "";
				var width = 900;
				var sectionWidth = 900 / num;
				// build the team output for popup
				for ( var i = 0; i < num; i++ ) {
					content += "<div style='position: absolute; left: " + sectionWidth * i + "'><h2>Team " + (i + 1) + "</h2>";
					for ( var j = 0; j < teams[i].length; j++ ) {
						content += "<h3>" + teams[i][j] + "</h3>"
					}
					content += "</div>";
				}

				setPopupContent(header, content, "");
				showPopup();
				resizePopup(500, width);
			}
		</script>

        	<!-- END FORM + Add extra info fields -->


        	<input type="hidden" name="Date" id="Date" value="<?php echo $service_date; ?>" />
        	<input type="hidden" name="Title" value="NULL" />
        	<input type="hidden" name="EventType" value="Youth Service" />
        	</form>
        </div>

        <!--  ADD VISITOR SECTION.  -->
        <?php include('add-student-form.php'); ?>


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

	<!-- Teams menu -- show the current teams and allow to click to view and to add extra points -->
	<?php
		$query = 'SELECT * FROM team WHERE End >= CURDATE() OR End="0000-00-00";';
    	if ( $results = $db->query($query) ) {
    		// if results and have rows print the team section
    		if ( $results->num_rows > 0 ) :
    ?>
	<div id="teams-menu" class="container">
		<span class="underline bold">Current Teams</span>
		<br />
		<span class="points-label">Click on the team's name to view the team's members.</span>
		<br /><br />

		<?php
			/**
			 Get all active teams.  These are teams that don't have an end date
			 set or that have an end date that is in the future.
			 **/
			include('TeamPointsCalculator.php');
    	  		while ( $dat = $results->fetch_object() )  {
				// print team name and button to add extra points
    				printf('<span class="underline bold italic larger-text clickable" onclick="showTeamPopup(%d)">%s</span><br /><input type="button" value="Add Extra Points" onclick="addTeamPoints(%d, %d);" /><br />', $dat->ID, $dat->Name, $serviceID, $dat->ID);

				// show the team's current points
				try {
					$team = new TeamPointsCalculator($db, $dat->ID);
					printf('<span class="large-text">Points = %s</span>', number_format($team->get_points()) );
				} catch ( Exception $e ) {
					echo "FAILED to get the team points!!!";
				}

				// now query and get any extra points for this team and list below button to be able to remove them if added incorrectly
				$query = 'SELECT * FROM servicepoints WHERE ServiceID=' . $serviceID . ' AND TeamID=' . $dat->ID . ';';
				if ( $r = $db->query($query) ) {
					while ( $o = $r->fetch_object() ) {
						// print the team points with description and a delete button
						printf('<br /><span id="%d">%s <input type="button" value="x" style="float: right" onclick="deleteExtraPoints(%d)" /><br /><span class="small-text italic">%s</span><hr /></span>', $o->ID, number_format($o->Points), $o->ID, $o->Description);
					}
				}

				echo "<br /><br />";
			}
		?>
	</div>

	<?php endif;
	     } else {
	     	// do nothing for now, just don't print teams...need to handle the error
	     }
    ?>

		<!-- Quick Picker for regular students -->
		<div id="add-quick-student">
		<?php
			 $query = 'SELECT AttendeeID, COUNT(AttendeeID), FirstName, NickName, LastName, PictureURL FROM (SELECT s.ID, s.AttendeeID, a.FirstName, a.LastName, a.NickName, a.DOB, a.PictureURL FROM servicelist AS s INNER JOIN attendee AS a ON s.AttendeeID = a.ID ORDER BY ID DESC LIMIT 100) AS t GROUP BY t.AttendeeID ORDER BY COUNT(t.AttendeeID) DESC;';

			 if ( $result = $db->query($query) ) {
					while ( $obj = $result->fetch_object() ) {
				  	// first check to see if the student has a picture in the system.  If so use the picture instead of a button
					 	if ( $obj->PictureURL != "NULL" ) {
							printf('<span><img src="%s" width="75px" height="75px" style="cursor: hand; cursor: pointer;"', $obj->PictureURL);
							// check for nickname and use if not null
							if ( $obj->NickName != 'NULL' && $obj->NickName != null ) {
								printf(' onclick=\'quickPickStudent("%d|%s|%s|%s")\' alt=\'Add %s %s\' /></span>', $obj->AttendeeID, $obj->FirstName, $obj->LastName, $obj->NickName, $obj->NickName, $obj->LastName);
							} else {
							  printf(' onclick=\'quickPickStudent("%d|%s|%s")\' alt="Add %s %s" /></span>', $obj->AttendeeID, $obj->FirstName, $obj->LastName, $obj->FirstName, $obj->LastName);
							}
					 	}
				 	}
			 }
		?>
		</div>

	<?php require_once('footer.php'); ?>
