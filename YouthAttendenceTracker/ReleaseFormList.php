<?php
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
?>

<html>
	<head>
		<title>Release Form List</title>
		
		<!--favicon icon-->
		<link rel="shortcut icon" href="./favicon.ico" />
   
		<!-- Metadata Section:  Description of Page and Keywords for Site. -->
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		
		<!-- Link to Stylesheet -->
		<link rel="stylesheet" href="./main.css" type="text/css" />
		
		<!-- Link to Javascript code for page -->
		<script type="text/javascript" language="javascript" src="./main.js"> </script>
		
		<style type="text/css">
			
		</style>
	</head>
		
	<body>
	
		<!-- Service Attendence -->
	   <div id="sidebar">
		<table>
			<tr>
				<th align="center">Average Attendence for <?= $_GET['year'] ?></th>
			</tr>
			<?php
				require_once('MYSQL_helper.php');
				$db = db_connect();
					
				require_once('Misc.php');
				printf('<tr><td>The average service attendence is:  %s.</td></tr>', get_average_attendence($db, null, $_GET['year']));
			?>
		</table>
		
		<!-- Average Monthly Attendence -->
		<table>
			<tr>
				<th align="center">Monthly Average Attendence</th>
			</tr>
			<?php
				require_once('MYSQL_helper.php');
				$db = db_connect();
					
				require_once('Misc.php');
				printf('<tr><td>The month\'s average service attendence is:  %s.</td></tr>', get_average_attendence($db, $_GET['month'], $_GET['year']));
			?>
		</table>
		
		<br />

		<!-- Current Release Forms -->
		<table>
			<tr>
				<th align="center">Students With Current Release Forms</th>
			</tr>
			<?php           			
    	  			require_once('WebControls.php');
				$year = date('Y');  // get the full year for use in list query
    	  			$list = new ListView($db, 'attendee', 'FirstName|LastName', '<tr><td>%s %s</td></tr>', 'Active="yes" AND DATE(ReleaseFormUpdate) BETWEEN "' . $year . '-01-01" AND "' . $year . '-12-31" ORDER BY LastName');
    	  			$list->print_results();
		    	  ?>
		</table>
		
		<br />
		
		<!-- Birthdays -->
		<table>
			<tr>
				<th align="center">Birthdays this Month</th>
			</tr>
			<?php
				// use webcontrols to get birthdays and print in tabular format
				// use month to build the where clause to get birthdays from current month
				$month = date('m');
				$where = "Active='yes' AND MONTH(DOB) = $month ORDER BY DOB;";  
				$list = new ListView($db, 'attendee', 'FirstName|LastName|DOB', '<tr><td>%s %s -- %s</td></tr>', $where);
				$list->print_results();
			?>
		</table>
		
		<br />
		
		<!-- Graduating This Year -->
		<table>
			<tr>
				<th align="center"><?= date('Y') ?> Graduates</th>
			</tr>
			<?php
				// use webcontrols to get birthdays and print in tabular format
				// use month to build the where clause to get birthdays from current month
				$year = date('Y');
				$query = 'SELECT a.FirstName, a.LastName FROM attendee as a INNER JOIN student as s ON a.ID = s.ID WHERE YEAR(s.GraduationDate) = "' . $year . '" AND Active="yes" ORDER BY a.LastName DESC;';
				if ( $results = $db->query($query) ) {
					// if no rows print "No Graduates"
					if ( $results->num_rows == 0 ) {
						echo "<tr><td>No Graduates</td></tr>";
					} else {
						while ( $object = $results->fetch_object() ) {
							printf('<tr><td>%s %s</td></tr>', $object->FirstName, $object->LastName);
						}
					}
				}
			?>
		</table>
	   </div><!-- End sidebar div -->
	</body>
</html>
